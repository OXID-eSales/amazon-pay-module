<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

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
        if (!OxidServiceProvider::getAmazonService()->isAmazonSessionActive()) {
            return parent::showShipAddress();
        }

        return true;
    }

    /**
     * Template getter for amazon bill address
     *
     * @return object
     */
    public function getDeliveryAddressAsObj()
    {
        return OxidServiceProvider::getAmazonService()->getDeliveryAddressAsObj();
    }

    /**
     * Template getter for amazon bill address
     *
     * @return object
     */
    public function getBillingAddressAsObj()
    {
        return OxidServiceProvider::getAmazonService()->getBillingAddressAsObj();
    }
}
