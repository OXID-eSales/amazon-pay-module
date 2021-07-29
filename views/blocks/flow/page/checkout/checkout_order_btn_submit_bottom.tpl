[{if $oViewConf->isAmazonActive()}]
    [{include file="amazonpay/flow_checkout_order_btn_submit_bottom.tpl"}]
[{/if}]
[{$smarty.block.parent}]