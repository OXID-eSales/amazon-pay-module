[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
    [{include file="amazonpay/basket_btn_next_bottom.tpl"}]
[{/if}]
