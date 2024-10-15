<?php

class AdminConfigureCraftgatePaymentOrchestrationController extends ModuleAdminController
{
    public function __construct()
    {
        $this->page_header_toolbar_title = 'Craftgate Payment Orchestration Module';
        $this->bootstrap = true;
        $this->className = 'Configuration';
        $this->table = 'configuration';
        parent::__construct();
    }

    public function renderForm()
    {
        $this->context->smarty->assign([
            'CRAFTGATE_MODULE_ENABLED' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_IS_MODULE_ENABLED),
            'CRAFTGATE_PAYMENT_OPTION_TITLE' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_TITLE),
            'CRAFTGATE_PAYMENT_OPTION_DESCRIPTION' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_DESCRIPTION),
            'CRAFTGATE_LIVE_API_KEY' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_LIVE_API_KEY),
            'CRAFTGATE_LIVE_SECRET_KEY' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_LIVE_SECRET_KEY),
            'CRAFTGATE_SANDBOX_API_KEY' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_SANDBOX_API_KEY),
            'CRAFTGATE_SANDBOX_SECRET_KEY' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_SANDBOX_SECRET_KEY),
            'CRAFTGATE_IS_SANDBOX_ACTIVE' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_IS_SANDBOX_ACTIVE),
            'CRAFTGATE_IFRAME_OPTIONS' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_IFRAME_OPTIONS),
            'CRAFTGATE_WEBHOOK_URL' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_WEBHOOK_URL),
            'CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE' => Configuration::get(Craftgate_Payment_Orchestration::CONFIG_IS_ONE_PAGE_CHECKOUT_ACTIVE),
        ]);

        return $this->context->smarty->fetch($this->getTemplatePath() . 'configure.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitCraftgateModuleConfiguration')) {
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_IS_MODULE_ENABLED, Tools::getValue('CRAFTGATE_MODULE_ENABLED'));
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_TITLE, Tools::getValue('CRAFTGATE_PAYMENT_OPTION_TITLE'));
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_PAYMENT_OPTION_DESCRIPTION, Tools::getValue('CRAFTGATE_PAYMENT_OPTION_DESCRIPTION'));
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_IS_ONE_PAGE_CHECKOUT_ACTIVE, Tools::getValue('CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE'));
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_LIVE_API_KEY, Tools::getValue('CRAFTGATE_LIVE_API_KEY'));
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_LIVE_SECRET_KEY, Tools::getValue('CRAFTGATE_LIVE_SECRET_KEY'));
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_SANDBOX_API_KEY, Tools::getValue('CRAFTGATE_SANDBOX_API_KEY'));
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_SANDBOX_SECRET_KEY, Tools::getValue('CRAFTGATE_SANDBOX_SECRET_KEY'));
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_IS_SANDBOX_ACTIVE, Tools::getValue('CRAFTGATE_IS_SANDBOX_ACTIVE'));
            Configuration::updateValue(Craftgate_Payment_Orchestration::CONFIG_IFRAME_OPTIONS, Tools::getValue('CRAFTGATE_IFRAME_OPTIONS'));

            $this->confirmations[] = $this->l('Settings updated successfully.');
        }

        return parent::postProcess();
    }

    public function initContent()
    {
        parent::initContent();
        $this->content .= $this->renderForm();
        $this->context->smarty->assign('content', $this->content);
    }

    public function getTemplatePath()
    {
        return _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/';
    }
}
