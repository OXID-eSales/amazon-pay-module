<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Controller\Admin;

use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * Class OrderListController
 * @mixin \OxidEsales\EshopCommunity\Application\Controller\Admin\OrderList
 */
class OrderListController extends OrderListController_parent
{
    public function cancelOrder()
    {
        OxidServiceProvider::getAmazonService()->processCancel($this->getEditObjectId());
        parent::cancelOrder();
    }
}
