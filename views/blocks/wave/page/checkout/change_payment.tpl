[{assign var="amazonConfig" value=$oViewConf->getAmazonConfig()}]
[{if !$oViewConf->isAmazonSessionActive()}]
    [{include file="amazonpay_payment_option.tpl" amazonConfig=$amazonConfig}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]

