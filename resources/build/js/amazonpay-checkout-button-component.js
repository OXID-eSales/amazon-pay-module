;(function() {
    window.OSCAmazonPayButtonComponent = {
        amazonPayButton: null,
        payLoad: null,
        signature: null,

        init: function (amazonPayButton, payloadJSON, signature) {
            this.amazonPayButton = amazonPayButton;
            this.payloadJSON = payloadJSON;
            this.signature = signature;
            this.registerEvents();
        },

        registerEvents: function () {
            amazonPayButton.onClick(function(){
                OSCAmazonPayButtonComponent.payButtonClickHandler();
            });
        },

        payButtonClickHandler: function () {
            if (
                (!this.forceConfirmAGB() || OSCAmazonPayCheckoutAGBComponent.isAgbConfirmed()) &&
                (!this.forceConfirmDPA() || OSCAmazonPayCheckoutAGBComponent.isDpaConfirmed()) &&
                (!this.forceConfirmSPA() || OSCAmazonPayCheckoutAGBComponent.isSpaConfirmed())
            ) {
                this.amazonPayButton.initCheckout({
                    createCheckoutSessionConfig: {
                        payloadJSON: this.payloadJSON,
                        signature: this.signature,
                    }
                });
            } else if(this.forceConfirmAGB() || this.forceConfirmDPA() || this.forceConfirmSPA()) {
                document.getElementById('confirm-agb-error-container').css('display', 'block');
                document.getElementByClassName('agbConfirmation').addClass('alert-danger');
            }
        },

        hideErrorContainer: function() {
            document.getElementById('confirm-agb-error-container').css('display', 'none');
        },

        forceConfirmAGB: function () {
            return document.getElementById('confirm-agb-error-container').dataset.oxidAgbForceConfirm;
        },

        forceConfirmDPA: function () {
            return document.getElementById('confirm-agb-error-container').dataset.oxidDpaForceConfirm;
        },

        forceConfirmSPA: function () {
            return document.getElementById('confirm-agb-error-container').dataset.oxidSpaForceConfirm;
        }
    };
})()