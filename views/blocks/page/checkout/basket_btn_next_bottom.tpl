[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && $oViewConf->displayExpressInMiniCartAndModal()}]
    <div class="clearfix" style="margin-bottom: 15px;"></div>
    [{if !$oViewConf->isAmazonSessionActive()}]
        [{include file="amazonpay/basket_btn_next_bottom.tpl"}]
    [{else}]
        [{include file='amazonpay/amazonpayhint.tpl' withBreak=true alignLeft=false alignRight=true }]
    [{/if}]
[{/if}]