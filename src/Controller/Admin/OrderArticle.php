<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\EshopCommunity\Core\Request;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Model\Order;

class OrderArticle extends OrderArticle_parent
{
    public function deleteThisArticle()
    {
        $this->refundAmazon();
        parent::deleteThisArticle();
    }

    public function storno()
    {
        $this->refundAmazon();
        parent::storno();
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    private function refundAmazon()
    {
        $request = new Request();
        // get article id
        /** @var string $sOrderArtId */
        $sOrderArtId = $request->getRequestParameter('sArtID') ?: '';
        $sOrderId = $this->getEditObjectId() ?: '';

        $oOrderArticle = oxNew(\OxidEsales\Eshop\Application\Model\OrderArticle::class);
        $oOrder = oxNew(Order::class);

        // order and order article exits?
        if ($oOrderArticle->load($sOrderArtId) && $oOrder->load($sOrderId)) {
            // deleting record
            //if (Constants::isAmazonPayment($oOrderArticle->getOrder()->oxorder__oxpaymenttype->value)) {
            /** @var  string $paymentType */
            $paymentType = $oOrder->getFieldData('oxpaymenttype');
            if (Constants::isAmazonPayment($paymentType)) {
                $logger = new Logger();
                OxidServiceProvider::getAmazonService()->createRefund(
                    $oOrder->getId(),
                    (float)$oOrderArticle->getTotalBrutPriceFormated(),
                    $logger
                );
            }
        }
    }
}
