module.exports = {

    options: {
        preserveComments: false
    },
    moduleproduction: {
        files: {
            "../assets/js/amazonpay.min.js": [
                // "build/vendor/jquery/js/jquery-1.12.0.js",
                "build/js/amazonpay-checkout-button-component.js",
                "build/js/amazonpay-checkout-agb-component.js"
            ]
        }
    }
};