<section id="{$moduleName}-displayAdminOrderMainBottom">
    <div class="card mt-2">
        <div class="card-header">
            <h3 class="card-header-title">
                <img src="{$moduleLogoSrc}" alt="{$moduleDisplayName}" width="20" height="20"/>
                {$moduleDisplayName}
            </h3>
        </div>
        <div class="card-body">
            <div class="payment-details">
                <h3>{l s='Payment Details' mod='craftgate_payment_orchestration'}</h3>
                <p><strong>{l s='Payment ID' mod='craftgate_payment_orchestration'}:</strong> {$craftgatePayment->payment_id}</p>
                <p><strong>{l s='Craftgate Order ID' mod='craftgate_payment_orchestration'}:</strong> {$craftgatePayment->getMetaData()->orderId}</p>
                <p><strong>{l s='Paid Price' mod='craftgate_payment_orchestration'}
                        :</strong> {displayPrice currency=$currency price=$craftgatePayment->getMetaData()->paidPrice}
                </p>
                <p><strong>{l s='Installment' mod='craftgate_payment_orchestration'}:</strong> {$craftgatePayment->getMetaData()->installment}</p>

                {if $craftgatePayment->getMetaData()->installment != 1}
                    <p><strong>{l s='Installment Fee' mod='craftgate_payment_orchestration'}
                            :</strong> {displayPrice price=$craftgatePayment->getMetaData()->installmentFee}</p>
                {/if}

                {if $craftgatePayment->getMetaData()->environment == 'Sandbox'}
                    <p><strong>{l s='Payment Detail URL' mod='craftgate_payment_orchestration'}:</strong> <a
                                href="https://sandbox-panel.craftgate.io/payments/{$craftgatePayment->payment_id}"
                                target="_blank">https://sandbox-panel.craftgate.io/payments/{$craftgatePayment->payment_id}</a></p>
                {else}
                    <p><strong>{l s='Payment Detail URL' mod='craftgate_payment_orchestration'}:</strong> <a
                                href="https://panel.craftgate.io/payments/{$craftgatePayment->payment_id}"
                                target="_blank">https://panel.craftgate.io/payments/{$craftgatePayment->payment_id}</a>
                    </p>
                {/if}
            </div>

        </div>
    </div>
</section>
