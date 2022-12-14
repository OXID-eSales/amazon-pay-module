[{assign var="oPayment" value=$oView->getPayment()}]
[{assign var="paymentId" value=$oPayment->getId()}]
[{if $paymentId == $oViewConf->getAmazonPaymentId()}]
    <div class="float-right">
        [{include file="amazonpay/amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2"}]
    </div>
    <div class="float-right amazonpay-button-or">
        [{"AMAZON_PAY_SUBMIT_ORDER_WITH"|oxmultilangassign}]
    </div>
[{/if}]


