[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonSessionActive() && $oViewConf->displayInMiniCart() && !$oViewConf->isAmazonExclude()}]
    [{include file="amazonpay/wave_dd_layout_page_header_icon_menu_minibasket_functions.tpl"}]
[{/if}]
