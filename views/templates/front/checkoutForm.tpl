{extends "$layout"}
{block name="content"}
    {if isset($error)}
        <section id="craftgate-checkout-form" class="card card-block mb-2" style="min-height: 60vh">
            <p class="alert alert-danger">
                {$error|escape:'htmlall':'UTF-8'}
            </p>
        <section
    {else}
        <section id="craftgate-checkout-form" class="card card-block mb-2">
            <div id="craftgate-checkout-form-iframe-container">
                <iframe src="{$checkoutFormUrl}"></iframe>
            </div>
        </section>
    {/if}
{/block}

{block name="footer"}
    <script>
    const actionUrl = '{$link->getModuleLink('craftgate_payment_orchestration', 'checkoutFormCallbackHandler')|escape:'javascript':'UTF-8'}'
       {literal}
           function submitCheckoutCompletedForm({token,cartId}) {
               const formHTML = `
                <form id="craftgate-callback-complete-form" action="${actionUrl}" method="POST">
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

            window.addEventListener("message", function (event) {
                const {type, value} = event.data;
                if (type === 'HEIGHT_CHANGED') {
                    document.getElementById('craftgate-checkout-form-iframe-container').style.height = value + 'px';
                }else if(type === 'PRESTASHOP_CHECKOUT_COMPLETED'){
                    submitCheckoutCompletedForm(value)
                }
            });
        {/literal}
    </script>
{/block}