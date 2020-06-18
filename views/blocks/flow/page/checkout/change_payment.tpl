[{assign var="amazonConfig" value=$oViewConf->getAmazonConfig()}]
[{include file="amazonpay_payment_option.tpl" amazonConfig=$amazonConfig}]

[{if !$oViewConf->isAmazonSessionActive() || $oViewConf->isAmazonExclude()}]
    [{$smarty.block.parent}]
[{/if}]
