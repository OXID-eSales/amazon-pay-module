[{assign var="oPayment" value=$oView->getPayment()}]
[{assign var="paymentId" value=$oPayment->getId()}]
paymentid top level: [{$paymentId}]<br />
[{if
    (
        $oViewConf->isAmazonActive() &&
        $paymentId == $oViewConf->getAmazonPaymentId() &&
        !$oViewConf->isAmazonExclude()
    )
}]
    [{if $oViewConf->isFlowCompatibleTheme()}]
        [{include file="amazonpay/checkout_order_btn_submit_bottom_flow.tpl" paymentId=$paymentId}]
    [{else}]
        [{include file="amazonpay/checkout_order_btn_submit_bottom_wave.tpl"}]
    [{/if}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]