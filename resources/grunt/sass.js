const sass = require('sass');

module.exports = {
    moduledevelopment: {
        options: {
            implementation: sass,
            update: true,
            style: 'nested'
        },
        files: {
            "../assets/css/amazonpay.css": "build/scss/amazonpay.scss",
            "../assets/css/amazonpay_backend.css": "build/scss/amazonpay_backend.scss",
        }
    },
    moduleproduction: {
        options: {
            implementation: sass,
            update: true,
            style: 'compressed'
        },
        files: {
            "../assets/css/amazonpay.css": "build/scss/amazonpay.scss",
            "../assets/css/amazonpay_backend.css": "build/scss/amazonpay_backend.scss",
        }
    }
};

