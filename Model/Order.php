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
use OxidEsales\Eshop\Application\Model\RequiredAddressFields;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidProfessionalServices\AmazonPay\Core\Config;
use OxidProfessionalServices\AmazonPay\Core\AmazonService;
use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order extends Order_parent
{
    /** @var AmazonService */
    private $amazonService;

    /**
     * Security and Cleanup before finalize order
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket              Basket object
     * @param object                                     $oUser                Current User object
     *
     */
    protected function prepareFinalizeOrder(Basket $oBasket, $oUser)
    {
        // if payment is 'oxidamazon' but we do not have a Amazon Pay Session
        // stop finalize order
        if (
            $oBasket->getPaymentId() === 'oxidamazon' &&
            !OxidServiceProvider::getAmazonService()->isAmazonSessionActive()
        ) {
            return self::ORDER_STATE_PAYMENTERROR; // means no authentication
        }

        // cleanup sanitized addresses for amazon-orders
        if (
            $oBasket->getPaymentId() === 'oxidamazon' &&
            ($missingRequestBillingFields = Registry::getConfig()->getRequestParameter(
                'missing_amazon_invadr'
            ))
        ) {
            $config = Registry::get(Config::class);
            $oRequiredAddressFields = oxNew(RequiredAddressFields::class);

            foreach ($oRequiredAddressFields->getBillingFields() as $billingKey) {
                if (isset($missingRequestBillingFields[$billingKey])) {
                    $oUser->{$billingKey} = new Field($missingRequestBillingFields[$billingKey], Field::T_RAW);
                }
                elseif (strpos($oUser->{$billingKey}->value, $config->getPlaceholder()) !== false) {
                    $oUser->{$billingKey} = new Field('', Field::T_RAW);
                }
            }
            $oUser->save();
            Registry::getSession()->deleteVariable('amazonMissingBillingFields');
        }
    }

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
        $ret = $this->prepareFinalizeOrder($oBasket, $oUser);

        if ($ret !== self::ORDER_STATE_PAYMENTERROR) {
            $ret = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
        }

        // Authorize and Capture via Amazon Pay will be done after finalizeOrder in OXID
        // therefore we reset status to "not finished yet"
        if ($ret < 2  &&
            !$blRecalculatingOrder &&
            $oBasket->getPaymentId() === 'oxidamazon'
        ) {
            $this->updateAmazonPayOrderStatus('AMZ_PAYMENT_PENDING');
        }
        return $ret;
    }

    /**
     * If Amazon Pay is active, it will return an address from Amazon
     *
     * @return Address
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getDelAddressInfo()
    {
        $amazonService = $this->getAmazonService();
        $amazonDelAddress = $amazonService->getDeliveryAddress();
        if (
            !$amazonService->isAmazonSessionActive() ||
            !$amazonDelAddress
        ) {
            return parent::getDelAddressInfo();
        }

        $address = oxNew(Address::class);
        $address->assign($amazonDelAddress);

        // sanitized addresses for amazon-orders
        $config = Registry::get(Config::class);
        $missingRequestDeliveryFields = Registry::getConfig()->getRequestParameter('missing_amazon_deladr');
        $oRequiredAddressFields = oxNew(RequiredAddressFields::class);
        foreach ($oRequiredAddressFields->getDeliveryFields() as $deliveryKey) {
            if (isset($missingRequestDeliveryFields[$deliveryKey])) {
                $address->{$deliveryKey} = new Field($missingRequestDeliveryFields[$deliveryKey], Field::T_RAW);
            }
            elseif (strpos($address->{$deliveryKey}->value, $config->getPlaceholder()) !== false) {
                $address->{$deliveryKey} = new Field('', Field::T_RAW);
            }
        }
        Registry::getSession()->deleteVariable('amazonMissingDeliveryFields');

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

    public function updateAmazonPayOrderStatus($amazonPayStatus, $data = null)
    {
        switch ($amazonPayStatus) {
            case "AMZ_PAYMENT_PENDING":
                $this->oxorder__oxtransstatus = new Field('NOT_FINISHED', Field::T_RAW);
                $this->oxorder__oxtransid = new Field('AMZ_PAYMENT_PENDING', Field::T_RAW);
                $this->oxorder__oxfolder = new Field('ORDERFOLDER_PROBLEMS', Field::T_RAW);
                $this->oxorder__oxps_amazon_remark = new Field('AmazonPay Authorisation pending', Field::T_RAW);
                $this->save();
                break;

            case "AMZ_AUTH_STILL_PENDING":
                if (is_array($data)) {
                    $this->oxorder__oxtransid = new Field($data['chargeId'], Field::T_RAW);
                    $this->oxorder__oxps_amazon_remark = new Field('AmazonPay Authorisation still pending: ' . $data['chargeAmount'], Field::T_RAW);
                }
                $this->save();
                break;

            case "AMZ_AUTH_AND_CAPT_FAILED":
                if (is_array($data)) {
                    $response = PhpHelper::jsonToArray($data['result']['response']);
                    if ($data['chargeId']) {
                        $this->oxorder__oxtransid = new Field($data['chargeId'], Field::T_RAW);
                    }
                    $this->oxorder__oxps_amazon_remark = new Field('AmazonPay ERROR: ' . $response['reasonCode'], Field::T_RAW);
                }
                else {
                    $this->oxorder__oxps_amazon_remark = new Field('AmazonPay: ERROR');
                }
                $this->save();
                break;

            case "AMZ_AUTH_AND_CAPT_OK":
                $this->oxorder__oxpaid =  new Field(\date('Y-m-d H:i:s'), Field::T_RAW);
                $this->oxorder__oxtransstatus = new Field('OK', Field::T_RAW);
                if (is_array($data)) {
                    $this->oxorder__oxtransid = new Field($data['chargeId'], Field::T_RAW);
                    $this->oxorder__oxps_amazon_remark = new Field('AmazonPay Captured: ' . $data['chargeAmount'], Field::T_RAW);
                }
                $this->oxorder__oxfolder = new Field('ORDERFOLDER_NEW', Field::T_RAW);
                $this->save();
                break;

            case "AMZ_2STEP_AUTH_OK":
                if (is_array($data)) {
                    $this->oxorder__oxtransid = new Field($data['chargeId'], Field::T_RAW);
                    $this->oxorder__oxps_amazon_remark = new Field('AmazonPay Authorized (not Captured):' . $data['chargeAmount'], Field::T_RAW);
                }
                $this->oxorder__oxfolder = new Field('ORDERFOLDER_NEW', Field::T_RAW);
                $this->save();
                break;
        }
    }

    /**
     * Just a helper to allow mock injection for testing
     * @return AmazonService
     */
    public function getAmazonService(): AmazonService
    {
        if (empty($this->amazonService)) {
            $this->setAmazonService(oxNew(AmazonService::class));
            return $this->amazonService;
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
