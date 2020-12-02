[{if !$oxcmp_user && !$oView->getLoginOption() && $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude() && !$oViewConf->isAmazonSessionActive()}]
    <div class="card-deck">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">[{"AMAZON_PAY"|oxmultilangassign|oxupper}]</h3>
            </div>
            <div class="card-body">
                [{oxmultilang ident="AMAZON_PAY_GUARANTEE"}]
            </div>
            <div class="card-footer">
                [{include file="amazonbutton.tpl" buttonId="AmazonPayButtonCheckoutUser"}]
            </div>
        </div>
    </div>
[{/if}]
[{$smarty.block.parent}]
