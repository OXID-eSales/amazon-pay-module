<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Model;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\UserAddressList;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\AmazonService;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

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
            $service = OxidServiceProvider::getAmazonService();

            if (
                $service->isAmazonSessionActive()
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
