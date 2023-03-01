<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use Psr\Log\LogLevel;
use stdClass;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\UserController
 */
class UserController extends UserController_parent
{
    /**
     * Force showing of shipping address when Amazon Pay is active
     *
     * @return bool
     */
    public function showShipAddress(): bool
    {
        $isAmazonSessionActive = OxidServiceProvider::getAmazonService()->isAmazonSessionActive();
        if (!$isAmazonSessionActive) {
            return parent::showShipAddress();
        }

        return true;
    }

    /**
     * Template getter for amazon bill address
     *
     * @return stdClass
     */
    public function getDeliveryAddressAsObj(): stdClass
    {
        return OxidServiceProvider::getAmazonService()->getDeliveryAddressAsObj();
    }

    /**
     * Template getter for amazon bill address
     *
     * @return stdClass
     */
    public function getBillingAddressAsObj(): stdClass
    {
        return OxidServiceProvider::getAmazonService()->getBillingAddressAsObj();
    }
}
