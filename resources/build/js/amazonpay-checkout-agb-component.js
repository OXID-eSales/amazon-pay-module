;(function() {
    window.OSCAmazonPayCheckoutAGBComponent = {
        init: function () {
            this.registerEvents();
        },
        registerEvents: function () {
           if (OSCAmazonPayButtonComponent.forceConfirmAGB()) {
                document.getElementById('checkAgbTop').onclick = function () {
                    document.getElementById('confirm-agb-error-container').classList.remove('alert-danger');
                    if (document.getElementsByClassName('agbConfirmation')[0]) {
                        document.getElementsByClassName('agbConfirmation')[0].classList.remove('alert-danger');
                    }
                    OSCAmazonPayButtonComponent.hideErrorContainer();
                    OSCAmazonPayCheckoutAGBComponent.saveAGBConfirmInSession();
                };
            }
            if (OSCAmazonPayButtonComponent.forceConfirmDPA()) {
                document.getElementById('oxdownloadableproductsagreement').addEventListener('click', function () {
                    document.getElementById('confirm-agb-error-container').classList.remove('alert-danger');
                    OSCAmazonPayButtonComponent.hideErrorContainer();
                    OSCAmazonPayCheckoutAGBComponent.saveDpaConfirmInSession();
                });
            }
            if (OSCAmazonPayButtonComponent.forceConfirmSPA()) {
                document.getElementById('oxserviceproductsagreement').addEventListener('click', function () {
                    document.getElementById('confirm-agb-error-container').classList.remove('alert-danger');
                    OSCAmazonPayButtonComponent.hideErrorContainer();
                    OSCAmazonPayCheckoutAGBComponent.saveSpaConfirmInSession();
                });
            }
        },
        isAgbConfirmed: function() {
            return document.getElementById('checkAgbTop').checked
        },
        isDpaConfirmed: function () {
            return document.getElementById('oxdownloadableproductsagreement').checked;
        },
        isSpaConfirmed: function () {
            return document.getElementById('oxserviceproductsagreement').checked;
        },
        saveAGBConfirmInSession() {
            var http = new XMLHttpRequest();
            var url = '/index.php?cl=amazoncheckoutajax&fnc=confirmAGB';
            var params = "confirm=" + (this.isAgbConfirmed() ? 1 : 0);
            http.open('POST', url, true);
            http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            http.onreadystatechange = function() {//Call a function when the state changes.
                if(http.readyState == 4 && http.status == 200) {
                    // uncomment for testing
                    // alert(http.responseText);
                }
            }
            http.send(params);
        },
        saveDpaConfirmInSession() {
            var http = new XMLHttpRequest();
            var url = '/index.php?cl=amazoncheckoutajax&fnc=confirmDPA';
            var params = "confirm=" + (this.isDpaConfirmed() ? 1 : 0);
            http.open('POST', url, true);
            http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            http.onreadystatechange = function() {//Call a function when the state changes.
                if(http.readyState == 4 && http.status == 200) {
                    // uncomment for testing
                    // alert(http.responseText);
                }
            }
            http.send(params);
            document.querySelector.ajax({
                type: "POST",
                url: "/index.php?cl=amazoncheckoutajax&fnc=confirmDPA",
                data: {confirm: (this.isDpaConfirmed() ? 1 : 0)}
            });
        },

        saveSpaConfirmInSession() {
            var http = new XMLHttpRequest();
            var url = '/index.php?cl=amazoncheckoutajax&fnc=confirmSPA';
            var params = "confirm=" + (this.isSpaConfirmed() ? 1 : 0);
            http.open('POST', url, true);
            http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            http.onreadystatechange = function() {//Call a function when the state changes.
                if(http.readyState == 4 && http.status == 200) {
                    // uncomment for testing
                    // alert(http.responseText);
                }
            }
            http.send(params);
        }
    };
})()