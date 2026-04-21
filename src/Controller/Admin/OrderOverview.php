<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Core\Repository\LogRepository;

class OrderOverview extends OrderOverview_parent
{
    /** @var null|string $captureStatus */
    protected $captureStatus = null;
    /** @var null|string $captureStatus */
    protected $paymentStatus = null;
    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function render()
    {
        /** @var \OxidSolutionCatalysts\AmazonPay\Model\Order $oOrder */
        $oOrder = oxNew(Order::class);
        $filteredLogs = [];
        $ipnLogs = [];
        $isCaptured = false;
        $isOneStepCapture = false;

        $existingItems = [];

        $orderLoaded = $oOrder->load($this->getEditObjectId());
        /** @var string $paymentType */
        $paymentType = $oOrder->getFieldData('oxpaymenttype');
        if (
            $orderLoaded &&
            Constants::isAmazonPayment($paymentType)
        ) {
            $orderLogs = OxidServiceProvider::getAmazonService()->getOrderLogs($oOrder);

            foreach ($orderLogs as $orderLog) {
                $newFilteredLog = [];

                if ($orderLog['OSC_AMAZON_REQUEST_TYPE'] === 'IPN') {
                    $ipnLog = [];
                    $ipnLog['time'] = $orderLog['OXTIMESTAMP'];
                    $ipnLog['identifier'] = $orderLog['OSC_AMAZON_IDENTIFIER'];
                    $ipnLog['requestType'] = $orderLog['OSC_AMAZON_OBJECT_TYPE'];
                    $ipnLogs[] = $ipnLog;
                    continue;
                }

                if (in_array($orderLog['OSC_AMAZON_PAYLOGID'], $existingItems, true)) {
                    continue;
                }

                $existingItems[] = $orderLog['OSC_AMAZON_PAYLOGID'];

                $newFilteredLog['time'] = $orderLog['OXTIMESTAMP'];
                $newFilteredLog['identifier'] = $orderLog['OSC_AMAZON_IDENTIFIER'];
                $newFilteredLog['statusCode'] = $orderLog['OSC_AMAZON_STATUS_CODE'] === '200' ? 'success' : 'error';

                if (strpos($orderLog['OSC_AMAZON_REQUEST_TYPE'], 'Error') !== false) {
                    $newFilteredLog['statusCode'] = 'error';
                    $newFilteredLog['identifier'] = 'ORDER ID:' . $orderLog['OSC_AMAZON_OXORDERID'];
                }

                $newFilteredLog['requestType'] = $orderLog['OSC_AMAZON_RESPONSE_MSG'];
                if ($orderLog['OSC_AMAZON_RESPONSE_MSG'] === 'Captured') {
                    $newFilteredLog['requestType'] = 'Payment captured';
                    $isCaptured = true;
                    $isOneStepCapture = false;
                } elseif ($orderLog['OSC_AMAZON_RESPONSE_MSG'] === 'Completed') {
                    $newFilteredLog['requestType'] = 'Checkout complete';
                    $isOneStepCapture = false;
                } elseif ($orderLog['OSC_AMAZON_RESPONSE_MSG'] === 'Completed & Captured') {
                    $newFilteredLog['requestType'] = 'Checkout & Capture complete';
                    $isOneStepCapture = true;
                    $isCaptured = true;
                } elseif ($orderLog['OSC_AMAZON_RESPONSE_MSG'] === 'Canceled') {
                    $newFilteredLog['requestType'] = 'Canceled';
                    $isCaptured = true;
                } elseif ($orderLog['OSC_AMAZON_RESPONSE_MSG'] === 'Refunded') {
                    $newFilteredLog['requestType'] = 'Refund Complete';
                    $isCaptured = true;
                }

                $filteredLogs[] = $newFilteredLog;
            }

            if (empty($orderLogs)) {
                $filteredLog = [];
                $filteredLog['time'] = 'no data';
                $filteredLog['identifier'] = 'no data';
                $filteredLog['requestType'] = 'no data';
                $filteredLogs[] = $filteredLog;
            }
            $this->addTplParam('orderLogs', $filteredLogs);

            if (empty($ipnLogs)) {
                $ipnLog = [];
                $ipnLog['time'] = 'no data';
                $ipnLog['identifier'] = 'no data';
                $ipnLog['requestType'] = 'no data';
                $ipnLogs[] = $ipnLog;
            }
            $this->addTplParam('ipnLogs', $ipnLogs);
        }

        $this->addtplParam('isOneStepCapture', $isOneStepCapture);
        $this->addTplParam('isCaptured', $isCaptured);
        $this->addTplParam('withLiveStatus', false);

        return parent::render();
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function isAuthorized()
    {
        return $this->getPaymentStatus() === 'Authorized';
    }
    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function isCaptured()
    {
        return $this->getPaymentStatus() === 'Captured';
    }
    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getAmazonAPIOrderStatus()
    {
        if (is_null($this->captureStatus)) {
            $this->captureStatus = '';
            $logMessage = $this->getLogMessageForOrder();
            if ($logMessage) {
                $lang = Registry::getLang();
                $chargePermissionId = isset($logMessage[0]['OSC_AMAZON_CHARGE_PERMISSION_ID']) ?
                    $logMessage[0]['OSC_AMAZON_CHARGE_PERMISSION_ID'] : null;
                $this->captureStatus = $lang->translateString('OSC_AMAZONPAY_NOLIVESTATUS');
                if ($chargePermissionId) {
                    $amzData = OxidServiceProvider::getAmazonClient()->getChargePermission($chargePermissionId);
                    $captureStatusRaw = isset($amzData['response']['statusDetails']['state']) 
                        ? $amzData['response']['statusDetails']['state'] : '';
                    $reasonCodes = [];
                    $captureReasonRaw = isset($amzData['response']['statusDetails']['reasons']) ?
                        $amzData['response']['statusDetails']['reasons'] : [];
                    foreach ($captureReasonRaw as $captureReason) {
                        if (isset($captureReason['reasonCode'])) {
                            $reasonCodes[] = $captureReason['reasonCode'];
                        }
                    }
                    $captureStatus = $lang->translateString(
                        'OSC_AMAZONPAY_LIVESTATUS_' . strtoupper($captureStatusRaw)
                    );
                    $this->captureStatus = $lang->isTranslated() ? $captureStatus : $captureStatusRaw;
                    if ($reasonCodes) {
                        $this->captureStatus .= ' (' . implode(',', $reasonCodes) . ')';
                    }
                }
            }
        }
        return $this->captureStatus;
    }
    public function getAmazonMaximalRefundAmount()
    {
        return PhpHelper::getMoneyValue(
            OxidServiceProvider::getAmazonService()->getMaximalRefundAmount($this->getEditObjectId())
        );
    }
    public function getAmazonMaximalCaptureAmount()
    {
        $order = new Order();
        $order->load($this->getEditObjectId());
        return $order->getTotalOrderSum();
    }
    public function refundpayment()
    {
        $oOrder = oxNew(Order::class);
        $refundAmount = Registry::getRequest()->getRequestParameter("refundAmount");
        $refundAmount = str_replace(',', '.', $refundAmount);
        $orderLoaded = $oOrder->load($this->getEditObjectId());
        /** @var string $paymentType */
        $paymentType = $oOrder->getFieldData('oxpaymenttype');
        if (
            $orderLoaded &&
            Constants::isAmazonPayment($paymentType) &&
            $oOrder->getId() !== null
        ) {
            $logger = new Logger();
            $errorMessage = OxidServiceProvider::getAmazonService()->createRefund(
                $oOrder->getId(),
                (float)$refundAmount,
                $logger
            );
            if (is_string($errorMessage)) {
                $this->addTplParam('amazonServiceErrorMessage', $errorMessage);
            }
        }
    }
    /**
     * @return string|void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function makeCharge()
    {
        $oOrder = oxNew(Order::class);
        /** @var string $captureAmount */
        $captureAmount = Registry::getRequest()->getRequestParameter("captureAmount");
        $amazonConfig = oxNew(Config::class);
        $orderLoaded = $oOrder->load($this->getEditObjectId());
        $currencyCode = isset($oOrder->oxorder__oxcurrency->rawValue) ?
            $oOrder->oxorder__oxcurrency->rawValue : $amazonConfig->getPresentmentCurrency();
        /** @var string $paymentType */
        $paymentType = $oOrder->getFieldData('oxpaymenttype');
        if (
            $orderLoaded &&
            Constants::isAmazonPayment($paymentType) &&
            $oOrder->getId() !== null
        ) {
            $chargeId = '';
            $repository = oxNew(LogRepository::class);
            $logMessages = $repository->findLogMessageForOrderId($this->getEditObjectId());
            if (!empty($logMessages)) {
                foreach ($logMessages as $logMessage) {
                    $logs = $repository->findLogMessageForChargePermissionId(
                        $logMessage['OSC_AMAZON_CHARGE_PERMISSION_ID']
                    );
                    foreach ($logs as $charchelog) {
                        if ($charchelog['OSC_AMAZON_RESPONSE_MSG'] === 'Captured') {
                            return '-1';
                        }
                        $chargeIdSet = isset($charchelog['OSC_AMAZON_CHARGE_ID'])
                            && $charchelog['OSC_AMAZON_CHARGE_ID'] !== 'null';
                        if ($chargeIdSet) {
                            $chargeId = $charchelog['OSC_AMAZON_CHARGE_ID'];
                            break;
                        }
                    }
                }
            }
            OxidServiceProvider::getAmazonService()->capturePaymentForOrder($chargeId, $captureAmount, $currencyCode);
        }
    }
    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    protected function getPaymentStatus()
    {
        if (is_null($this->paymentStatus)) {
            $this->paymentStatus = '';
            $logMessage = $this->getLogMessageForOrder();
            if ($logMessage) {
                $chargeId = isset($logMessage[0]['OSC_AMAZON_CHARGE_ID']) ?
                    $logMessage[0]['OSC_AMAZON_CHARGE_ID'] : null;
                $amzData = OxidServiceProvider::getAmazonClient()->getCharge($chargeId);
                $this->paymentStatus = isset($amzData['response']['statusDetails']['state']) ?
                    $amzData['response']['statusDetails']['state'] : '';
            }
        }
        return $this->paymentStatus;
    }
    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    protected function getLogMessageForOrder()
    {
        $result = [];
        $orderId = $this->getEditObjectId();
        if ($orderId !== '-1') {
            $repository = oxNew(LogRepository::class);
            $order = oxNew(Order::class);
            $order->load($orderId);
            $result = $repository->findLogMessageForOrderId($orderId);
        }
        return $result;
    }
}
