[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayInMiniCartAndModal()}]
    [{include file="amazonpay/wave_basket_btn_next_bottom.tpl"}]
[{/if}]
[{$smarty.block.parent}]
