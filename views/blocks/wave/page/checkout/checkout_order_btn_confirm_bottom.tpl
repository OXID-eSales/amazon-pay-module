[{if ($oViewConf->isAmazonActive() && $oViewConf->isAmazonPaymentPossible()) || !$oViewConf->isAmazonActive()}]
    [{$smarty.block.parent}]
[{/if}]
