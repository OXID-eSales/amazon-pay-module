[{if $paymentId == $oViewConf->getAmazonPaymentId()}]
    [{assign var="oConfig" value=$oViewConf->getConfig()}]
    [{assign var="confirmAGB" value=$oConfig->getConfigParam('blConfirmAGB')}]
    [{assign var="confirmIPA" value=$oConfig->getConfigParam('blEnableIntangibleProdAgreement')}]
    [{assign var="confirmDPA" value=false}]
    [{assign var="confirmSPA" value=false}]
    [{if $confirmIPA && $oxcmp_basket->hasArticlesWithDownloadableAgreement()}]
        [{assign var="confirmDPA" value=true}]
    [{/if}]
    [{if $confirmIPA && $oxcmp_basket->hasArticlesWithIntangibleAgreement()}]
        [{assign var="confirmSPA" value=true}]
    [{/if}]
    [{oxscript include=$oViewConf->getModuleUrl('osc_amazonpay', 'out/src/js/amazonpay.min.js')}]
    <p class="alert alert-danger" id="confirm-agb-error-container"
       [{if $confirmAGB eq 1}] data-oxid-agb-force-confirm="1"[{/if}]
       [{if $confirmDPA eq 1}] data-oxid-dpa-force-confirm="1"[{/if}]
       [{if $confirmSPA eq 1}] data-oxid-spa-force-confirm="1"[{/if}]
    >
        [{"READ_AND_CONFIRM_TERMS"|oxmultilangassign}]
    </p>
    <div class="pull-right">
        [{include file="amazonpay/amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2" placement="Cart"}]
    </div>
    <div class="pull-right amazonpay-button-or">
        [{"AMAZON_PAY_SUBMIT_ORDER_WITH"|oxmultilangassign}]
    </div>
[{/if}]


