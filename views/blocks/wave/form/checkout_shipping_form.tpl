[{if $oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
    [{if $oxcmp_user}]
        [{assign var="delivadr" value=$oxcmp_user->getSelectedAddress()}]
    [{/if}]

    <div class="col-lg-9 offset-lg-3" id="shippingAddress">
        [{include file="widget/address/shipping_address.tpl" delivadr=$delivadr}]
    </div>
    <hr>
[{else}]
    [{$smarty.block.parent}]
[{/if}]