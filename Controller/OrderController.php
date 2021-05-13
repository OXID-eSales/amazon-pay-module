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
use OxidEsales\Eshop\Core\Field;
use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
use OxidProfessionalServices\AmazonPay\Core\Payload;
use OxidProfessionalServices\AmazonPay\Core\Config;
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
            if (OxidServiceProvider::getAmazonService()->isAmazonSessionActive()) {
                // Create guest user if not logged in
                if ($user === false) {
                    $userComponent = oxNew('oxcmp_user');
                    $userComponent->createGuestUser(OxidServiceProvider::getAmazonService()->getCheckoutSession());
                    $this->setAmazonPayAsPaymentMethod();
                    Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=order', false, 302);
                } else {
                    $this->setAmazonPayAsPaymentMethod();
                }
            }
        }

        parent::init();
    }

    public function execute()
    {
        // check missing amazonfields
        $oUser = $this->getUser();

        $missingError = false;

        if ($oUser) {
            $oDelAdress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
            $oDelAdress->load(\OxidEsales\Eshop\Core\Registry::getSession()->getVariable('deladrid'));

            $changeMissingBillingFields = false;
            $changeMissingDeliveryFields = false;
            $oConfig = $this->getConfig();
            $missingRequestBillingFields = $oConfig->getRequestParameter('missing_amazon_invadr');
            $missingRequestDeliveryFields = $oConfig->getRequestParameter('missing_amazon_deladr');
            foreach ($this->getMissingRequiredBillingFields() as $key => $value) {
                if (isset($missingRequestBillingFields[$key])) {
                    if ($missingRequestBillingFields[$key]) {
                        $changeMissingBillingFields = true;
                        $oUser->{$key} = new Field($missingRequestBillingFields[$key], Field::T_RAW);
                    } else {
                        $missingError = true;
                    }
                }
            }

            foreach ($this->getMissingRequiredDeliveryFields() as $key => $value) {
                if (isset($missingRequestDeliveryFields[$key])) {
                    if ($missingRequestDeliveryFields[$key]) {
                        $changeMissingDeliveryFields = true;
                        $oDelAdress->{$key} = new Field($missingRequestDeliveryFields[$key], Field::T_RAW);
                    } else {
                        $missingError = true;
                    }
                }
            }
            if ($changeMissingDeliveryFields) {
                $oDelAdress->save();
            }
            if ($changeMissingBillingFields) {
                $oUser->save();
            }
        }
        if ($missingError) {
            return;
        }

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

        $activeShop = Registry::getConfig()->getActiveShop();

        $payload->setMerchantStoreName($activeShop->oxshops__oxcompany->value);
        $payload->setNoteToBuyer($activeShop->oxshops__oxordersubject->value);

        $amazonConfig = oxNew(Config::class);

        $payload->setCurrencyCode($amazonConfig->getPresentmentCurrency());

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

    /**
     * Template getter for amazon bill address
     *
     * @return array
     */
    public function getMissingRequiredBillingFields(): array
    {
        return OxidServiceProvider::getAmazonService()->getMissingRequiredBillingFields();
    }

    /**
     * Template getter for amazon bill address
     *
     * @return array
     */
    public function getMissingRequiredDeliveryFields(): array
    {
        return OxidServiceProvider::getAmazonService()->getMissingRequiredDeliveryFields();
    }

    /**
     * Template getter for amazon bill address
     *
     * @return object
     */
    public function getFilteredDeliveryAddress()
    {
        return OxidServiceProvider::getAmazonService()->getFilteredDeliveryAddress();
    }

    /**
     * Template getter for amazon bill address
     *
     * @return object
     */
    public function getFilteredBillingAddress()
    {
        return OxidServiceProvider::getAmazonService()->getFilteredBillingAddress();
    }

    private function setAmazonPayAsPaymentMethod()
    {
        $payment = $this->getBasket()->getPaymentId();
        if (($payment !== 'oxidamazon')) {
            $this->getBasket()->setPayment('oxidamazon');
        }
    }
}
