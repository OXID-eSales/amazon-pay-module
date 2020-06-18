[{$smarty.block.parent}]
[{if $oViewConf->isAmazonActive() && !$oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
    <div class="amazonpay-button-or pull-right">
        [{"OR"|oxmultilangassign|oxupper}]
    </div>
    <div class="amazonpay-button small pull-right">
        [{include file="amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2" buttonclass="small"}]
    </div>
[{/if}]