<form action="{$current|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}" method="post" class="defaultForm form-horizontal">
    <div class="panel">
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Enable/Disable' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="CRAFTGATE_MODULE_ENABLED" id="CRAFTGATE_MODULE_ENABLED_on" value="1"
                               {if $CRAFTGATE_MODULE_ENABLED}checked="checked"{/if}>
                        <label for="CRAFTGATE_MODULE_ENABLED_on">{l s='Yes' mod='craftgate_payment_orchestration'}</label>
                        <input type="radio" name="CRAFTGATE_MODULE_ENABLED" id="CRAFTGATE_MODULE_ENABLED_off" value="0"
                               {if !$CRAFTGATE_MODULE_ENABLED}checked="checked"{/if}>
                        <label for="CRAFTGATE_MODULE_ENABLED_off">{l s='No' mod='craftgate_payment_orchestration'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">{l s='Enable or disable the Craftgate payment option on the checkout page.' mod='craftgate_payment_orchestration'}</p>
                </div>

                <label class="control-label col-lg-3">{l s='One Page Checkout' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE" id="CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE_on" value="1"
                               {if $CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE}checked="checked"{/if}>
                        <label for="CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE_on">{l s='Yes' mod='craftgate_payment_orchestration'}</label>
                        <input type="radio" name="CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE" id="CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE_off" value="0"
                               {if !$CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE}checked="checked"{/if}>
                        <label for="CRAFTGATE_IS_ONE_PAGE_CHECKOUT_ACTIVE_off">{l s='No' mod='craftgate_payment_orchestration'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">{l s='Enable or disable one-page checkout for the Craftgate payment option.' mod='craftgate_payment_orchestration'}</p>
                </div>

                <label class="control-label col-lg-3">{l s='Title' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CRAFTGATE_PAYMENT_OPTION_TITLE" value="{$CRAFTGATE_PAYMENT_OPTION_TITLE|escape:'html':'UTF-8'}">
                    <p class="help-block">{l s='This is the title of the payment option shown to the customer during checkout. To use default, keep this field empty.' mod='craftgate_payment_orchestration'}</p>
                </div>

                <label class="control-label col-lg-3">{l s='Description' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <textarea name="CRAFTGATE_PAYMENT_OPTION_DESCRIPTION" rows="4">{$CRAFTGATE_PAYMENT_OPTION_DESCRIPTION|escape:'html':'UTF-8'}</textarea>
                    <p class="help-block">{l s='Provide a brief description for the payment option that will appear on the checkout page. To use default, keep this field empty.' mod='craftgate_payment_orchestration'}</p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Live API Key' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CRAFTGATE_LIVE_API_KEY" value="{$CRAFTGATE_LIVE_API_KEY|escape:'html':'UTF-8'}">
                    <p class="help-block">{l s='Enter the live API key provided by Craftgate.' mod='craftgate_payment_orchestration'}</p>
                </div>

                <label class="control-label col-lg-3">{l s='Live Secret Key' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CRAFTGATE_LIVE_SECRET_KEY" value="{$CRAFTGATE_LIVE_SECRET_KEY|escape:'html':'UTF-8'}">
                    <p class="help-block">{l s='Enter the live secret key provided by Craftgate.' mod='craftgate_payment_orchestration'}</p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Sandbox API Key' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CRAFTGATE_SANDBOX_API_KEY" value="{$CRAFTGATE_SANDBOX_API_KEY|escape:'html':'UTF-8'}">
                    <p class="help-block">{l s='Enter the sandbox API key for testing.' mod='craftgate_payment_orchestration'}</p>
                </div>

                <label class="control-label col-lg-3">{l s='Sandbox Secret Key' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CRAFTGATE_SANDBOX_SECRET_KEY" value="{$CRAFTGATE_SANDBOX_SECRET_KEY|escape:'html':'UTF-8'}">
                    <p class="help-block">{l s='Enter the sandbox secret key for testing.' mod='craftgate_payment_orchestration'}</p>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Sandbox Mode' mod='craftgate_payment_orchestration'}</label>
                    <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="CRAFTGATE_IS_SANDBOX_ACTIVE" id="CRAFTGATE_IS_SANDBOX_ACTIVE_on" value="1"
                               {if $CRAFTGATE_IS_SANDBOX_ACTIVE}checked="checked"{/if}>
                        <label for="CRAFTGATE_IS_SANDBOX_ACTIVE_on">{l s='Yes' mod='craftgate_payment_orchestration'}</label>
                        <input type="radio" name="CRAFTGATE_IS_SANDBOX_ACTIVE" id="CRAFTGATE_IS_SANDBOX_ACTIVE_off" value="0"
                               {if !$CRAFTGATE_IS_SANDBOX_ACTIVE}checked="checked"{/if}>
                        <label for="CRAFTGATE_IS_SANDBOX_ACTIVE_off">{l s='No' mod='craftgate_payment_orchestration'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                        <p class="help-block">{l s='Enable or disable sandbox mode for testing the integration.' mod='craftgate_payment_orchestration'}</p>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Iframe Options' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CRAFTGATE_IFRAME_OPTIONS" value="{$CRAFTGATE_IFRAME_OPTIONS|escape:'html':'UTF-8'}">
                    <p class="help-block">{l s='Example: hideFooter=true&hideHeader=true' mod='craftgate_payment_orchestration'}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Webhook URL' mod='craftgate_payment_orchestration'}</label>
                <div class="col-lg-9">
                    <input disabled type="text" name="CRAFTGATE_IFRAME_OPTIONS" value="{$CRAFTGATE_WEBHOOK_URL|escape:'html':'UTF-8'}">
                    <p class="help-block">{l s='The URL that payment results will be sent to on the server-side. You should enter this webhook address to Craftgate Merchant Panel to get webhook request.' mod='craftgate_payment_orchestration'}</p>
                </div>
            </div>

            <div class="panel-footer">
                <button type="submit" value="1" id="module_form_submit_btn" name="submitCraftgateModuleConfiguration" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> {l s='Save' mod='craftgate_payment_orchestration'}
                </button>
            </div>
        </div>
    </div>
</form>
