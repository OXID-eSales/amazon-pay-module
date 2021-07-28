[{if !$oxcmp_user && !$oView->getLoginOption() && $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive()}]
    [{include file="amazonpay/flow_checkout_user_main.tpl"}]
[{/if}]
[{$smarty.block.parent}]