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
    private $amazonAddress = null;

    /**
     * Return the amazon address if set.
     *
     * @return Address|null
     */
    private function getAmazonAddress()
    {
        if ($this->amazonAddress === null) {
            $service = oxNew(AmazonService::class);

            if ($service->isAmazonSessionActive()
                && Registry::getConfig()->getTopActiveView()->getIsOrderStep()
                && $service->getDeliveryAddress()
            ) {
                $address = oxNew(Address::class);
                $address->assign($service->getDeliveryAddress());
                $address->setId('amazonPaymentDeliveryAddress');
                $this->amazonAddress = $address;
            }
        }

        return $this->amazonAddress;
    }

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
        $address = $this->getAmazonAddress();

        if (!$address) {
            return parent::getUserAddresses($sUserId);
        }

        $list = oxNew(UserAddressList::class);
        $list->add($address);

        return $list;
    }

    /**
     * Return the amazon address id if set.
     *
     * @return mixed
     */
    public function getSelectedAddressId()
    {
        $address = $this->getAmazonAddress();

        if ($address) {
            return $address->getId();
        }

        return parent::getSelectedAddressId();
    }
}
