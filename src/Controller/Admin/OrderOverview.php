<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

class OrderOverview extends OrderOverview_parent
{
    public function render()
    {
        $oOrder = oxNew(Order::class);
        $filteredLogs = [];
        $ipnLogs = [];
        $isCaptured = false;
        $isOneStepCapture = false;

        $existingItems = [];

        if (
            $oOrder->load($this->getEditObjectId()) &&
            Constants::isAmazonPayment($oOrder->oxorder__oxpaymenttype->value)
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
                } else {
                    $newFilteredLog['requestType'] = $orderLog['OSC_AMAZON_RESPONSE_MSG'];
                }

                $filteredLogs[] = $newFilteredLog;
            }

            if (!empty($orderLogs)) {
                $this->addTplParam('orderLogs', $filteredLogs);
            } else {
                $filteredLog = [];
                $filteredLog['time'] = 'no data';
                $filteredLog['identifier'] = 'no data';
                $filteredLog['requestType'] = 'no data';
                $filteredLogs[] = $filteredLog;
                $this->addTplParam('orderLogs', $filteredLogs);
            }

            if (!empty($ipnLogs)) {
                $this->addTplParam('ipnLogs', $ipnLogs);
            } else {
                $ipnLog = [];
                $ipnLog['time'] = 'no data';
                $ipnLog['identifier'] = 'no data';
                $ipnLog['requestType'] = 'no data';
                $ipnLogs[] = $ipnLog;
                $this->addTplParam('ipnLogs', $ipnLogs);
            }
        }

        $this->addtplParam('isOneStepCapture', $isOneStepCapture);
        $this->addTplParam('isCaptured', $isCaptured);

        return parent::render();
    }

    public function refundpayment(): void
    {
        $oOrder = oxNew(Order::class);

        if (
            $oOrder->load($this->getEditObjectId()) &&
            Constants::isAmazonPayment($oOrder->oxorder__oxpaymenttype->value) &&
            $oOrder->getId() !== null
        ) {
            $logger = new Logger();
            OxidServiceProvider::getAmazonService()->createRefund(
                $oOrder->getId(),
                $logger
            );
        }
    }
}
