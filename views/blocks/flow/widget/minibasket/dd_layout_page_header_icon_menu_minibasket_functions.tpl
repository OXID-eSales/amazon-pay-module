[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive() && $oViewConf->displayInMiniCart() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive()}]
    [{include file="amazonpay/flow_dd_layout_page_header_icon_menu_minibasket_functions.tpl"}]
[{/if}]
