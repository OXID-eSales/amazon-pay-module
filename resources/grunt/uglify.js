module.exports = {

    options: {
        preserveComments: false
    },
    moduleproduction: {
        files: {
            "../out/src/js/amazonpay.min.js": [
                "build/js/amazonpay-checkout-button-component.js",
                "build/js/amazonpay-checkout-agb-component.js"
            ]
        }
    }
};