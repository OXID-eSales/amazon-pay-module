[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
    <div class="float-right">
        [{include file="amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2"}]
    </div>
    <div class="float-right amazonpay-button-or">
        [{"OR"|oxmultilangassign|oxupper}]
    </div>
[{/if}]
[{$smarty.block.parent}]
