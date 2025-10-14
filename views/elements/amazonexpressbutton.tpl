[{assign var="amazonConfig" value=$oViewConf->getAmazonConfig()}]
[{assign var="sToken" value=$oViewConf->getSessionChallengeToken()}]
[{assign var="oxArticlesId" value=$oxArticlesId|default:""}]
[{if $disableAutomaticAddToCart}]
    [{assign var="oxArticlesId" value=null}]
    [{/if}]
[{assign var="aPayload" value=$oViewConf->getPayloadExpress($oxArticlesId)}]
[{assign var="isUserLoggedIn"  value=$oViewConf->isUserLoggedIn()}]
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
    [{* when user is already logged in add a redirect to start amazon-non-express flow instead of using AmazonExpress *}]
    [{if $isUserLoggedIn}]
        document.getElementById('[{$buttonId}]').addEventListener('click', function(event){
            event.preventDefault();
            window.location.href = '[{$oViewConf->getSelfActionLink()}]' + '&cl=order&useAmazonNonExpress=true' +'&anid=[{$oxArticlesId}]';
        });
    [{/if}]
[{/capture}]
[{oxscript add=$smarty.capture.amazonpay_script}]
