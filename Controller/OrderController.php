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

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
use OxidProfessionalServices\AmazonPay\Core\Payload;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * Class OrderController
 * @mixin \OxidEsales\Eshop\Application\Controller\OrderController
 */
class OrderController extends OrderController_parent
{
    public function init()
    {
        /** @var User $user */
        $user = $this->getUser();

        $exclude = $this->getViewConfig()->isAmazonExclude();

        if (!$exclude) {
            // Create guest user if not logged in
            if ($user === false) {
                $userComponent = oxNew('oxcmp_user');
                $userComponent->createGuestUser(OxidServiceProvider::getAmazonService()->getCheckoutSession());
                $payment = $this->getBasket()->getPaymentId();
                if (($payment !== 'oxidamazon')) {
                    $this->getBasket()->setPayment('oxidamazon');
                }
                Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=order', false, 302);
            }
        }

        parent::init();

        if (!$exclude) {
            if ($this->getBasket()->getBruttoSum() !== null) {
                if (OxidServiceProvider::getAmazonService()->isAmazonSessionActive()) {
                    $user->assign(OxidServiceProvider::getAmazonService()->getBillingAddress());
                }

                if ($this->getBasket()->getBruttoSum() !== null) {
                    $payment = $this->getBasket()->getPaymentId();
                    if (
                        ($payment !== 'oxidamazon') &&
                        OxidServiceProvider::getAmazonService()->isAmazonSessionActive()
                    ) {
                        $this->getBasket()->setPayment('oxidamazon');
                        Registry::getUtils()
                            ->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=order', false, 302);
                    }
                }
            }
        }
    }

    public function execute()
    {
        $ret = parent::execute();

        if (strpos($ret, 'thankyou') === false) {
            return $ret;
        }

        $exclude = $this->getViewConfig()->isAmazonExclude();

        if ($exclude) {
            return $ret;
        }

        $payload = new Payload();

        $payload->setPaymentDetailsChargeAmount(PhpHelper::getMoneyValue($this->getBasket()->getBruttoSum()));

        if (OxidServiceProvider::getAmazonClient()->getModuleConfig()->isOneStepCapture()) {
            $payload->setPaymentIntent('AuthorizeWithCapture');
            $payload->setCanHandlePendingAuthorization(false);
        } else {
            $payload->setPaymentIntent('Authorize');
            $payload->setCanHandlePendingAuthorization(true);
        }

        $result = OxidServiceProvider::getAmazonClient()->updateCheckoutSession(
            OxidServiceProvider::getAmazonService()->getCheckoutSessionId(),
            $payload->getData()
        );

        $response = PhpHelper::jsonToArray($result['response']);

        Registry::getUtils()->redirect(PhpHelper::getArrayValue('amazonPayRedirectUrl', $response), false, 301);
    }
}
