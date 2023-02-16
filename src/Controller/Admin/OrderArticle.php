<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Model\Order;

class OrderArticle extends OrderArticle_parent
{
    /**
     * @inheritdoc
     */
    public function deleteThisArticle(): void
    {
        $this->refundAmazon();
        parent::deleteThisArticle();
    }

    /**
     * @inheritdoc
     */
    public function storno(): void
    {
        $this->refundAmazon();
        parent::storno();
    }

    private function refundAmazon(): void
    {
        // get article id
        $sOrderArtId = (string) Registry::getConfig()->getRequestParameter('sArtID');
        $sOrderId = $this->getEditObjectId();

        $oOrderArticle = oxNew(\OxidEsales\Eshop\Application\Model\OrderArticle::class);
        $oOrder = oxNew(Order::class);

        // order and order article exits?
        if ($oOrderArticle->load($sOrderArtId) && $oOrder->load($sOrderId)) {
            // deleting record
            if (Constants::isAmazonPayment($oOrderArticle->getOrder()->oxorder__oxpaymenttype->value)) {
                $logger = new Logger();
                OxidServiceProvider::getAmazonService()->createRefund(
                    $oOrderArticle->getOrder()->getId(),
                    (float)$oOrderArticle->getTotalBrutPriceFormated(),
                    $logger
                );
            }
        }
    }
}
