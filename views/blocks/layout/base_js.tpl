[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive()}]
    [{oxscript include="https://static-eu.payments-amazon.com/checkout.js" priority=1}]
[{/if}]

