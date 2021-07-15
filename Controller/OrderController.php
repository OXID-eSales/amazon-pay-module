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

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Application\Model\DeliverySetList;
use OxidEsales\Eshop\Application\Component\UserComponent;
use OxidProfessionalServices\AmazonPay\Core\Helper\Address;
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
        $session = Registry::getSession();

        $exclude = $this->getViewConfig()->isAmazonExclude();

        if (!$exclude) {
            if (OxidServiceProvider::getAmazonService()->isAmazonSessionActive()) {
                $amazonSession = OxidServiceProvider::getAmazonService()->getCheckoutSession();
                $country = oxNew(Country::class);
                $countryOxId = $country->getIdByCode($amazonSession['response']['shippingAddress']['countryCode']);
                // Create guest user if not logged in
                if ($user === false) {
                    $userComponent = oxNew(UserComponent::class);
                    $userComponent->createGuestUser($amazonSession);

                    $this->setAmazonPayAsPaymentMethod($countryOxId);
                    Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=order', false, 302);
                } else {
                    $this->setAmazonPayAsPaymentMethod($countryOxId);
                    $mappedBillingFields = Address::mapAddressToDb(
                        $amazonSession['response']['billingAddress'],
                        'oxuser__'
                    );
                    $mappedDeliveryFields = Address::mapAddressToDb(
                        $amazonSession['response']['shippingAddress'],
                        'oxaddress__'
                    );
                    $missingBillingFields = Address::collectMissingRequiredBillingFields($mappedBillingFields);
                    $missingDeliveryFields = Address::collectMissingRequiredDeliveryFields($mappedDeliveryFields);
                    $session->deleteVariable('amazonMissingBillingFields');
                    $session->deleteVariable('amazonMissingDeliveryFields');
                    if (count($missingBillingFields)) {
                        $session->setVariable('amazonMissingBillingFields', $missingBillingFields);
                    }
                    if (count($missingDeliveryFields)) {
                        $session->setVariable('amazonMissingDeliveryFields', $missingDeliveryFields);
                    }
                }
            }
        }

        parent::init();
    }

    public function execute()
    {
        $ret = parent::execute();

        if (strpos($ret, 'thankyou') === false) {
            return $ret;
        }

        $oBasket = $this->getSession()->getBasket();

        if ($oBasket->getPaymentId() !== 'oxidamazon') {
            return $ret;
        }

        $exclude = $this->getViewConfig()->isAmazonExclude();

        if ($exclude) {
            return $ret;
        }

        $payload = new Payload();

        $orderOxId = Registry::getSession()->getVariable('sess_challenge');
        $oOrder = oxNew(Order::class);
        if ($oOrder->load($orderOxId)) {
             $payload->setMerchantReferenceId($oOrder->oxorder__oxordernr->value);
        }
        $payload->setPaymentDetailsChargeAmount(PhpHelper::getMoneyValue(
            $this->getBasket()->getPrice()->getBruttoPrice()
        ));

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

    private function setAmazonPayAsPaymentMethod($countryOxId = null)
    {
        $basket = $this->getBasket();
        $user = $this->getUser();
        $session = Registry::getSession();
        $countryOxId = $countryOxId ?? $user->getActiveCountry();
        $session->setVariable('amazonCountryOxId', $countryOxId);
        $payment = $basket->getPaymentId();
        $possibleDeliverySets = [];

        $deliverySetList = Registry::get(DeliverySetList::class)
        ->getDeliverySetList(
            $user,
            $countryOxId
        );
        foreach ($deliverySetList as $deliverySet) {
            $paymentList = Registry::get(PaymentList::class)->getPaymentList(
                $deliverySet->getId(),
                $basket->getPrice()->getBruttoPrice(),
                $user
            );
            if (array_key_exists('oxidamazon', $paymentList)) {
                $possibleDeliverySets[] = $deliverySet->getId();
            }
        }

        if (count($possibleDeliverySets)) {
            $basket->setPayment('oxidamazon');
            $session->setVariable('paymentid', 'oxidamazon');
            $basket->setShipping(reset($possibleDeliverySets));
        }
    }
}
