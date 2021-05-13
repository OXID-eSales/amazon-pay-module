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

namespace OxidProfessionalServices\AmazonPay\Controller;

use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

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
    public function getFilteredDeliveryAddress()
    {
        return OxidServiceProvider::getAmazonService()->getFilteredDeliveryAddress();
    }

    /**
     * Template getter for amazon bill address
     *
     * @return object
     */
    public function getFilteredBillingAddress()
    {
        return OxidServiceProvider::getAmazonService()->getFilteredBillingAddress();
    }
}
