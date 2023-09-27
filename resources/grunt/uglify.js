module.exports = {

    options: {
        preserveComments: false
    },
    moduleproduction: {
        files: {
            "../assets/js/amazonpay.min.js": [
                "build/js/amazonpay-checkout-button-component.js",
                "build/js/amazonpay-checkout-agb-component.js"
            ],
            "../assets/js/amazonpay-variant-observer.min.js": [
                "build/js/amazonpay-variant-observer.js"
            ],
            "../assets/js/amazonpay-variant-button.min.js": [
                "build/js/amazonpay-variant-button.js"
            ]


        }
    }
};