[{if $oViewConf->isAmazonActive()}]
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">[{oxmultilang ident="AMAZON_PAY"}]</h3>
        </div>
        <div class="panel-body">
            [{if !$oViewConf->isAmazonSessionActive()}]
                <div class="pull-left">
                    [{include file='amazonpay/amazonexpressbutton.tpl' buttonId='AmazonPayButtonCheckOutUser' display='inline-block'}]
                    <br />
                    [{oxmultilang ident="AMAZON_PAY_ADVANTAGES"}]
                </div>
            [{else}]
                <div class="pull-left">
                    [{oxmultilang ident="AMAZON_PAY_PROCESSED"}]
                </div>
                <div class="pull-right">
                    <a class="btn btn-default" href="[{$oViewConf->getCancelAmazonPaymentUrl()}]">[{oxmultilang ident="AMAZON_PAY_UNLINK"}]</a>
                </div>
                [{if !$oViewConf->isAmazonPaymentPossible()}]
                    <p class="alert alert-danger">
                        [{oxmultilang ident="AMAZON_PAY_PAYMENT_ERROR"}]
                    </p>
                    <a href="[{oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=user"}]" class="btn btn-warning">
                        [{oxmultilang ident="AMAZON_PAY_CHECKOUT_CHANGE_ADDRESS"}]
                    </a>
                [{/if}]
            [{/if}]
        </div>
    </div>
[{/if}]