<?php
/**
 * This file is part of OXID eSales AmazonPay module.
 *
 * OXID eSales AmazonPay module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales AmazonPay module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales AmazonPay module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2020
 */
$sLangName = 'English';

$aLang = [
    'charset'                           => 'UTF-8',
    'amazonpay'                         => 'Amazon Pay',
    'OXPS_AMAZONPAY_CONFIG'             => 'Configuration',
    'OXPS_AMAZONPAY_GENERAL'            => 'General',
    'OXPS_AMAZONPAY_CREDENTIALS'        => 'Credentials',
    'OXPS_AMAZONPAY_OPMODE'             => 'Operation Mode',
    'OXPS_AMAZONPAY_OPMODE_PROD'        => 'Production',
    'OXPS_AMAZONPAY_OPMODE_SANDBOX'     => 'Sandbox',
    'HELP_OXPS_AMAZONPAY_OPMODE'        => 'To configure and test Amazon Pay, use Sandbox (test). When you\'re ready
        to receive real transactions, switch to Production (live).',
    'OXPS_AMAZONPAY_PRIVKEY'            => 'Private Key',
    'HELP_OXPS_AMAZONPAY_PRIVKEY'       => 'Your private key for the integration. To generate it, sign in to Seller Central,
        and then go to Integration',
    'OXPS_AMAZONPAY_PUBKEYID'           => 'Public Key ID',
    'HELP_OXPS_AMAZONPAY_PUBKEYID'      => 'The Public key ID you will recive together with the privatekey generated in the Seller Central.',
    'OXPS_AMAZONPAY_MERCHANTID'         => 'Merchant ID',
    'HELP_OXPS_AMAZONPAY_MERCHANTID'    => 'Amazon Pay merchant account identifier. If you are not sure what your merchant ID is,
        sign in to Seller Central, and then go to Integration > MWS Access Key under General Information, Merchant ID.',
    'OXPS_AMAZONPAY_STOREID'            => 'Store ID',
    'HELP_OXPS_AMAZONPAY_STOREID'       => 'Login with Amazon client ID. Do not use the application ID. Retrieve this value
        from Login with Amazon in Seller Central.',
    'OXPS_AMAZONPAY_PAYREGION'          => 'Payment Region',
    'HELP_OXPS_AMAZONPAY_PAYREGION'     => 'The currencies allowed in your shop and possible on Amazon Pay.',
    'OXPS_AMAZONPAY_SELLER'             => 'Seller',
    'OXPS_AMAZONPAY_IPN'                => 'IPN Endpoint',
    'HELP_OXPS_AMAZONPAY_IPN'           => 'IPN messages are sent by Amazon Pay without any action required on your part and
         can be used to update your internal order management system and process orders',
    'OXPS_AMAZONPAY_PLACEMENT'          => 'Placement',
    'HELP_OXPS_AMAZONPAY_PLACEMENT'     => 'Define where to show the Amazon Pay button in your online store.',
    'OXPS_AMAZONPAY_PDP'                => 'Product Detail Page',
    'OXPS_AMAZONPAY_MINICART_AND_MODAL' => 'Basket + Basket-Modal',
    'OXPS_AMAZONPAY_PERFORMANCE'        => 'Performance',
    'OXPS_AMAZONPAY_EXCLUSION'          => 'use "Exclude AmazonPay"',
    'HELP_OXPS_AMAZONPAY_EXCLUSION'     => 'Products and categories can be excluded from AmazonPay. If you do not do this, you can generally deactivate the feature for performance reasons',
    'OXPS_AMAZONPAY_SAVE'               => 'Save',
    'OXPS_AMAZONPAY_ERR_CONF_INVALID'   =>
        'One or more configuration values are either not set or incorrect. Please double check them.<br>
        <b>Module inactive.</b>',
    'OXPS_AMAZONPAY_CONF_VALID'         => 'Configuration values OK.<br><b>Module is active</b>',
    'OXPS_AMAZONPAY_CAPTYPE'            => 'Capture Type',
    'HELP_OXPS_AMAZONPAY_CAPTYPE'       => 'One Step captures payment immediately. Two Step captures payment after shipping.',
    'OXPS_AMAZONPAY_CAPTYPE_ONE_STEP'   => 'One Step',
    'OXPS_AMAZONPAY_CAPTYPE_TWO_STEP'   => 'Two Step',
    'OXPS_AMAZONPAY_EXCLUDED'           => 'Exclude AmazonPay',
    'OXPS_AMAZONPAY_CARRIER_CODE'       => 'Amazon Carrier Code',
    'OXPS_AMAZONPAY_PLEASE_CHOOSE'      => 'Please choose',

    'OXPS_AMAZONPAY_PAYMENT_WAS_SHIPPING'    => 'Amazon payment is made after shipping',
    'OXPS_AMAZONPAY_PAYMENT_WHEN_SHIPPING'   => 'Amazon payment is made on shipping',
    'OXPS_AMAZONPAY_PAYMENT_DURING_CHECKOUT' => 'Amazon payment is made during checkout',
    'OXPS_AMAZONPAY_TRANSACTION_HISTORY'     => 'Transaction-History',
    'OXPS_AMAZONPAY_IPN_HISTORY'             => 'IPN-History',
    'OXPS_AMAZONPAY_DATE'                    => 'Date',
    'OXPS_AMAZONPAY_REFERENCE'               => 'Reference',
    'OXPS_AMAZONPAY_RESULT'                  => 'Result',

    'OXPS_AMAZONPAY_PAYTYPE'            => 'AmazonPay Checkout Type',
    'HELP_OXPS_AMAZONPAY_PAYTYPE'       => 'Selection of delivery address via Amazon Pay (PayAndShip) or only payment via Amazon Pay (PayOnly)'
];
