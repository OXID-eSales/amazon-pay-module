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

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidProfessionalServices\AmazonPay\Core\AmazonService;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order extends Order_parent
{
    /** @var AmazonService */
    private $amazonService;

    /**
     * Order checking, processing and saving method.
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket              Basket object
     * @param object                                     $oUser                Current User object
     * @param bool                                       $blRecalculatingOrder Order recalculation
     *
     * @return integer
     */
    public function finalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        if ($oBasket->getPaymentId() == 'oxidamazon') {
            $missingError = false;

            $session = Registry::getSession();

            $oDelAdress = oxNew(Address::class);
            $oDelAdress->load(Registry::getSession()->getVariable('deladrid'));

            $oConfig = $this->getConfig();

            if ($missingRequestBillingFields = $oConfig->getRequestParameter('missing_amazon_invadr')) {
                $changeMissingBillingFields = false;
                foreach ($this->getAmazonService()->getMissingRequiredBillingFields() as $key => $value) {
                    if (isset($missingRequestBillingFields[$key])) {
                        if ($missingRequestBillingFields[$key]) {
                            $changeMissingBillingFields = true;
                            $oUser->{$key} = new Field($missingRequestBillingFields[$key], Field::T_RAW);
                        } else {
                            $missingError = true;
                        }
                    }
                }
                if ($changeMissingBillingFields) {
                    $oUser->save();
                }
                $session->deleteVariable('amazonMissingBillingFields');
            }

            if ($missingRequestDeliveryFields = $oConfig->getRequestParameter('missing_amazon_deladr')) {
                $changeMissingDeliveryFields = false;
                foreach ($this->getAmazonService()->getMissingRequiredDeliveryFields() as $key => $value) {
                    if (isset($missingRequestDeliveryFields[$key])) {
                        if ($missingRequestDeliveryFields[$key]) {
                            $changeMissingDeliveryFields = true;
                            $oDelAdress->{$key} = new Field($missingRequestDeliveryFields[$key], Field::T_RAW);
                        } else {
                            $missingError = true;
                        }
                    }
                }
                if ($changeMissingDeliveryFields) {
                    $oDelAdress->save();
                }
                $session->deleteVariable('amazonMissingDeliveryFields');
            }

            if ($missingError) {
                return self::ORDER_STATE_INVALIDDELADDRESSCHANGED;
            }
        }
        return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
    }

    /**
     * If Amazon Pay is active, it will return an address from Amazon
     *
     * @return Address
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getDelAddressInfo()
    {
        if (!$this->getAmazonService()->isAmazonSessionActive()) {
            return parent::getDelAddressInfo();
        }

        $address = oxNew(Address::class);
        $address->assign($this->getAmazonService()->getDeliveryAddress());

        return $address;
    }

    /**
     * Disabling validation for Amazon addresses when Amazon Pay is active
     *
     * @param $oUser
     *
     * @return int
     */
    public function validateDeliveryAddress($oUser)
    {
        if (!$this->getAmazonService()->isAmazonSessionActive()) {
            return parent::validateDeliveryAddress($oUser);
        }

        return 0; // disable validation
    }

    public function updateStatus($status)
    {
        $this->__set('OXTRANSTATUS', $status);
    }

    /**
     * Just a helper to allow mock injection for testing
     * @return AmazonService
     */
    public function getAmazonService(): AmazonService
    {
        if (empty($this->amazonService)) {
            return oxNew(AmazonService::class);
        }

        return $this->amazonService;
    }

    /**
     * @param AmazonService $amazonService
     */
    public function setAmazonService(AmazonService $amazonService): void
    {
        $this->amazonService = $amazonService;
    }
}
