[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && $oViewConf->displayExpressInMiniCartAndModal()}]
    [{if !$oViewConf->isAmazonSessionActive()}]
        [{include file="amazonpay/dd_layout_page_header_icon_menu_minibasket_functions.tpl"}]
    [{else}]
        <div class="clearfix" style="margin-bottom: 15px;"></div>
        [{include file='amazonpay/amazonpayhint.tpl' withBreak=true}]
    [{/if}]
[{/if}]