[{if ($oViewConf->isAmazonActive() && $oViewConf->isAmazonPaymentPossible()) || !$oViewConf->isAmazonActive()}]
    [{$smarty.block.parent}]
[{else}]
    <div class="row">
        <div class="col-12">
            <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
                <div class="hidden">
                    [{$oViewConf->getHiddenSid()}]
                    <input type="hidden" name="cl" value="payment">
                    <input type="hidden" name="fnc" value="">
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            [{oxmultilang ident="AMAZON_PAY_CHECKOUT_ERROR_HEAD"}]
                            <button type="submit" class="btn btn-sm btn-warning float-right submitButton largeButton edit-button" title="[{oxmultilang ident="EDIT"}]">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="alert alert-danger">
                            [{oxmultilang ident="AMAZON_PAY_CHECKOUT_ERROR"}]
                        </p>
                        <a href="#" class="btn btn-warning" onclick="$('#amznChangeAddress')[0].click(); return false;">
                            [{oxmultilang ident="AMAZON_PAY_CHECKOUT_CHANGE_ADDRESS"}]
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
[{/if}]
