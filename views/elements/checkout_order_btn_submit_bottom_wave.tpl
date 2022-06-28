[{if !$oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
    <div class="float-right">
        [{include file="amazonpay/amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2"}]
    </div>
    <div class="float-right amazonpay-button-or">
        [{"OR"|oxmultilangassign|oxupper}]
    </div>
[{/if}]