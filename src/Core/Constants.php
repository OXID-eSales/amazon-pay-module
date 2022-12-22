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
    public const PAYMENT_ID = 'oxidamazon';

    /**
     * @var string Payment ID
     */
    public const PAYMENT_ID_EXPRESS = 'oxidamazonexpress';

    /**
     * @var string Module ID
     */
    public const MODULE_ID = 'osc_amazonpay';

    /**
     * @var string Amazon checkout session id param name
     */
    public const SESSION_CHECKOUT_ID = 'amzn-checkout-sess';

    /**
     * @var string Amazon checkout session param name deliveryaddr
     */
    public const SESSION_DELIVERY_ADDR = 'amazondeladr';


    /**
     * @var string Amazon checkout id request param name
     */
    public const CHECKOUT_REQUEST_PARAMETER_ID = 'amazonCheckoutSessionId';

    /**
     * @var string Amazon checkout buyer token param name
     */
    public const CHECKOUT_REQUEST_BUYER_TOKEN = 'buyerToken';

    /**
     * @var string Amazon Plattform ID
     */
    public const PLATTFORM_ID = 'A1O8CIV1A24A6X';

    /**
     * @var string Session Active
     */
    public const CHECKOUT_OPEN = 'Open';

    /**
     * @var array Error responses on CHARGE
     */
    public const CHARGE_ERROR_CODES = [
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

    public static function isAmazonPayment(string $id): bool
    {
        return !empty(self::PAYMENT_DESCRIPTIONS[$id]);
    }

    public static function getPaymentIds(): array
    {
        return array_keys(self::PAYMENT_DESCRIPTIONS);
    }

    /**
     * @var array default Payment-Descriptions
     */
    public const PAYMENT_DESCRIPTIONS = [
        self::PAYMENT_ID => [
            'en' => [
                'title' => 'AmazonPay',
                'desc' => '<div>AmazonPay</div>'
            ],
            'de' => [
                'title' => 'AmazonPay',
                'desc' => '<div>AmazonPay</div>'
            ]
        ],
        self::PAYMENT_ID_EXPRESS => [
            'en' => [
                'title' => 'AmazonPay Express',
                'desc' => '<div>AmazonPay Express</div>'
            ],
            'de' => [
                'title' => 'AmazonPay Express',
                'desc' => '<div>AmazonPay Express</div>'
            ]
        ],
    ];
}
