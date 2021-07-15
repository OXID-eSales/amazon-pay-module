<?php

/**
 * This file is part of OXID eSales AmazonPay module.
 *
 * OXID eSales AmazonPay module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales AmazonPay module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales AmazonPay module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2020
 */

namespace OxidProfessionalServices\AmazonPay\Model;

use OxidEsales\Eshop\Core\Registry;

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
    protected function _findDelivCountry() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $deliveryCountryId = null;
        if ($this->getPaymentId() == 'oxidamazon') {
            $deliveryCountryId = Registry::getSession()->getVariable('amazonCountryOxId');
        }
        if (is_null($deliveryCountryId)) {
            $deliveryCountryId = parent::_findDelivCountry();
        }
        return $deliveryCountryId;
    }
}
