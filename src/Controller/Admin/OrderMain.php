<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidProfessionalServices\AmazonPay\Core\Config;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidProfessionalServices\AmazonPay\Core\Repository\LogRepository;

/**
 * Class OrderMain
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\OrderMain
 */
class OrderMain extends OrderMain_parent
{
    /**
     * @return void
     */
    protected function onOrderSend()
    {
        parent::onOrderSend();

        $order = oxNew(Order::class);

        if ($order->load($this->getEditObjectId())) {
            $chargeId = $this->getOrderChargeId($order);

            if (!OxidServiceProvider::getAmazonClient()->getModuleConfig()->isOneStepCapture()) {
                if ($chargeId === null || $chargeId === -1) {
                    return;
                }

                $amazonConfig = oxNew(Config::class);
                $currencyCode = $order->oxorder__oxcurrency->rawValue ?? $amazonConfig->getPresentmentCurrency();

                if ($order->oxorder__oxtransstatus->rawValue !== 'OK') {
                    OxidServiceProvider::getAmazonService()
                        ->capturePaymentForOrder(
                            $chargeId,
                            $order->getFormattedTotalBrutSum(),
                            $currencyCode
                        );
                }

                OxidServiceProvider::getAmazonService()->sendAlexaNotification(
                    $this->getOrderChargePermissionId($order),
                    $order->oxorder__oxtrackcode->rawValue,
                    $order->oxorder__oxdeltype->rawValue
                );
            }
        }
    }

    protected function getOrderChargePermissionId(Order $oOrder)
    {
        $chargePermissionId = null;

        if ($oOrder->load($this->getEditObjectId())) {
            $repository = oxNew(LogRepository::class);
            $logMessages = $repository->findLogMessageForOrderId($this->getEditObjectId());
            if (!empty($logMessages)) {
                foreach ($logMessages as $logMessage) {
                    $logsWithChargePermission = $repository->findLogMessageForChargePermissionId(
                        $logMessage['OXPS_AMAZON_CHARGE_PERMISSION_ID']
                    );
                    foreach ($logsWithChargePermission as $logWithChargePermission) {
                        if ($logWithChargePermission['OXPS_AMAZON_CHARGE_PERMISSION_ID'] !== null) {
                            $chargePermissionId = $logWithChargePermission['OXPS_AMAZON_CHARGE_PERMISSION_ID'];
                            break;
                        }
                    }
                }
            }
        }

        return $chargePermissionId;
    }


    protected function getOrderChargeId(Order $oOrder)
    {
        $chargeId = null;

        if ($oOrder->load($this->getEditObjectId())) {
            $repository = oxNew(LogRepository::class);
            $logMessages = $repository->findLogMessageForOrderId($this->getEditObjectId());
            if (!empty($logMessages)) {
                foreach ($logMessages as $logMessage) {
                    $logsWithChargePermission = $repository->findLogMessageForChargePermissionId(
                        $logMessage['OXPS_AMAZON_CHARGE_PERMISSION_ID']
                    );
                    foreach ($logsWithChargePermission as $logWithChargePermission) {
                        if ($logWithChargePermission['OXPS_AMAZON_RESPONSE_MSG'] === 'Captured') {
                            return -1;
                        }
                        $chargeIdSet = isset($logWithChargePermission['OXPS_AMAZON_CHARGE_ID'])
                            && $logWithChargePermission['OXPS_AMAZON_CHARGE_ID'] !== null
                            && $logWithChargePermission['OXPS_AMAZON_CHARGE_ID'] !== 'null';
                        if ($chargeIdSet) {
                            $chargeId = $logWithChargePermission['OXPS_AMAZON_CHARGE_ID'];
                            break;
                        }
                    }
                }
            }
        }

        return $chargeId;
    }
}
