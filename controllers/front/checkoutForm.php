<?php

require_once _PS_MODULE_DIR_ . 'craftgate_payment_orchestration/classes/CraftgateClient.php';

class Craftgate_Payment_OrchestrationCheckoutFormModuleFrontController extends ModuleFrontController
{

    public function postProcess(): void
    {
        $customer = new Customer($this->context->cart->id_customer);
        if (false === $this->checkIfContextIsValid() || false === $this->checkIfPaymentOptionIsAvailable() || false === Validate::isLoadedObject($customer)) {
            $this->redirectToCheckoutPage();
        }
    }

    public function initContent(): void
    {
        parent::initContent();
        if (Tools::isSubmit("isOnePageCheckout")) {
            $this->handleOnePageCheckout();
        } else {
            $this->handleCheckout();
        }
    }

    public function handleOnePageCheckout(): void
    {
        header('Content-Type: application/json');
        try {
            $checkoutFormUrl = $this->initCheckoutForm();
            $language = $this->context->language->iso_code;
            $iframeOptions = Configuration::get(Craftgate_Payment_Orchestration::CONFIG_IFRAME_OPTIONS);
            $response = ["checkoutFormUrl" => $checkoutFormUrl . "&iframe=true&lang=" . $language . "&" . $iframeOptions . "&hideSubmitButton=true"];
            http_response_code(201);
            exit(json_encode($response));
        } catch (Exception $exception) {
            http_response_code(422);
            $response = ["errorMessage" => $this->module->l("We're currently unable to process this payment option. Please choose another method or try again later.")];
            PrestaShopLogger::addLog("Error occurred while initializing checkout form. Error: " . $exception->getMessage(), 3);
            exit(json_encode($response));
        }
    }

    public function handleCheckout(): void
    {
        try {
            $checkoutFormUrl = $this->initCheckoutForm();

            $language = $this->context->language->iso_code;
            $iframeOptions = Configuration::get(Craftgate_Payment_Orchestration::CONFIG_IFRAME_OPTIONS);

            $this->context->smarty->assign([
                'checkoutFormUrl' => $checkoutFormUrl . "&iframe=true&lang=" . $language . "&" . $iframeOptions
            ]);
        } catch (Exception $exception) {
            PrestaShopLogger::addLog("Error occurred while initializing checkout form. Error: " . $exception->getMessage(), 3);
            $this->context->smarty->assign([
                'error' => $this->module->l("We're currently unable to process this payment option. Please choose another method or try again later.")
            ]);
        }

        $this->setTemplate('module:' . $this->module->name . '/views/templates/front/checkoutForm.tpl');
    }

    private function initCheckoutForm()
    {
        $request = $this->buildInitCheckoutFormRequest();
        $response = CraftgatePaymentService::craftgateClient()->initCheckoutForm($request);

        if (isset($response->pageUrl)) {
            return $response->pageUrl;
        } elseif (isset($response->errors)) {
            throw new Exception($response->errors->errorDescription, $response->errors->errorCode);
        }
    }

    private function buildInitCheckoutFormRequest(): array
    {
        $cartId = $this->context->cart->id;
        $orderTotal = CraftgateUtil::format_price($this->context->cart->getOrderTotal());

        return [
            'price' => $orderTotal,
            'paidPrice' => $orderTotal,
            'currency' => $this->context->currency->iso_code,
            'paymentGroup' => \Craftgate\Model\PaymentGroup::LISTING_OR_SUBSCRIPTION,
            'conversationId' => $cartId,
            'callbackUrl' => $this->context->link->getModuleLink($this->module->name, 'checkoutFormCallbackHandler', ['cartId' => $cartId], true),
            'items' => [
                [
                    'externalId' => $cartId,
                    'name' => 'Cart #' . $cartId,
                    'price' => $orderTotal,
                ],
            ],
        ];
    }

    private function checkIfContextIsValid()
    {
        return true === Validate::isLoadedObject($this->context->cart)
            && true === Validate::isUnsignedInt($this->context->cart->id_customer)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_delivery)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_invoice);
    }


    private function checkIfPaymentOptionIsAvailable(): bool
    {
        if (!Configuration::get(Craftgate_Payment_Orchestration::CONFIG_IS_MODULE_ENABLED)) {
            return false;
        }

        $modules = Module::getPaymentModules();

        if (empty($modules)) {
            return false;
        }

        foreach ($modules as $module) {
            if (isset($module['name']) && $this->module->name === $module['name']) {
                return true;
            }
        }

        return false;
    }

    public function redirectToCheckoutPage(): void
    {
        Tools::redirect($this->context->link->getPageLink('order', true, $this->context->language->id, ['step' => 1]));
    }
}
