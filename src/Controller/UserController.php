<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

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
    public function showShipAddress()
    {
        $isAmazonSessionActive = OxidServiceProvider::getAmazonService()->isAmazonSessionActive();
        if (!$isAmazonSessionActive) {
            /**
             * parent::showShipAddress() should return bool, but can also return null
             * TODO: check if it is fixed in Oxid 7
             * @var bool|null $showShipAddress
             */
            $showShipAddress = parent::showShipAddress();
            return $showShipAddress ?? false;
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
