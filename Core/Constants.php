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

namespace OxidProfessionalServices\AmazonPay\Core;

/**
 * Amazon Pay module constants
 */
class Constants
{
    /**
     * @var string Module ID
     */
    public const MODULE_ID = 'oxps_amazonpay';
    /**
     * @var string Amazon checkout session id param name
     */
    public const SESSION_CHECKOUT_ID = 'amzn-checkout-sess';

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
}
