[{assign var="oDeliveryAddress" value=$oView->getFilteredDeliveryAddress()}]
[{if $oViewConf->isAmazonActive() && $oViewConf->isAmazonSessionActive() && $oDeliveryAddress}]
    <div id="shippingAddress" class="col-lg-9 offset-lg-3">
        [{include file="widget/address/shipping_address.tpl" delivadr=$oDeliveryAddress}]
    </div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
