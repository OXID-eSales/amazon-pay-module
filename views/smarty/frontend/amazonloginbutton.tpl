[{assign var="amazonConfig" value=$oViewConf->getAmazonConfig()}]
[{assign var="sToken" value=$oViewConf->getSessionChallengeToken()}]
[{assign var="aPayload" value=$oViewConf->getPayloadSignIn()}]
<div class="amazonpay-button [{$buttonclass}]" id="[{$buttonId}]"></div>
[{capture name="amazonpay_login_script"}]
    amazon.Pay.renderButton('#[{$buttonId}]', {
        merchantId: '[{$amazonConfig->getMerchantId()}]',
        ledgerCurrency: '[{$amazonConfig->getLedgerCurrency()}]',
        sandbox: [{if $amazonConfig->isSandbox()}]true[{else}]false[{/if}],
        checkoutLanguage: '[{$amazonConfig->getCheckoutLanguage()}]',
        productType: 'SignIn',
        placement: 'Cart',
        buttonColor: 'Gold',
        signInConfig: {
            payloadJSON: '[{$aPayload}]',
            signature: '[{$oViewConf->signature}]',
            publicKeyId: '[{$amazonConfig->getPublicKeyId()}]'
        }
    });
[{/capture}]
[{oxscript add=$smarty.capture.amazonpay_login_script}]
