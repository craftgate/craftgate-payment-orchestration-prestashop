<div id="loading-spinner" class="display-none">
    <div class="lds-facebook">
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
<section>
    <div id="craftgate-one-page-checkout-form-iframe-container" class="display-none"></div>
    <div id="craftgate-error-container"></div>
</section>


<script>
    var resultHandlerActionUrl = '{$link->getModuleLink('craftgate_payment_orchestration', 'checkoutFormCallbackHandler')|escape:'javascript':'UTF-8'}'
    var checkoutFormContainer = document.getElementById('craftgate-one-page-checkout-form-iframe-container');
    var iframeUrl = null;

    document.addEventListener('DOMContentLoaded', function () {
        const paymentRadio = document.querySelector('input[name="payment-option"][data-module-name="craftgate_payment_orchestration"]');
        const allPaymentOptions = document.querySelectorAll('input[name="payment-option"]');

        if (paymentRadio && paymentRadio.checked) {
            handleCraftgatePaymentMethodSelection();
        }

        allPaymentOptions.forEach(function (radio) {
            radio.addEventListener('change', function (event) {
                if (paymentRadio.checked) {
                    handleCraftgatePaymentMethodSelection();
                } else {
                    checkoutFormContainer.classList.add("display-none");
                    hideCraftgatePaymentOptionError()
                }
            });
        });
    });

    function initCheckoutForm() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '{$link->getModuleLink('craftgate_payment_orchestration', 'checkoutForm')|escape:'javascript':'UTF-8'}',
                type: 'POST',
                data: {
                    isOnePageCheckout: true,
                },
                dataType: 'json',
                success: function (response) {
                    resolve(response)
                },
                error: function (xhr) {
                    reject(xhr.responseJSON)
                }
            });
        })
    }

    {literal}
    async function handleCraftgatePaymentMethodSelection() {
        console.log("Craftgate payment method is selected!")
        showLoadingSpinner();
        try {
            if (!iframeUrl) {
                const {checkoutFormUrl} = await initCheckoutForm();
                iframeUrl = checkoutFormUrl;
            }
            checkoutFormContainer.innerHTML = `<iframe src="${iframeUrl}" style="visibility: hidden"></iframe>`;
        } catch (e) {
            console.error("Error occurred while initializing checkout form", e);
            const {errorMessage} = e;
            hideLoadingSpinner();
            showCraftgatePaymentOptionError(errorMessage);
        }
    }

    window.addEventListener("message", function (event) {
        const {type, value} = event.data;
        if (type === 'HEIGHT_CHANGED') {
            checkoutFormContainer.style.height = value + 'px';
        } else if (type === 'SUBMIT_BUTTON_ENABLED') {
            handleCheckoutFormSubmitButtonStatusChange(value);
        } else if (type === 'LOAD_COMPLETED') {
            checkoutFormContainer.classList.remove("display-none");
            addCustomCss()
            hideLoadingSpinner();
            checkoutFormContainer.querySelector("iframe").style.removeProperty('visibility')
            scrollToPaymentOption();
        } else if (type === 'PRESTASHOP_CHECKOUT_COMPLETED') {
            submitCheckoutCompletedForm(value)
        }
    })

    function showLoadingSpinner() {
        document.getElementById("loading-spinner").classList.remove('display-none');
    }

    function hideLoadingSpinner() {
        document.getElementById("loading-spinner").classList.add('display-none');
    }

    function addCustomCss() {
        var iframeElement = checkoutFormContainer.querySelector("iframe");
        iframeElement.contentWindow.postMessage({
            type: 'CUSTOMIZE_UI',
            value: {
                cssRules: {
                    '.environment-info': {
                        display: 'none'
                    },
                    '.container': {
                        padding: 0
                    },
                    '.main-card-brands': {
                        margin: '0.5rem 0'
                    },
                    '.footer .icons': {
                        'margin-bottom': 0
                    },
                    '.footer-iframe': {
                        'margin-top': '-2rem'
                    }
                },
            }
        }, '*')
    }

    function scrollToPaymentOption() {
        const element = document.querySelector('input[name="payment-option"][data-module-name="craftgate_payment_orchestration"]');
        if (element) {
            const elementTop = element.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({
                top: elementTop - 5,
                behavior: 'smooth'
            });
        }
    }

    function showCraftgatePaymentOptionError(errorMessage) {
        document.getElementById("craftgate-error-container").innerHTML = `
            <p class="alert alert-danger">
                ${errorMessage}
            </p>`
    }

    function hideCraftgatePaymentOptionError() {
        document.getElementById("craftgate-error-container").innerHTML = ``
    }

    function submitCheckoutCompletedForm({token, cartId}) {
        const formHTML = `
                <form id="craftgate-callback-complete-form" action="${resultHandlerActionUrl}" method="POST">
                    <input type="hidden" name="token" value="${token}">
                    <input type="hidden" name="cartId" value="${cartId}">
                    <input type="hidden" name="source" value="prestashop">
                </form>`;

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = formHTML;

        document.body.appendChild(tempDiv);

        const form = document.getElementById('craftgate-callback-complete-form');
        setTimeout(() => form.submit(), 1)
    }
    {/literal}
</script>
