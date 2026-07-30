<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Core\RefundMailService;
use OxidEsales\Eshop\Application\Model\Order;

/**
 * Class OrderListController
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\OrderList
 */
class OrderList extends OrderList_parent
{
    /**
     * @inheritDoc
     *
     * @return void
     *
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
            parent::cancelOrder();

            return;
        }

        /** @var  string $paymentType */
        $paymentType = $oOrder->getFieldData('oxpaymenttype');
        if (!Constants::isAmazonPayment($paymentType)) {
            parent::cancelOrder();

            return;
        }

        $config = new Config();
        $refundedAmount = null;
        $currency = (string)$oOrder->getFieldData('oxcurrency');

        if ($config->automatedRefundActivated()) {
            $logger = new Logger();
            $refundAmount = (float)$oOrder->getTotalOrderSum();
            // the cancel context suppresses the refund mail, this method sends one
            // mail covering the cancellation and the refunded amount instead
            $refunded = OxidServiceProvider::getAmazonService()->createRefund(
                $sOxId,
                $refundAmount,
                $logger,
                Constants::REFUND_CONTEXT_CANCEL
            );
            // only `true` means Amazon confirmed the refund, a string is an error
            // message and null means no money was moved
            if ($refunded === true) {
                $refundedAmount = $refundAmount;
            }
        }

        parent::cancelOrder();

        // after the order was actually cancelled, and regardless of whether a
        // refund happened: a cancellation is worth a confirmation on its own
        $mailService = oxNew(RefundMailService::class);
        $mailService->sendCancelMail($oOrder, $refundedAmount, $currency);
    }
}
