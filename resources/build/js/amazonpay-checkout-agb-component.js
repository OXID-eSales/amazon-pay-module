;(function() {
    window.OSCAmazonPayCheckoutAGBComponent = {
        init: function () {
            this.registerEvents();
        },
        registerEvents: function () {
           if (OSCAmazonPayButtonComponent.forceConfirmAGB()) {
                document.getElementById('checkAgbTop').on('click', function () {
                    document.getElementByClassName('agbConfirmation').removeClass('alert-danger');
                    OSCAmazonPayButtonComponent.hideErrorContainer();
                    OSCAmazonPayCheckoutAGBComponent.saveAGBConfirmInSession();
                });
            }
            if (OSCAmazonPayButtonComponent.forceConfirmDPA()) {
                document.getElementById('oxdownloadableproductsagreement').on('click', function () {
                    document.getElementByClassName('agbConfirmation').removeClass('alert-danger');
                    OSCAmazonPayButtonComponent.hideErrorContainer();
                    OSCAmazonPayCheckoutAGBComponent.saveDpaConfirmInSession();
                });
            }
            if (OSCAmazonPayButtonComponent.forceConfirmSPA()) {
                document.getElementById('oxserviceproductsagreement').on('click', function () {
                    document.getElementByClassName('agbConfirmation').removeClass('alert-danger');
                    OSCAmazonPayButtonComponent.hideErrorContainer();
                    OSCAmazonPayCheckoutAGBComponent.saveSpaConfirmInSession();
                });
            }
        },
        isAgbConfirmed: function() {
            return document.getElementById('checkAgbTop').is(':checked');
        },
        isDpaConfirmed: function() {
            return document.getElementById('oxdownloadableproductsagreement').is(':checked');
        },
        isSpaConfirmed: function() {
            return document.getElementById('oxserviceproductsagreement').is(':checked');
        },
        saveAGBConfirmInSession() {
            document.querySelector.ajax({
                type: "POST",
                url: "/index.php?cl=amazoncheckoutajax&fnc=confirmAGB",
                data: {confirm: (this.isAgbConfirmed() ? 1 : 0)}
            });
        },
        saveDpaConfirmInSession() {
            document.querySelector.ajax({
                type: "POST",
                url: "/index.php?cl=amazoncheckoutajax&fnc=confirmDPA",
                data: {confirm: (this.isDpaConfirmed() ? 1 : 0)}
            });
        },
        saveSpaConfirmInSession() {
            document.querySelector.ajax({
                type: "POST",
                url: "/index.php?cl=amazoncheckoutajax&fnc=confirmSPA",
                data: {confirm: (this.isSpaConfirmed() ? 1 : 0)}
            });
        }
    };
})()