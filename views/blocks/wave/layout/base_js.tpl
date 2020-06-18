[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive()}]
    [{oxscript include="https://static-eu.payments-amazon.com/checkout.js" priority=1}]
    [{oxscript include=$oViewConf->getModuleUrl('oxps/amazonpay', 'out/src/js/amazonpay.min.js') priority=1}]
[{/if}]

