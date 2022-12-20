[{$smarty.block.parent}]
[{if $blCanBuy && $oViewConf->isAmazonActive() && $oViewConf->displayExpressInPDP() && !$oViewConf->isAmazonExclude($oDetailsProduct->oxarticles__oxid->value) && !$oViewConf->isAmazonSessionActive()}]
    [{include file='amazonpay/details_productmain_tobasket.tpl'}]
[{/if}]
