<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * Class OrderListController
 * @mixin \OxidEsales\EshopCommunity\Application\Controller\Admin\OrderList
 */
class OrderList extends OrderList_parent
{
    /**
     * @return void
     */
    public function cancelOrder()
    {
        OxidServiceProvider::getAmazonService()->processCancel($this->getEditObjectId());
        parent::cancelOrder();
    }
}
