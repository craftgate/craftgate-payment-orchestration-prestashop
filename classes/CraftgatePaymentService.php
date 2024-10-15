<?php

use classes\CraftgatePayment;

class CraftgatePaymentService
{
    const INSTALLMENT_FEE_COOKIE_NAME = "installmentFee";
    private PaymentModule $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function handleCheckoutPaymentCallbackResult(Cart $cart, Customer $customer, $checkoutToken): void
    {
        if (!$this->checkIfContextIsValid($cart, $customer)) {
            throw new Exception("Context is not valid!");
        }

        $craftgatePayment = $this->retrieveCraftgatePayment($checkoutToken);
        if ($craftgatePayment) {
            $this->redirectOrderConfirmationPage($craftgatePayment, $customer);
            exit();
        }

        $checkoutFormResult = self::craftgateClient()->retrieveCheckoutFormResult($checkoutToken);
        $checkoutFormResult->checkoutToken = $checkoutToken;

        if (!isset($checkoutFormResult->conversationId) || $checkoutFormResult->conversationId != $cart->id) {
            PrestaShopLogger::addLog("Conversation ID is not matched cart id " . $cart->id . $checkoutFormResult->conversationId);
            throw new Exception("Unknown error occurred!");
        }

        if (!isset($checkoutFormResult->paymentError) && $checkoutFormResult->paymentStatus === 'SUCCESS') {
            $this->createOrder($cart, $customer, $checkoutFormResult);
        } else {
            throw new CraftgatePaymentErrorException($checkoutFormResult->paymentError->errorDescription);
        }
    }

    public function handleCheckoutPaymentWebhookResult($checkoutToken): void
    {
        if ($this->retrieveCraftgatePayment($checkoutToken))
            return;


        $checkoutFormResult = self::craftgateClient()->retrieveCheckoutFormResult($checkoutToken);
        $checkoutFormResult->checkoutToken = $checkoutToken;

        $cartId = $checkoutFormResult->conversationId;
        $cart = new Cart($cartId);
        $customer = new Customer($cart->id_customer);

        if ($this->checkIfContextIsValid($cart, $customer) && !isset($checkoutFormResult->paymentError) && $checkoutFormResult->paymentStatus === 'SUCCESS') {
            $this->createOrder($cart, $customer, $checkoutFormResult);
        }
    }

    private function createOrder(Cart $cart, Customer $customer, $checkoutFormResult): void
    {
        Context::getContext()->cookie->__unset(self::INSTALLMENT_FEE_COOKIE_NAME);
        Context::getContext()->cookie->write();

        if ($checkoutFormResult->installment > 1) {
            Context::getContext()->cookie->__set(self::INSTALLMENT_FEE_COOKIE_NAME, $checkoutFormResult->paidPrice - $checkoutFormResult->price);
            Context::getContext()->cookie->write();
        }

        $this->module->validateOrder(
            (int)$cart->id,
            (int)Configuration::get('PS_OS_PAYMENT'),
            (float)$checkoutFormResult->price,
            $this->module->displayName,
            null,
            ['transaction_id' => $checkoutFormResult->id],
            null,
            false,
            $customer->secure_key,
        );

        $orderCore = Order::getByCartId($cart->id);
        $craftgatePayment = new CraftgatePayment();
        $craftgatePayment->setPaymentId($checkoutFormResult->id);
        $craftgatePayment->setCheckoutToken($checkoutFormResult->checkoutToken);
        $craftgatePayment->setIdOrder($orderCore->id);
        $craftgatePayment->setMetaData([
            'paidPrice' => $checkoutFormResult->paidPrice,
            'installment' => $checkoutFormResult->installment,
            'installmentFee' => $checkoutFormResult->paidPrice - $checkoutFormResult->price,
            'orderId' => $checkoutFormResult->orderId,
            'environment' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_IS_SANDBOX_ACTIVE) ? "Sandbox" : "Production"
        ]);

        $craftgatePayment->save();

    }

    public function addInstallmentFee(Order $order): void
    {
        $installmentFee = Context::getContext()->cookie->__get(self::INSTALLMENT_FEE_COOKIE_NAME);
        if (!$installmentFee) {
            return;
        }

        Context::getContext()->cookie->__unset(self::INSTALLMENT_FEE_COOKIE_NAME);
        Context::getContext()->cookie->write();

        $installmentFee = (float)$installmentFee;

        $orderDetail = new OrderDetail();
        $orderDetail->id_order = $order->id;
        $orderDetail->product_name = $this->module->l('Installment Fee');
        $orderDetail->product_quantity = 1;
        $orderDetail->product_price = $installmentFee;
        $orderDetail->unit_price_tax_excl = $installmentFee;
        $orderDetail->unit_price_tax_incl = $installmentFee;
        $orderDetail->total_price_tax_incl = $installmentFee;
        $orderDetail->total_price_tax_excl = $installmentFee;
        $orderDetail->id_warehouse = 0;
        $orderDetail->id_shop = $order->id_shop;
        $orderDetail->add();

        $orderPayment = $order->getOrderPayments()[0];
        $orderPayment->amount += $installmentFee;
        $orderPayment->save();

        $order->total_paid_real += $installmentFee;
        $order->total_paid += $installmentFee;
        $order->total_paid_tax_incl += $installmentFee;
        $order->total_paid_tax_excl += $installmentFee;
        $order->update();
    }

    private function retrieveCraftgatePayment($checkoutToken): false|CraftgatePayment
    {
        return CraftgatePayment::getByCheckoutToken($checkoutToken);
    }

    private function checkIfContextIsValid($cart, $customer): bool
    {
        return true === Validate::isLoadedObject($cart)
            && true === Validate::isLoadedObject($customer);
    }

    public static function craftgateClient(): CraftgateClient
    {
        $isSandboxActive = Configuration::get(Craftgate_Payment_Orchestration::CONFIG_IS_SANDBOX_ACTIVE);
        if ($isSandboxActive) {
            $apiKey = Configuration::get(Craftgate_Payment_Orchestration::CONFIG_SANDBOX_API_KEY);
            $secretKey = Configuration::get(Craftgate_Payment_Orchestration::CONFIG_SANDBOX_SECRET_KEY);
            return CraftgateClient::getInstance($apiKey, $secretKey, "https://sandbox-api.craftgate.io");
        }

        $apiKey = Configuration::get(Craftgate_Payment_Orchestration::CONFIG_LIVE_API_KEY);
        $secretKey = Configuration::get(Craftgate_Payment_Orchestration::CONFIG_LIVE_SECRET_KEY);
        return CraftgateClient::getInstance($apiKey, $secretKey, "https://api.craftgate.io");
    }


    public function redirectOrderConfirmationPage(CraftgatePayment $craftgatePayment, Customer $customer): void
    {
        $id_order = $craftgatePayment->getIdOrder();
        $order = new Order($id_order);
        $link = new Link();
        $redirect_url = $link->getPageLink('order-confirmation', true, null, [
            'id_cart' => $order->id_cart,
            'id_module' => $this->module->id,
            'id_order' => $id_order,
            'key' => $customer->secure_key,
        ]);
        Tools::redirect($redirect_url);
    }
}