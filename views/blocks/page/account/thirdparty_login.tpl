[{if !$oxcmp_user && $oViewConf->isAmazonActive() && !$oViewConf->isAmazonSessionActive()}]
    <div>
        <div class="text-center amazonpay-button-or small">[{"OR"|oxmultilangassign|oxupper}]</div>
        [{include file="amazonpay/amazonloginbutton.tpl" buttonId="AmazonPayWidgetCheckoutUser" buttonclass="small"}]
    </div>
[{/if}]
[{$smarty.block.parent}]