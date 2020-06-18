[{if !$oxcmp_user && !$oView->getLoginOption() && $oViewConf->isAmazonActive() && !$oViewConf->isAmazonExclude()}]
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">[{"AMAZON_PAY"|oxmultilangassign|oxupper}]</h3>
                </div>
                <div class="panel-body">
                    [{oxmultilang ident="AMAZON_PAY_GUARANTEE"}]
                </div>
                <div class="panel-footer">
                    [{include file="amazonbutton.tpl" buttonId="AmazonPayButtonCheckoutUser"}]
                </div>
            </div>
        </div>
    </div>
[{/if}]
[{$smarty.block.parent}]
