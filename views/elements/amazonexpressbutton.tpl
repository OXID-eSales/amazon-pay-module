[{assign var="amazonConfig" value=$oViewConf->getAmazonConfig()}]
[{assign var="sToken" value=$oViewConf->getSessionChallengeToken()}]
[{if !$oxArticlesId}]
    [{assign var="oxArticlesId" value=""}]
[{/if}]
[{assign var="aPayload" value=$oViewConf->getPayloadExpress($oxArticlesId)}]
<div class="amazonpay-button [{$buttonclass}] express" id="[{$buttonId}]"></div>

[{capture name="amazonpay_script"}]
    amazon.Pay.renderButton('#[{$buttonId}]', {
        merchantId: '[{$amazonConfig->getMerchantId()}]',
            sandbox: [{if $amazonConfig->isSandbox()}]true[{else}]false[{/if}],
            ledgerCurrency: '[{$amazonConfig->getLedgerCurrency()}]',
            checkoutLanguage: '[{$amazonConfig->getCheckoutLanguage()}]',
            productType: 'PayAndShip',
            placement: '[{$placement}]',
            createCheckoutSessionConfig: {
                payloadJSON: '[{$aPayload}]',
                signature: '[{$oViewConf->signature}]',
                publicKeyId: '[{$amazonConfig->getPublicKeyId()}]'
        }
    });
[{/capture}]
[{oxscript add=$smarty.capture.amazonpay_script}]
