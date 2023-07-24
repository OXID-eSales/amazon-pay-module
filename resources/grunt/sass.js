const sass = require('node-sass');

module.exports = {
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

