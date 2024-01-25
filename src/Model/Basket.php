<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Model;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Basket
 */
class Basket extends Basket_parent
{
    /**
     * Tries to fetch user delivery country ID
     *
     * @return string
     */
    protected function findDelivCountry() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $deliveryCountryId = null;
        $paymentId = $this->getPaymentId() ?: '';
        if (Constants::isAmazonPayment($paymentId)) {
            /** @var string $deliveryCountryId */
            $deliveryCountryId = Registry::getSession()->getVariable('amazonCountryOxId');
        }
        if (is_null($deliveryCountryId)) {
            $deliveryCountryId = parent::findDelivCountry();
        }
        return $deliveryCountryId;
    }
}
