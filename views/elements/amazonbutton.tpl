[{assign var="amazonConfig" value=$oViewConf->getAmazonConfig()}]
[{assign var="sToken" value=$oViewConf->getSessionChallengeToken()}]
<script src="https://static-eu.payments-amazon.com/checkout.js"></script>
<div class="amazonpay-button [{$buttonclass}]" id="[{$buttonId}]"></div>
[{capture name="amazonpay_script"}]
    amazon.Pay.renderButton('#[{$buttonId}]', {
        merchantId: '[{$amazonConfig->getMerchantId()}]',
        sandbox: [{if $amazonConfig->isSandbox()}]true[{else}]false[{/if}],
        ledgerCurrency: '[{$amazonConfig->getLedgerCurrency()}]',
        checkoutLanguage: '[{$amazonConfig->getCheckoutLanguage()}]',
        productType: 'PayAndShip',
        placement: 'Cart',
        createCheckoutSessionConfig: {
            payloadJSON: '[{$oViewConf->getPayload()}]',
            signature: '[{$oViewConf->getSignature()}]',
            publicKeyId: '[{$amazonConfig->getPublicKeyId()}]'
        }
    });
[{/capture}]
[{oxscript add=$smarty.capture.amazonpay_script}]
