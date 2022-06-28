<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Core\Helper;

class AmazonCarrier extends AmazonCarrierMap
{
    public static function getAllCarriers(): array
    {
        return static::$carriers;
    }
}
