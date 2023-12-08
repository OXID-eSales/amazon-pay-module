;(function() {
    window.OSCAmazonPayButtonComponent = {
        amazonPayButton: null,
        payLoad: null,
        signature: null,

        init: function (amazonPayButton, payloadJSON, signature) {
            // replace the "Zur Kasse" button by using javascript
            // until we get a block to replace it by template
            // for now identify the button by using css classes
            buttonClasses = 'btn btn-highlight btn-lg w-100'
            document.getElementsByClassName(buttonClasses)[0].parentNode.append(document.getElementById('AmazonPayWrapper'))
            document.getElementsByClassName(buttonClasses)[0].style.display = "none"

            this.amazonPayButton = amazonPayButton;
            this.payloadJSON = payloadJSON;
            this.signature = signature;
            this.registerEvents();
        },

        registerEvents: function () {
            amazonPayButton.onClick(function(){
                console.log('amazonpay onCLick regsitered');
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