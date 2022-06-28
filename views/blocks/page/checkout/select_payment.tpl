[{if $sPaymentID != $oViewConf->getAmazonPaymentId()}]
    [{$smarty.block.parent}]
[{/if}]