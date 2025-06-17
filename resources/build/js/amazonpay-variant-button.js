oscAmazonPayDebug = false;
const oscAmazonPayBasketSelector = 'toBasket';
const oscAmazonPayExpressButtonSelector = 'amazonpayexpressbutton';
const oscAmazonPayExpressButtonSelectorId = '#' + oscAmazonPayExpressButtonSelector;
const oscAmazonPayDisabledClass = 'amazonpay-button-disabled';
const oscAmazonPayEnabledClass = 'amazonpay-button-enabled';
const oscAmazonPayButtonContainer = '.amazonpay-button-container';

oscAmazonPayRegisterAmazonPayClickHandler = () => {
    oscAmazonPayExpressButton.onClick(function () {
        var BasketElem = document.getElementById(oscAmazonPayBasketSelector);
        // the disabled amazonpay button can still be clicked; prevent this when the basket is disabled
        if (typeof BasketElem !== 'undefined' && !BasketElem.disabled) {
            // replace() fixes the json output string, twigs "|json_encode" on the array differs from php json_encode and produces checksum errors
            var payloadJson = document.getElementById('osc_amazonpay_payloadJSON').value.replace(/\\/g, "");
            var signature = document.getElementById('osc_amazonpay_signature').value;
            var publicKeyId = document.getElementById('osc_amazonpay_publicKeyId').value;
            oscAmazonPayExpressButton.initCheckout({
                createCheckoutSessionConfig: {
                    payloadJSON: payloadJson,
                    signature: signature,
                    publicKeyId: publicKeyId
                }
            });
        }
    })
};

oscAmazonPaySetAmazonButtonState = () => {
    var BasketElem = document.getElementById(oscAmazonPayBasketSelector);
    var oscAmazonPayExpressButton = document.getElementById(oscAmazonPayExpressButtonSelector);
    var showButtonAndText = true;
    var BasketDisabled = true;

    if (typeof BasketElem !== 'undefined' && BasketElem) {
        // set amazonpay button to the same state the basket button is in
        BasketDisabled = BasketElem.disabled;
    } else if (typeof BasketElem === 'undefined' || !BasketElem) {
        // do not show text and button when basket is not shown on detail page (out-of-stock)
        showButtonAndText = false;
    }

    if (typeof oscAmazonPayExpressButton !== 'undefined' && BasketDisabled === true) {
        document.querySelector(oscAmazonPayExpressButtonSelectorId).shadowRoot.querySelector(oscAmazonPayButtonContainer).classList.replace(oscAmazonPayEnabledClass, oscAmazonPayDisabledClass);
    } else {
        document.querySelector(oscAmazonPayExpressButtonSelectorId).shadowRoot.querySelector(oscAmazonPayButtonContainer).classList.replace(oscAmazonPayDisabledClass, oscAmazonPayEnabledClass);
    }

    if (showButtonAndText) {
        document.getElementById('osc_amazonpay_wrapper').style.display = 'block';
    } else {
        document.getElementById('osc_amazonpay_wrapper').style.display = 'none';
    }

}

oscAmazonPayRenderAmazonButton = () => {
    if (typeof oscAmazonPayButtonIsRendered === "undefined" || oscAmazonPayButtonIsRendered === false) {
        try {
            oscAmazonPayExpressButton = amazon.Pay.renderButton(oscAmazonPayExpressButtonSelectorId, {
                merchantId: document.getElementById('osc_amazonpay_merchantId').value,
                ledgerCurrency: document.getElementById('osc_amazonpay_ledgerCurrency').value,
                sandbox: document.getElementById('osc_amazonpay_isSandbox').value === 'true',
                checkoutLanguage: document.getElementById('osc_amazonpay_checkoutLanguage').value,
                productType: 'PayAndShip',
                placement: document.getElementById('osc_amazonpay_placement').value,
                buttonColor: 'Gold'
            });
            oscAmazonPayButtonIsRendered = true;
        } catch (err) {
            // suppress javascript exception when re-rendering the amazonpay button
            // due to "shadow-root" cannot be manipulated after first render
        }
    }
}

// set render the button and set states initially
oscAmazonPayToBasket = document.getElementById(oscAmazonPayBasketSelector);
if (typeof oscAmazonPayToBasket !== "undefined") {
    oscAmazonPayRenderAmazonButton();
    oscAmazonPaySetAmazonButtonState();
    oscAmazonPayRegisterAmazonPayClickHandler();
}
