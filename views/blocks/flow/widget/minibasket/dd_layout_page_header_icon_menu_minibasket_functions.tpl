[{$smarty.block.parent}]

[{if $oViewConf->isAmazonActive() && $oViewConf->displayInMiniCart() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive()}]
    <div class="pull-right">
        <div class="text-center amazonpay-button-or small">[{"OR"|oxmultilangassign|oxupper}]</div>
        [{include file="amazonbutton.tpl" buttonId="AmazonPayButtonMiniCart" buttonclass="small"}]
    </div>
[{/if}]