<?php
require_once _PS_MODULE_DIR_ . 'craftgate_payment_orchestration/classes/CraftgateClient.php';


class Craftgate_Payment_OrchestrationCheckoutFormCallbackHandlerModuleFrontController extends ModuleFrontController
{

    private CraftgatePaymentService $craftgatePaymentService;

    public function __construct()
    {
        parent::__construct();
        $this->auth = false;
        $this->craftgatePaymentService = new CraftgatePaymentService($this->module);
    }

    public function postProcess(): void
    {
        $checkoutToken = Tools::getValue("token");

        if (!isset($checkoutToken)) {
            Tools::displayError("Error occurred");
            return;
        }

        if (Tools::getValue('source') != 'prestashop') {
            $this->context->smarty->assign([
                "checkoutToken" => $checkoutToken,
                "cartId" => Tools::getValue("cartId")
            ]);

            $this->setTemplate('module:' . $this->module->name . '/views/templates/front/checkoutFormCallback.tpl');
        } else {
            $this->handleResult($checkoutToken);
        }
    }

    public function handleResult(mixed $checkoutToken): void
    {
        $cartId = Tools::getValue("cartId");
        $cart = new Cart($cartId);
        $customer = new Customer($cart->id_customer);

        try {
            $this->craftgatePaymentService->handleCheckoutPaymentCallbackResult($cart, $customer, $checkoutToken);
        } catch (CraftgatePaymentErrorException $e) {
            PrestaShopLogger::addLog("Payment error occurred while creating order for cart $cartId" . $e->getMessage(), 2);
            $this->redirectOrderCheckout($e->getMessage());
        } catch (Exception $e) {
            PrestaShopLogger::addLog("Unknown error occurred while creating order" . serialize($e), 3);
            $this->redirectOrderCheckout();
        }

        $orderConfirmationPageLink = $this->buildOrderConfirmationLink($cart, $customer);
        Tools::redirect($orderConfirmationPageLink);
    }

    public function redirectOrderCheckout($message = null): void
    {
        $redirectUrl = $this->context->link->getPageLink('order', true, $this->context->language->id, ['step' => 1]);
        if (!isset($message)) {
            Tools::redirect($redirectUrl);
        } else {
            $this->context->controller->errors[] = $message;
            $this->context->controller->redirectWithNotifications($redirectUrl);
        }
    }

    public function buildOrderConfirmationLink(Cart $cart, Customer $customer): string
    {
        return $this->context->link->getPageLink('order-confirmation', true, $this->context->language->id,
            [
                'id_cart' => (int)$cart->id,
                'id_module' => (int)$this->module->id,
                'id_order' => (int)$this->module->currentOrder,
                'key' => $customer->secure_key,
            ]
        );
    }
}
