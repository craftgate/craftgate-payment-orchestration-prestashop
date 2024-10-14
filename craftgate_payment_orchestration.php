<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use classes\CraftgatePayment;
use classes\CraftgatePaymentDBManager;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

require_once _PS_MODULE_DIR_ . 'craftgate_payment_orchestration/classes/CraftgateUtil.php';
require_once _PS_MODULE_DIR_ . 'craftgate_payment_orchestration/classes/CraftgatePaymentService.php';
require_once _PS_MODULE_DIR_ . 'craftgate_payment_orchestration/classes/CraftgatePaymentDBManager.php';
require_once _PS_MODULE_DIR_ . 'craftgate_payment_orchestration/classes/CraftgatePayment.php';
require_once _PS_MODULE_DIR_ . 'craftgate_payment_orchestration/classes/CraftgatePaymentErrorException.php';

class Craftgate_Payment_Orchestration extends PaymentModule
{
    const CONFIG_IS_MODULE_ENABLED = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_IS_MODULE_ENABLED';
    const CONFIG_PAYMENT_OPTION_TITLE = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_PAYMENT_OPTION_TITLE';
    const CONFIG_PAYMENT_OPTION_DESCRIPTION = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_PAYMENT_OPTION_DESCRIPTION';
    const CONFIG_LIVE_API_KEY = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_LIVE_API_KEY';
    const CONFIG_LIVE_SECRET_KEY = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_LIVE_SECRET_KEY';
    const CONFIG_SANDBOX_API_KEY = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_SANDBOX_API_KEY';
    const CONFIG_SANDBOX_SECRET_KEY = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_SANDBOX_SECRET_KEY';
    const CONFIG_IS_SANDBOX_ACTIVE = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_IS_SANDBOX_ACTIVE';
    const CONFIG_IFRAME_OPTIONS = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_IFRAME_OPTIONS';
    const CONFIG_WEBHOOK_URL = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_WEBHOOK_URL';
    const CONFIG_IS_ONE_PAGE_CHECKOUT_ACTIVE = 'CRAFTGATE_PAYMENT_ORCHESTRATION_CONFIG_ONE_PAGE_CHECKOUT_ACTIVE';

    const MODULE_ADMIN_CONTROLLER = 'AdminConfigureCraftgatePaymentOrchestration';

    const HOOKS = [
        'paymentOptions',
        'displayAdminOrderLeft',
        'displayAdminOrderMainBottom',
        'actionValidateOrder',
        'displayPaymentByBinaries',
        'displayHeader',
    ];

    private CraftgatePaymentService $craftgatePaymentService;


    public function __construct()
    {
        $this->name = 'craftgate_payment_orchestration';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Craftgate';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];

        $this->controllers = [
            'cancel',
            'external',
            'validation',
        ];

        parent::__construct();

        $this->displayName = $this->l('Craftgate Payment Orchestration');
        $this->description = $this->l("Simple, Flexible, Accessible 'One-Stop Shop' Payment Orchestration");
        $this->craftgatePaymentService = new CraftgatePaymentService($this);
    }

    public function install(): bool
    {
        return parent::install()
            && CraftgatePaymentDBManager::install()
            && $this->registerHook(static::HOOKS)
            && $this->updateDefaultConfigurations();
    }

    public function uninstall(): bool
    {
        return parent::uninstall()
            && Configuration::deleteByName(static::CONFIG_IS_MODULE_ENABLED);
    }

