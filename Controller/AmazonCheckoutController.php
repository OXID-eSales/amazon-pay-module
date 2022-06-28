<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
        // if an article is given, we put it in the shopping cart
        if ($sProductId = Registry::getRequest()->getRequestParameter('anid')) {
            $database = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
            $database->startTransaction();
            try {
                $basket = Registry::getSession()->getBasket();
                $basket->addToBasket(
                    $sProductId,
                    1
                );
                $basket->calculateBasket(true);
            } catch (\Exception $exception) {
                $database->rollbackTransaction();
                throw $exception;
            }
        }

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
