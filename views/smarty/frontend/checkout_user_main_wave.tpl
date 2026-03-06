[{block name="amazonpay_checkout_user_main_wave"}]
    <div class="card-deck">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">[{"AMAZON_PAY"|oxmultilangassign|oxupper}]</h3>
            </div>
            <div class="card-body">
                [{oxmultilang ident="AMAZON_PAY_GUARANTEE"}]
            </div>
            <div class="card-footer">
                [{include file="@osc_amazonpay/frontend/amazonloginbutton.tpl" buttonId="AmazonPayButtonCheckoutUser"}]
            </div>
        </div>
    </div>
[{/block}]