[{assign var="oPayment" value=$oView->getPayment()}]
[{assign var="paymentId" value=$oPayment->getId()}]
[{if $paymentId == $oViewConf->getAmazonPaymentId()}]
    [{assign var="oConfig" value=$oViewConf->getConfig()}]
    [{assign var="confirmAGB" value=$oConfig->getConfigParam('blConfirmAGB')}]
    [{oxscript include=$oViewConf->getModuleUrl('osc_amazonpay', 'out/src/js/amazonpay.min.js')}]
    <p class="alert alert-danger" id="confirm-agb-error-container" [{if $confirmAGB eq 1}]data-oxid-agb-force-confirm="1"[{/if}]>
        [{"READ_AND_CONFIRM_TERMS"|oxmultilangassign}]
    </p>
    <div class="float-right">
        [{include file="amazonpay/amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2"}]
    </div>
    <div class="float-right amazonpay-button-or">
        [{"AMAZON_PAY_SUBMIT_ORDER_WITH"|oxmultilangassign}]
    </div>
[{/if}]


