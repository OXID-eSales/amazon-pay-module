[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
    <div class="clearfix" style="margin-bottom: 15px;"></div>
    [{include file="amazonpay/basket_btn_next_bottom.tpl"}]
[{/if}]
