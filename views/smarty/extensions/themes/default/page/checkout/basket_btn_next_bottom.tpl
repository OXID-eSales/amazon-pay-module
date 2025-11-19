[{$smarty.block.parent}]
[{if $oViewConf->isFlowCompatibleTheme()}]
    <div class="clearfix" style="margin-bottom: 15px;"></div>
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
        [{include file="@osc_amazonpay/frontend/basket_btn_next_bottom_flow.tpl"}]
    [{/if}
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && $oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
    [{include file='amazonpay/amazonpayhint.tpl' withBreak=true}]
    [{/if}]
[{else}]
    <div class="clearfix" style="margin-bottom: 15px;"></div>
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
        [{include file="@osc_amazonpay/frontend/basket_btn_next_bottom_wave.tpl"}]
    [{/if}]
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && $oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
        [{include file='amazonpay/amazonpayhint.tpl' withBreak=true}]
    [{/if}]
[{/if}]