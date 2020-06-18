[{if $oViewConf->isAmazonActive() && $oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
    [{capture name="amazonpay_script"}]
        $('#amznChangeAddress').click(function (e) {
            e.preventDefault();
        });
        amazon.Pay.bindChangeAction('#amznChangeAddress', {
            amazonCheckoutSessionId: '[{$oViewConf->getAmazonSessionId()}]',
            changeAction: 'changeAddress'
        });
    [{/capture}]
    [{oxscript add=$smarty.capture.amazonpay_script}]
    <div id="orderAddress" class="row">
        <div class="col-12 col-md-6">
            <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
                <div class="hidden">
                    [{$oViewConf->getHiddenSid()}]
                    <input type="hidden" name="cl" value="user">
                    <input type="hidden" name="fnc" value="">
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            [{oxmultilang ident="BILLING_ADDRESS"}]
                            <button type="submit" class="btn btn-sm btn-warning float-right submitButton Button edit-button" title="[{oxmultilang ident="EDIT"}]">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </h3>
                    </div>
                    <div class="card-body">
                        [{include file="widget/address/billing_address.tpl"}]
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12 col-md-6">
            <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
                <div class="hidden">
                    [{$oViewConf->getHiddenSid()}]
                    <input type="hidden" name="cl" value="user">
                    <input type="hidden" name="fnc" value="">
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            [{oxmultilang ident="SHIPPING_ADDRESS"}]
                            <button type="submit" id="amznChangeAddress" class="btn btn-sm btn-warning float-right submitButton largeButton edit-button" title="[{oxmultilang ident="EDIT"}]">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </h3>
                    </div>
                    <div class="card-body">
                        [{assign var="oDelAdress" value=$oView->getDelAddress()}]
                        [{if $oDelAdress}]
                            [{include file="widget/address/shipping_address.tpl" delivadr=$oDelAdress}]
                        [{else}]
                            [{include file="widget/address/billing_address.tpl"}]
                        [{/if}]
                    </div>
                </div>
            </form>
        </div>
    </div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
