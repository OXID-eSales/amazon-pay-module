[{if $oViewConf->isAmazonActive() && $oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
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
    <h3 class="card-title">
        [{oxmultilang ident="SHIPPING_ADDRESS"}]
        <button id="userChangeShippingAddress"
                class="btn btn-sm btn-warning float-right submitButton largeButton edit-button"
                name="userChangeBillingAddress"
                type="submit"
                title="[{oxmultilang ident="CHANGE"}]">
            <i class="fas fa-pencil-alt"></i>
        </button>
    </h3>
[{else}]
    [{$smarty.block.parent}]
[{/if}]