[{if !$oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
    <div class="pull-right">
        [{include file="amazonpay/amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2"}]
    </div>
    <div class="pull-right amazonpay-button-or">
        [{"OR"|oxmultilangassign|oxupper}]
    </div>
[{/if}]
