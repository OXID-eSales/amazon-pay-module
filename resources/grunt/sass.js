const sass = require('node-sass');

module.exports = {
    moduledevelopment: {
        options: {
            implementation: sass,
            update: true,
            style: 'nested'
        },
        files: {
            "../out/src/css/amazonpay.css": "build/scss/amazonpay.scss",
            "../out/src/css/amazonpay_backend.css": "build/scss/amazonpay_backend.scss",
        }
    },
    moduleproduction: {
        options: {
            implementation: sass,
            update: true,
            style: 'compressed'
        },
        files: {
            "../out/src/css/amazonpay.css": "build/scss/amazonpay.scss",
            "../out/src/css/amazonpay_backend.css": "build/scss/amazonpay_backend.scss",
        }
    }
};

