[{capture name="amazonpay_script"}]
$('#userChangeShippingAddress').click(function (e) {
    e.preventDefault();
});
amazon.Pay.bindChangeAction('#userChangeShippingAddress', {
    amazonCheckoutSessionId: '[{$oViewConf->getAmazonSessionId()}]',
    changeAction: 'changeAddress'
});
[{/capture}]
[{oxscript add=$smarty.capture.amazonpay_script}]
<h3 class="panel-title">
    [{oxmultilang ident="SHIPPING_ADDRESS"}]
    <button id="userChangeShippingAddress"
            class="btn btn-xs btn-warning pull-right submitButton largeButton"
            name="changeShippingAddress"
            type="submit"
            title="[{oxmultilang ident="CHANGE"}]">
        <i class="fa fa-pencil"></i>
    </button>
</h3>
