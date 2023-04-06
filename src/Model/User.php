<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Model;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\UserAddressList;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\User
 */
class User extends User_parent
{
    private $amazonAddress = null;

    /**
     * @inherit doc
     *
     * @param array $aDelAddress address data array
     * TODO: check if typehint can be used in Oxid 7
     */
    protected function _assignAddress($aDelAddress)
    {
        $session = Registry::getSession();
        if (
            $session->getVariable('paymentid') !== Constants::PAYMENT_ID ||
            !$session->getVariable(Constants::SESSION_DELIVERY_ADDR)
        ) {
            parent::_assignAddress($aDelAddress);
        }
    }

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
     * @param string|null $sUserId
     *
     * @return UserAddressList|array
     * TODO: check if typehint can be used in Oxid 7
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
     * @return string
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
