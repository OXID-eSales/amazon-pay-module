[{assign var="amazonConfig" value=$oViewConf->getAmazonConfig()}]
[{assign var="sToken" value=$oViewConf->getSessionChallengeToken()}]
[{assign var="aPayload" value=$oViewConf->getPayload()}]
<div class="amazonpay-button [{$buttonclass}] nonexpress" id="[{$buttonId}]"></div>
[{capture name="amazonpay_script"}]
    var amazonPayButton = amazon.Pay.renderButton('#[{$buttonId}]', {
        merchantId: '[{$amazonConfig->getMerchantId()}]',
        publicKeyId : '[{$amazonConfig->getPublicKeyId()}]',
        sandbox: [{if $amazonConfig->isSandbox()}]true[{else}]false[{/if}],
        ledgerCurrency: '[{$amazonConfig->getLedgerCurrency()}]',
        checkoutLanguage: '[{$amazonConfig->getCheckoutLanguage()}]',
        productType: 'PayAndShip',
        placement: 'Checkout'
    });
    OSCAmazonPayButtonComponent.init(amazonPayButton, '[{$aPayload}]', '[{$oViewConf->signature}]');
    OSCAmazonPayCheckoutAGBComponent.init();
[{/capture}]
[{oxscript add=$smarty.capture.amazonpay_script}]
