;(function() {
    window.OSCAmazonPayCheckoutAGBComponent = {
        init: function () {
            this.registerEvents();
        },
        registerEvents: function () {
           if (OSCAmazonPayButtonComponent.forceConfirmAGB()) {
                $('#checkAgbTop').on('click', function () {
                    $('.agbConfirmation').removeClass('alert-danger');
                    OSCAmazonPayButtonComponent.hideErrorContainer();
                    OSCAmazonPayCheckoutAGBComponent.saveAGBConfirmInSession();
                });

            }
            if (OSCAmazonPayButtonComponent.forceConfirmDPA()) {
                $('#oxdownloadableproductsagreement').on('click', function () {
                    $('.agbConfirmation').removeClass('alert-danger');
                    OSCAmazonPayButtonComponent.hideErrorContainer();
                    OSCAmazonPayCheckoutAGBComponent.saveDpaConfirmInSession();
                });
            }

            if (OSCAmazonPayButtonComponent.forceConfirmSPA()) {
                $('#oxserviceproductsagreement').on('click', function () {
                    $('.agbConfirmation').removeClass('alert-danger');
                    OSCAmazonPayButtonComponent.hideErrorContainer();
                    OSCAmazonPayCheckoutAGBComponent.saveSpaConfirmInSession();
                });
            }
        },
        isAgbConfirmed: function() {
            return $('#checkAgbTop').is(':checked');
        },
        isDpaConfirmed: function() {
            return $('#oxdownloadableproductsagreement').is(':checked');
        },
        isSpaConfirmed: function() {
            return $('#oxserviceproductsagreement').is(':checked');
        },
        saveAGBConfirmInSession() {
            $.ajax({
                type: "POST",
                url: "/index.php?cl=amazoncheckoutajax&fnc=confirmAGB",
                data: {confirm: (this.isAgbConfirmed() ? 1 : 0)}
            });
        },
        saveDpaConfirmInSession() {
            $.ajax({
                type: "POST",
                url: "/index.php?cl=amazoncheckoutajax&fnc=confirmDPA",
                data: {confirm: (this.isDpaConfirmed() ? 1 : 0)}
            });
        },
        saveSpaConfirmInSession() {
            $.ajax({
                type: "POST",
                url: "/index.php?cl=amazoncheckoutajax&fnc=confirmSPA",
                data: {confirm: (this.isSpaConfirmed() ? 1 : 0)}
            });
        }
    };
})()