<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

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
     */
    public function cancelOrder(): void
    {
        $sOxId = $this->getEditObjectId();
        if (!$sOxId) {
            return;
        }

        $oOrder = oxNew(Order::class);
        if (!$oOrder->load($sOxId)) {
            return;
        }

        if (Constants::isAmazonPayment($oOrder->oxorder__oxpaymenttype->value)) {
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
