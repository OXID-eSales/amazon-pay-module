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

namespace OxidProfessionalServices\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

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

        if ($oOrder->load($this->getEditObjectId()) && $oOrder->oxorder__oxpaymenttype->value == 'oxidamazon') {
            $orderLogs = OxidServiceProvider::getAmazonService()->getOrderLogs($oOrder);

            foreach ($orderLogs as $orderLog) {
                $newFilteredLog = [];

                if ($orderLog['OXPS_AMAZON_REQUEST_TYPE'] === 'IPN') {
                    $ipnLog = [];
                    $ipnLog['time'] = $orderLog['OXTIMESTAMP'];
                    $ipnLog['identifier'] = $orderLog['OXPS_AMAZON_IDENTIFIER'];
                    $ipnLog['requestType'] = $orderLog['OXPS_AMAZON_OBJECT_TYPE'];
                    $ipnLogs[] = $ipnLog;
                    continue;
                }

                if (in_array($orderLog['OXPS_AMAZON_PAYLOGID'], $existingItems, true)) {
                    continue;
                }

                $existingItems[] = $orderLog['OXPS_AMAZON_PAYLOGID'];

                $newFilteredLog['time'] = $orderLog['OXTIMESTAMP'];
                $newFilteredLog['identifier'] = $orderLog['OXPS_AMAZON_IDENTIFIER'];
                $newFilteredLog['statusCode'] = $orderLog['OXPS_AMAZON_STATUS_CODE'] === '200' ? 'success' : 'error';

                if (strpos($orderLog['OXPS_AMAZON_REQUEST_TYPE'], 'Error') !== false) {
                    $newFilteredLog['statusCode'] = 'error';
                    $newFilteredLog['identifier'] = 'ORDER ID:' . $orderLog['OXPS_AMAZON_OXORDERID'];
                }

                if ($orderLog['OXPS_AMAZON_RESPONSE_MSG'] === 'Captured') {
                    $newFilteredLog['requestType'] = 'Payment captured';
                    $isCaptured = true;
                    $isOneStepCapture = false;
                } elseif ($orderLog['OXPS_AMAZON_RESPONSE_MSG'] === 'Completed') {
                    $newFilteredLog['requestType'] = 'Checkout complete';
                    $isOneStepCapture = false;
                } elseif ($orderLog['OXPS_AMAZON_RESPONSE_MSG'] === 'Completed & Captured') {
                    $newFilteredLog['requestType'] = 'Checkout & Capture complete';
                    $isOneStepCapture = true;
                    $isCaptured = true;
                } elseif ($orderLog['OXPS_AMAZON_RESPONSE_MSG'] === 'Canceled') {
                    $newFilteredLog['requestType'] = 'Canceled';
                    $isCaptured = true;
                } elseif ($orderLog['OXPS_AMAZON_RESPONSE_MSG'] === 'Refunded') {
                    $newFilteredLog['requestType'] = 'Refund Complete';
                    $isCaptured = true;
                } else {
                    $newFilteredLog['requestType'] = $orderLog['OXPS_AMAZON_RESPONSE_MSG'];
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
}
