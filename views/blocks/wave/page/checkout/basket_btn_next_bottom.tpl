[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive()}]
    [{include file="amazonpay/wave_basket_btn_next_bottom.tpl"}]
[{/if}]
[{$smarty.block.parent}]
