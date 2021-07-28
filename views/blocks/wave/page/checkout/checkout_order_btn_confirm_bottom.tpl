[{if ($oViewConf->isAmazonActive() && $oView->isAmazonPaymentPossible()) || !$oViewConf->isAmazonActive()}]
    [{$smarty.block.parent}]
[{/if}]
