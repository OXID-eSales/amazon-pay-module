[{assign var="oBillingAddress" value=$oView->getFilteredBillingAddress()}]
[{if $oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude() && $oBillingAddress}]
    <div class="col-lg-9 offset-lg-3" id="addressText">
        [{include file="filtered_billing_address.tpl" billadr=$oBillingAddress}]
    </div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]