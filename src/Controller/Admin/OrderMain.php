<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Core\Repository\LogRepository;

/**
 * Class OrderMain
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\OrderMain
 */
class OrderMain extends OrderMain_parent
{
    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function onOrderSend()
    {
        parent::onOrderSend();

        $order = oxNew(Order::class);

        if ($order->load($this->getEditObjectId())) {
            $chargeId = $this->getOrderChargeId($order);

            if (!OxidServiceProvider::getAmazonClient()->getModuleConfig()->isOneStepCapture()) {
                if ($chargeId === '-1') {
                    return;
                }

                $amazonConfig = oxNew(Config::class);
                $currencyCode = $order->oxorder__oxcurrency->rawValue ?? $amazonConfig->getPresentmentCurrency();

                if ($order->getRawFieldData('oxtransstatus') !== 'OK') {
                    OxidServiceProvider::getAmazonService()
                        ->capturePaymentForOrder(
                            $chargeId,
                            $order->getFormattedTotalBrutSum(),
                            $currencyCode
                        );
                }

                /** @var string $oxtrackcode */
                $oxtrackcode = $order->getRawFieldData('oxtrackcode');
                /** @var string $oxdeltype */
                $oxdeltype = $order->getRawFieldData('oxdeltype');
                OxidServiceProvider::getAmazonService()->sendAlexaNotification(
                    $this->getOrderChargePermissionId($order),
                    $oxtrackcode,
                    $oxdeltype
                );
            }
        }
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    protected function getOrderChargePermissionId(Order $oOrder): string
    {
        $chargePermissionId = null;

        if ($oOrder->load($this->getEditObjectId())) {
            $repository = oxNew(LogRepository::class);
            $logMessages = $repository->findLogMessageForOrderId($this->getEditObjectId());
            if (!empty($logMessages)) {
                foreach ($logMessages as $logMessage) {
                    $logsWithChargePermission = $repository->findLogMessageForChargePermissionId(
                        $logMessage['OSC_AMAZON_CHARGE_PERMISSION_ID']
                    );
                    foreach ($logsWithChargePermission as $logWithChargePermission) {
                        if ($logWithChargePermission['OSC_AMAZON_CHARGE_PERMISSION_ID'] !== null) {
                            $chargePermissionId = $logWithChargePermission['OSC_AMAZON_CHARGE_PERMISSION_ID'];
                            break;
                        }
                    }
                }
            }
        }

        return $chargePermissionId;
    }

    /**
     * @param Order $oOrder
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function getOrderChargeId(Order $oOrder): string
    {
        $chargeId = '';

        if ($oOrder->load($this->getEditObjectId())) {
            $repository = oxNew(LogRepository::class);
            $logMessages = $repository->findLogMessageForOrderId($this->getEditObjectId());
            if (!empty($logMessages)) {
                foreach ($logMessages as $logMessage) {
                    $logsWithChargePermission = $repository->findLogMessageForChargePermissionId(
                        $logMessage['OSC_AMAZON_CHARGE_PERMISSION_ID']
                    );
                    foreach ($logsWithChargePermission as $logWithChargePermission) {
                        if ($logWithChargePermission['OSC_AMAZON_RESPONSE_MSG'] === 'Captured') {
                            return '-1';
                        }
                        $chargeIdSet = isset($logWithChargePermission['OSC_AMAZON_CHARGE_ID'])
                            && $logWithChargePermission['OSC_AMAZON_CHARGE_ID'] !== 'null';
                        if ($chargeIdSet) {
                            $chargeId = $logWithChargePermission['OSC_AMAZON_CHARGE_ID'];
                            break;
                        }
                    }
                }
            }
        }

        return $chargeId;
    }
}
