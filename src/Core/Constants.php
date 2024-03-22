<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

/**
 * Amazon Pay module constants
 */
class Constants
{
    /**
     * @var string Payment ID
     */
    const PAYMENT_ID = 'oxidamazon';

    /**
     * @var string Payment ID
     */
    const PAYMENT_ID_EXPRESS = 'oxidamazonexpress';

    /**
     * @var string Module ID
     */
    const MODULE_ID = 'osc_amazonpay';

    /**
     * @var string Amazon checkout session id param name
     */
    const SESSION_CHECKOUT_ID = 'amzn-checkout-sess';

    /**
     * @var string temp amazon delivery address id used to display delivery address in checkout
     */
    const SESSION_TEMP_DELIVERY_ADDRESS_ID = 'amazondeladrid';

    /**
     * @var string Amazon checkout session param name deliveryaddr
     */
    const SESSION_DELIVERY_ADDR = 'amazondeladr';


    /**
     * @var string Amazon checkout id request param name
     */
    const CHECKOUT_REQUEST_PARAMETER_ID = 'amazonCheckoutSessionId';

    /**
     * @var string Amazon checkout buyer token param name
     */
    const CHECKOUT_REQUEST_BUYER_TOKEN = 'buyerToken';

    /**
     * @var string Amazon Plattform ID
     */
    const PLATTFORM_ID = 'A1O8CIV1A24A6X';

    /**
     * @var string Session Active
     */
    const CHECKOUT_OPEN = 'Open';

    /**
     * @var array Error responses on CHARGE
     */
    const CHARGE_ERROR_CODES = [
        'TransactionAmountExceeded',
        'InvalidChargePermissionStatus',
        'SoftDeclined',
        'HardDeclined',
        'TransactionCountExceeded',
        'PaymentMethodNotAllowed',
        'AmazonRejected',
        'MFANotCompleted',
        'TransactionTimedOut',
        'ProcessingFailure',
    ];

    public static function isAmazonPayment(string $paymentId): bool
    {
        return !empty(self::PAYMENT_DESCRIPTIONS[$paymentId]);
    }

    public static function isAmazonExpressPayment(string $paymentId): bool
    {
        return ($paymentId === self::PAYMENT_ID_EXPRESS && self::isAmazonPayment($paymentId));
    }

    public static function getPaymentIds(): array
    {
        return array_keys(self::PAYMENT_DESCRIPTIONS);
    }

    /**
     * @var array default Payment-Descriptions
     */
    const PAYMENT_DESCRIPTIONS = [
        self::PAYMENT_ID => [
            'en' => [
                'title' => 'AmazonPay',
                'desc' => ''
            ],
            'de' => [
                'title' => 'AmazonPay',
                'desc' => ''
            ]
        ],
        self::PAYMENT_ID_EXPRESS => [
            'en' => [
                'title' => 'AmazonPay Express',
                'desc' => ''
            ],
            'de' => [
                'title' => 'AmazonPay Express',
                'desc' => ''
            ]
        ],
    ];
}
