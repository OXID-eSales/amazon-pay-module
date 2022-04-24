[{assign var="amazonConfig" value=$oViewConf->getAmazonConfig()}]
<div class="amazonpay-button [{$buttonclass}]" id="[{$buttonId}]"></div>
[{capture name="amazonpay_script"}]
    amazon.Pay.renderButton('#[{$buttonId}]', {
        merchantId: '[{$amazonConfig->getMerchantId()}]',
        createCheckoutSession: {
            url: '[{$amazonConfig->getCreateCheckoutUrl()}][{if $oxArticlesId}]&anid=[{$oxArticlesId}][{/if}]'
        },
        sandbox: [{if $amazonConfig->isSandbox()}]true[{else}]false[{/if}],
        ledgerCurrency: '[{$amazonConfig->getLedgerCurrency()}]',
        checkoutLanguage: '[{$amazonConfig->getCheckoutLanguage()}]',
        productType: [{if $amazonConfig->getPayType()}]'[{$amazonConfig->getPayType()}]'[{else}]'PayAndShip'[{/if}],
        placement: 'Cart'
    });
[{/capture}]
[{oxscript add=$smarty.capture.amazonpay_script}]
