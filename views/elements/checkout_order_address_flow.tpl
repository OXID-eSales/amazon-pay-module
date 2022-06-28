[{capture name="amazonpay_script"}]
    $('#amznChangeAddress').click(function (e) {
        e.preventDefault();
    });
    amazon.Pay.bindChangeAction('#amznChangeAddress', {
        amazonCheckoutSessionId: '[{$oViewConf->getAmazonSessionId()}]',
        changeAction: 'changeAddress'
    });
[{/capture}]
[{assign var="oDeliveryAddress" value=$oView->getDeliveryAddressAsObj()}]
[{assign var="oBillingAddress" value=$oView->getBillingAddressAsObj()}]
[{oxscript add=$smarty.capture.amazonpay_script}]
<div id="orderAddress" class="row">
    <div class="col-xs-12 col-md-6">
        <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
            <div class="hidden">
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="user">
                <input type="hidden" name="fnc" value="">
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        [{oxmultilang ident="BILLING_ADDRESS"}]
                        <button type="submit" class="btn btn-xs btn-warning pull-right submitButton largeButton" title="[{oxmultilang ident="EDIT"}]">
                            <i class="fa fa-pencil"></i>
                        </button>
                    </h3>
                </div>
                <div class="panel-body">
                    [{include file="amazonpay/filtered_billing_address.tpl" billadr=$oBillingAddress}]
                </div>
            </div>
        </form>
    </div>
    <div class="col-xs-12 col-md-6">
        <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
            <div class="hidden">
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="user">
                <input type="hidden" name="fnc" value="">
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        [{oxmultilang ident="SHIPPING_ADDRESS"}]
                        <button type="submit" id="amznChangeAddress" class="btn btn-xs btn-warning pull-right submitButton largeButton" title="[{oxmultilang ident="EDIT"}]">
                            <i class="fa fa-pencil"></i>
                        </button>
                    </h3>
                </div>
                <div class="panel-body">
                    [{if $oDeliveryAddress}]
                        [{include file="amazonpay/filtered_delivery_address.tpl" delivadr=$oDeliveryAddress}]
                    [{else}]
                        [{include file="amazonpay/filtered_billing_address.tpl" billadr=$oBillingAddress}]
                    [{/if}]
                </div>
            </div>
        </form>
    </div>
</div>