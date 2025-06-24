[{$smarty.block.parent}]
[{if $blCanBuy && $oViewConf->isAmazonActive() && $oViewConf->displayExpressInPDP() && !$oViewConf->isAmazonExclude($oDetailsProduct->oxarticles__oxid->value) && !$oViewConf->isAmazonSessionActive()}]
    [{include file='@osc_amazonpay/frontend/details_productmain_tobasket.tpl'}]
[{/if}]
