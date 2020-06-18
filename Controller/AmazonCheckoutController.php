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

namespace OxidProfessionalServices\AmazonPay\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * Handles Amazon checkout sessions
 */
class AmazonCheckoutController extends FrontendController
{
    /**
     * Creates a new amazon checkout session
     *
     * @throws \Exception
     */
    public function createCheckout(): void
    {
        $result = OxidServiceProvider::getAmazonClient()->createCheckoutSession();

        if ($result['status'] !== 201) {
            OxidServiceProvider::getLogger()->info('create checkout failed', $result);
            http_response_code(500);
        } else {
            OxidServiceProvider::getAmazonService()
                ->storeAmazonSession(PhpHelper::jsonToArray($result['response'])['checkoutSessionId']);
        }

        Registry::getUtils()->setHeader('Content-type:application/json; charset=utf-8');
        Registry::getUtils()->showMessageAndExit($result['response']);
    }

    public function cancelAmazonPayment(): void
    {
        OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=payment', false, 301);
    }
}
