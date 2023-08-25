<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

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

        $addressService = OxidServiceProvider::getDeliveryAddressService();
        if ($addressService->isPaymentInSessionIsAmazonPay()) {
            $addressService->moveInSession();
        }

        return $returnValue;
    }
}
