<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\AmazonPay\Service\DeliveryAddressService;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\PaymentController
 */
class PaymentController extends PaymentController_parent
{
    /**
     * @return mixed
     */
    public function validatePayment()
    {
        $returnValue = parent::validatePayment();

        $addressService = ContainerFactory::getInstance()->getContainer()->get(DeliveryAddressService::class);
        if ($addressService->isPaymentInSessionIsAmazonPay()) {
            $addressService->moveInSession();
        }

        return $returnValue;
    }
}
