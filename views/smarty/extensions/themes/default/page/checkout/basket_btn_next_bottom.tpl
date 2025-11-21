[{$smarty.block.parent}]
[{if $oViewConf->isFlowCompatibleTheme()}]
    <div class="clearfix" style="margin-bottom: 15px;"></div>
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
    [{include file="@osc_amazonpay/frontend/basket_btn_next_bottom_flow.tpl"}]
    [{/if}]
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && $oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
        <div class="clearfix" style="margin-bottom: 15px;"></div>
        [{include file='@osc_amazonpay/frontend/amazonpayhint.tpl' withBreak=true alignLeft=false alignRight=true }]
    [{/if}]
[{else}]
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
        [{include file="@osc_amazonpay/frontend/basket_btn_next_bottom_wave.tpl"}]
    [{/if}]
    [{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && $oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
    <div class="clearfix" style="margin-bottom: 15px;"></div>
    [{include file='@osc_amazonpay/frontend/amazonpayhint.tpl' withBreak=true alignLeft=true alignRight=false }]
    [{/if}]
[{/if}]