    public function getContent(): void
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(static::MODULE_ADMIN_CONTROLLER));
    }

    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $this->craftgatePaymentService->addInstallmentFee($order);
    }

    public function hookPaymentOptions(array $params): array
    {
        $cart = $params['cart'];

        if (false === Validate::isLoadedObject($cart) || false === $this->isCurrencySupported($cart)) {
            return [];
        }

        $paymentOptions = [];

        if (Configuration::get(static::CONFIG_IS_MODULE_ENABLED)) {
            $paymentOptions[] = Configuration::get(static::CONFIG_IS_ONE_PAGE_CHECKOUT_ACTIVE)
                ? $this->getCraftgateOnePageCheckoutFormPaymentOption()
                : $this->getCraftgateCheckoutFormPaymentOption();
        }

        return $paymentOptions;
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS('modules/' . $this->name . '/views/css/style.css');
    }

    public function hookDisplayPaymentByBinaries(array $params)
    {

        $cart = $params['cart'];

        if (false === Validate::isLoadedObject($cart) || false === $this->isCurrencySupported($cart) || false === Configuration::get(static::CONFIG_IS_ONE_PAGE_CHECKOUT_ACTIVE)) {
            return '';
        }

        return $this->context->smarty->fetch('module:craftgate_payment_orchestration/views/templates/hook/embeddedCheckoutFormBinary.tpl');
    }

    public function hookDisplayAdminOrderLeft(array $params): string
    {
        return $this->hookDisplayAdminOrderMainBottom($params);
    }


    public function hookDisplayAdminOrderMainBottom(array $params): string
    {
        if (empty($params['id_order'])) {
            return '';
        }

        $orderId = $params['id_order'];
        $order = new Order((int)$orderId);
        if (false === Validate::isLoadedObject($order) || $order->module !== $this->name) {
            return '';
        }

        $craftgatePayment = CraftgatePayment::getByOrderId($orderId);

        $this->context->smarty->assign([
            'currency' => $order->id_currency,
            'craftgatePayment' => $craftgatePayment,
            'moduleName' => $this->name,
            'moduleDisplayName' => $this->displayName,
            'moduleLogoSrc' => $this->getPathUri() . 'logo.png',
        ]);

        return $this->context->smarty->fetch('module:craftgate_payment_orchestration/views/templates/hook/displayAdminOrderMainBottom.tpl');
    }


    private function isCurrencySupported(Cart $cart): bool
    {
        $cartCurrency = new Currency($cart->id_currency);
        $moduleCurrencies = $this->getCurrency($cart->id_currency);

        if (empty($moduleCurrencies)) {
            return false;
        }

        foreach ($moduleCurrencies as $currency_module) {
            if ($cartCurrency->id == $currency_module['id_currency']) {
                return true;
            }
        }

        return false;
    }

    private function getCraftgateCheckoutFormPaymentOption(): PaymentOption
    {
        $paymentOption = new PaymentOption();
        $paymentOption->setModuleName($this->name);
        $paymentOptionTitle = !empty(Configuration::get(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_TITLE))
            ? Configuration::get(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_TITLE)
            : $this->l('Pay with Debit/Credit Card and Alternative Payment Methods');

        $paymentOptionDescription = !empty(Configuration::get(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_DESCRIPTION))
            ? Configuration::get(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_DESCRIPTION)
            : $this->l('You can pay with Debit and Credit Card');

        $paymentOption->setCallToActionText($paymentOptionTitle);
        $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'checkoutForm', [], true));

        $this->context->smarty->clearCompiledTemplate('module:craftgate_payment_orchestration/views/templates/front/checkoutFormPaymentOption.tpl');
        $this->context->smarty->assign([
            "description" => $paymentOptionDescription
        ]);
        $paymentOption->setAdditionalInformation($this->context->smarty->fetch('module:craftgate_payment_orchestration/views/templates/front/checkoutFormPaymentOption.tpl', 1, 2));
        return $paymentOption;
    }

    private function getCraftgateOnePageCheckoutFormPaymentOption(): PaymentOption
    {
        $paymentOption = new PaymentOption();
        $paymentOption->setModuleName($this->name);
        $paymentOptionTitle = !empty(Configuration::get(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_TITLE))
            ? Configuration::get(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_TITLE)
            : $this->l('Pay with Debit/Credit Card and Alternative Payment Methods');

        $paymentOption->setCallToActionText($paymentOptionTitle);
        $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'checkoutForm', [], true));

        $this->context->smarty->clearCompiledTemplate('module:craftgate_payment_orchestration/views/templates/front/onePageCheckoutFormPaymentOption.tpl');
        $paymentOption->setBinary(true);
        $paymentOption->setAdditionalInformation($this->context->smarty->fetch('module:craftgate_payment_orchestration/views/templates/front/onePageCheckoutFormPaymentOption.tpl', 1, 2));
        return $paymentOption;
    }

    private function updateDefaultConfigurations(): bool
    {
        $webhookUrl = $this->context->link->getModuleLink($this->name, 'checkoutFormWebhook', [], true);
        return Configuration::updateGlobalValue(Craftgate_Payment_Orchestration::CONFIG_IS_ONE_PAGE_CHECKOUT_ACTIVE, true)
            && Configuration::updateGlobalValue(Craftgate_Payment_Orchestration::CONFIG_WEBHOOK_URL, $webhookUrl);
    }
}
