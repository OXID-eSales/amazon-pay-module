[{if !$oxcmp_user && !$oView->getLoginOption() && $oViewConf->isAmazonActive() && !$oViewConf->isAmazonSessionActive() && !$oViewConf->socialLoginDeactivated()}]
    [{include file="amazonpay/checkout_user_main.tpl"}]
[{/if}]
[{$smarty.block.parent}]