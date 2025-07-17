[{$smarty.block.parent}]
[{if $oViewConf->isFlowCompatibleTheme()}]
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
        <div class="clearfix" style="margin-bottom: 15px;"></div>
        [{include file="amazonpay/basket_btn_next_bottom_flow.tpl"}]
    [{/if}]
[{else}]
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
        <div class="clearfix" style="margin-bottom: 15px;"></div>
        [{include file="amazonpay/basket_btn_next_bottom_wave.tpl"}]
    [{/if}]
[{/if}]