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

namespace OxidProfessionalServices\AmazonPay\Model;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\UserAddressList;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\AmazonService;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\User
 */
class User extends User_parent
{
    /**
     * Use amazon address in checkout steps
     *
     * @param null $sUserId
     *
     * @return object|UserAddressList
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getUserAddresses($sUserId = null)
    {
        $service = oxNew(AmazonService::class);
        if (!$service->isAmazonSessionActive()) {
            return parent::getUserAddresses($sUserId);
        }

        if (!Registry::getConfig()->getTopActiveView()->getIsOrderStep()) {
            return parent::getUserAddresses($sUserId); // only do this in checkout
        }

        $amznAddress = $service->getDeliveryAddress();

        if (!$amznAddress) {
            return parent::getUserAddresses($sUserId);
        }

        $address = oxNew(Address::class);
        $address->assign($amznAddress);
        $list = oxNew(UserAddressList::class);
        $list->add($address);

        return $list;
    }
}
