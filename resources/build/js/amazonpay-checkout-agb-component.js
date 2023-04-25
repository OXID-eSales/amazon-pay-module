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
        },
        isAgbConfirmed: function() {
            return $('#checkAgbTop').is(':checked');
        },
        saveAGBConfirmInSession() {
            $.ajax({
                type: "POST",
                url: "/index.php?cl=amazoncheckoutajax&fnc=confirmAGB",
                data: {confirm: (this.isAgbConfirmed() ? 1 : 0)}
            });
        }
    };
})()