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

    /**
     * @var array default Payment-Descriptions
     */
    public const PAYMENT_DESCRIPTIONS = [
        'en' => [
            'title' => 'AmazonPay',
            'desc'  => '<div>AmazonPay</div>'
        ],
        'de' => [
            'title' => 'AmazonPay',
            'desc'  => '<div>AmazonPay</div>'
        ]
    ];
}
