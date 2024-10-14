<style>
    .payment-section {
        text-align: center;
    }

    .payment-cards {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .card-logo {
        width: 60px;
        height: auto;
        max-width: 100%;
        object-fit: contain;
    }

    @media (max-width: 768px) {
        .card-logo {
            width: 50px;
        }
    }

</style>
<section id="craftgate-checkout-form-po" class="payment-section">
    <p>{l s=$description mod='craftgate_payment_orchestration'}</p>

    <div class="payment-cards">
        <img src="{$urls.base_url}/modules/craftgate_payment_orchestration/views/img/cards/visa-electron.svg" alt="visa-electron" class="card-logo"/>
        <img src="{$urls.base_url}/modules/craftgate_payment_orchestration/views/img/cards/visa.svg" alt="visa" class="card-logo"/>
        <img src="{$urls.base_url}/modules/craftgate_payment_orchestration/views/img/cards/mastercard.svg" alt="mastercard" class="card-logo"/>
        <img src="{$urls.base_url}/modules/craftgate_payment_orchestration/views/img/cards/maestro.svg" alt="maestro" class="card-logo"/>
        <img src="{$urls.base_url}/modules/craftgate_payment_orchestration/views/img/cards/amex.svg" alt="amex" class="card-logo"/>
        <img src="{$urls.base_url}/modules/craftgate_payment_orchestration/views/img/cards/troy.svg" alt="troy" class="card-logo"/>
    </div>
</section>
