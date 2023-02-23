<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\Address;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
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
        if (!OxidServiceProvider::getAmazonService()->isAmazonSessionActive()) {
            return parent::showShipAddress();
        }

        return true;
    }

    /**
     * Template getter for amazon bill address
     *
     * @return Address
     */
    public function getDeliveryAddressAsObj(): Address
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
