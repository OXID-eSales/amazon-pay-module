[{if $oViewConf->isAmazonActive() && $oViewConf->isAmazonSessionActive()}]
    <div class="card-deck">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">[{oxmultilang ident="AMAZON_PAY"}]</h3>
            </div>
            <div class="card-body oxEqualized">
                <div class="row">
                    <div class="col-12 col-md-6">
                        [{oxmultilang ident="AMAZON_PAY_PROCESSED"}]
                    </div>
                    <div class="col-12 col-md-6 text-right">
                        <a class="btn btn-outline-dark" href="[{$oViewConf->getCancelAmazonPaymentUrl()}]">[{oxmultilang ident="AMAZON_PAY_UNLINK"}]</a>
                    </div>
                </div>
                [{if !$oViewConf->isAmazonPaymentPossible()}]
                    <div class="row">
                        <div class="col-12">
                            <br />
                            <p class="alert alert-danger">
                                [{oxmultilang ident="AMAZON_PAY_PAYMENT_ERROR"}]
                            </p>
                            <a href="[{oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=user"}]" class="btn btn-warning">
                                [{oxmultilang ident="AMAZON_PAY_CHECKOUT_CHANGE_ADDRESS"}]
                            </a>
                        </div>
                    </div>
                [{/if}]
            </div>
        </div>
    </div>
[{/if}]
