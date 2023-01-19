[{if $paymentId == $oViewConf->getAmazonPaymentId()}]
    <div class="pull-right">
        [{include file="amazonpay/amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2" placement="Cart"}]
    </div>
    <div class="pull-right amazonpay-button-or">
        [{"AMAZON_PAY_SUBMIT_ORDER_WITH"|oxmultilangassign}]
    </div>
[{/if}]


