[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayExpressInMiniCartAndModal()}]
    [{include file="amazonpay/dd_layout_page_header_icon_menu_minibasket_functions.tpl"}]
[{/if}]
