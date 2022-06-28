[{if ($oViewConf->isAmazonActive() && $oViewConf->isAmazonSessionActive() && $oViewConf->isAmazonPaymentPossible()) || !$oViewConf->isAmazonActive() || !$oViewConf->isAmazonSessionActive()}]
    [{$smarty.block.parent}]
[{/if}]
