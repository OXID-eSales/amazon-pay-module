;(function() {
    window.OSCAmazonPayButtonComponent = {
        amazonPayButton: null,
        payLoad: null,
        signature: null,

        init: function (amazonPayButton, payloadJSON, signature) {
            // replace the "Zur Kasse" button by using javascript
            // until we get a block to replace it by template
            // for now identify the button by using css classes
            buttonClassesApex = 'btn btn-highlight btn-lg w-100'
            buttonClassesTwig = 'btn btn-lg btn-primary pull-right submitButton nextStep largeButton'
            if (document.getElementsByClassName(buttonClassesApex)[0])
            {
                document.getElementsByClassName(buttonClassesApex)[0].parentNode.append(document.getElementById('AmazonPayWrapper'))
                document.getElementsByClassName(buttonClassesApex)[0].style.display = "none"
            }
            if (document.getElementsByClassName(buttonClassesTwig)[0]) {
                document.getElementsByClassName(buttonClassesTwig)[0].parentNode.prepend(document.getElementById('AmazonPayWrapper'))
                document.getElementsByClassName(buttonClassesTwig)[0].style.display = "none"
            }
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
                if (document.getElementById('confirm-agb-error-container')){
                    document.getElementById('confirm-agb-error-container').setAttribute('style', 'display:block');
                    document.getElementsByClassName('agbConfirmation')[0].classList.add('alert-danger');
                }
            }
        },

        hideErrorContainer: function() {
            document.getElementById('confirm-agb-error-container').setAttribute('style', 'display:none');
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