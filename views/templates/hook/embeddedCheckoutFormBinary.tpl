<section id="craftgate_payment_orchestration-binary-form" class="js-payment-binary js-payment-craftgate_payment_orchestration disabled disabled-by-craftgate">
    <button id="craftgate-checkout-form-submit-button" type="button" class="btn btn-primary">
        {l s='Place Order' mod='craftgate_payment_orchestration'}
    </button>
</section>

<script>
    window.onload = function () {
        document.getElementById("craftgate-checkout-form-submit-button").addEventListener("click", function () {
            var isButtonDisabled = document.getElementById("craftgate_payment_orchestration-binary-form").classList.contains('disabled-by-craftgate');
            if (isButtonDisabled)
                return;

            var iframeWin = document.querySelector("#craftgate-one-page-checkout-form-iframe-container iframe").contentWindow;
            iframeWin.postMessage({
                type: 'CRAFTGATE_SUBMIT_MASTERPASS_FORM'
            }, '*')
        });
    };

    function handleCheckoutFormSubmitButtonStatusChange(status) {
        if (status === true) {
            document.getElementById("craftgate_payment_orchestration-binary-form").classList.remove('disabled-by-craftgate');
            document.getElementById("craftgate-checkout-form-submit-button").disabled = false;
        } else {
            var isAlreadyDisabled = document.getElementById("craftgate_payment_orchestration-binary-form").classList.contains('disabled-by-craftgate');
            if (!isAlreadyDisabled) {
                document.getElementById("craftgate_payment_orchestration-binary-form").classList.add('disabled-by-craftgate');
                document.getElementById("craftgate-checkout-form-submit-button").disabled = true;
            }
        }
    }
</script>
