[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayInMiniCartAndModal()}]
    [{include file="amazonpay/flow_basket_btn_next_bottom.tpl"}]
[{/if}]