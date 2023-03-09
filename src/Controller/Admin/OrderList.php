<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Model\Order;

/**
 * Class OrderListController
 * @mixin \OxidEsales\EshopCommunity\Application\Controller\Admin\OrderList
 */
class OrderList extends OrderList_parent
{
    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function cancelOrder()
    {
        $sOxId = $this->getEditObjectId();
        if (!$sOxId) {
            return;
        }

        $oOrder = oxNew(Order::class);
        if (!$oOrder->load($sOxId)) {
            return;
        }
        /** @var  string $paymentType */
        $paymentType = $oOrder->getFieldData('oxpaymenttype');
        if (Constants::isAmazonPayment($paymentType)) {
            $logger = new Logger();
            OxidServiceProvider::getAmazonService()->createRefund(
                $sOxId,
                (float)$oOrder->getTotalOrderSum(),
                $logger
            );
        }

        parent::cancelOrder();
    }
}
