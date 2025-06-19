[{assign var="oPayment" value=$oView->getPayment()}]
[{assign var="paymentId" value=$oPayment->getId()}]
[{if
    (
        $oViewConf->isAmazonActive() &&
        $paymentId == $oViewConf->getAmazonPaymentId() &&
        !$oViewConf->isAmazonExclude()
    )
}]
    [{include file="amazonpay/checkout_order_btn_submit_bottom.tpl" paymentId=$paymentId}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]