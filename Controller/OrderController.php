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
use OxidEsales\Eshop\Application\Model\DeliveryList;
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
        $amazonService = OxidServiceProvider::getAmazonService();

        if (!$exclude &&
            $amazonService->isAmazonSessionActive()
        ) {
            $amazonSession = $amazonService->getCheckoutSession();
            // Create guest user if not logged in
            if (!$user) {
                $userComponent = oxNew(UserComponent::class);
                $userComponent->createGuestUser($amazonSession);

                $this->setAmazonPayAsPaymentMethod();
                Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=order', false, 302);
            } else {
                // if Amazon provides a shipping address use it
                if ($amazonSession['response']['shippingAddress']) {
                    // we check only the shippingAddress
                    $mappedDeliveryFields = Address::mapAddressToDb(
                        $amazonSession['response']['shippingAddress'],
                        'oxaddress__'
                    );
                    $missingDeliveryFields = Address::collectMissingRequiredDeliveryFields($mappedDeliveryFields);
                    $session->deleteVariable('amazonMissingDeliveryFields');
                    if (count($missingDeliveryFields)) {
                        $session->setVariable('amazonMissingDeliveryFields', $missingDeliveryFields);
                    }
                    $deliveryAddress = array_merge($mappedDeliveryFields, $missingDeliveryFields);
                    $session->setVariable('amazondeladr', $deliveryAddress);
                    $this->setAmazonPayAsPaymentMethod();
                }
                // if amazon does not provide a shipping address and we already have an oxid user, use oxid-user-data
                else {
                    $this->setAmazonPayAsPaymentMethod();
                    $session->deleteVariable('amazondeladr');
                }
            }
        }
        
        parent::init();
    }

    public function execute()
    {
        $basket = $this->getSession()->getBasket();
        $exclude = $this->getViewConfig()->isAmazonExclude();

        // if payment is 'oxidamazon' but we do not have a Amazon Pay Session
        // or Amazon Pay is excluded stop executing order
        if (
            ($basket->getPaymentId() === 'oxidamazon' &&
                !OxidServiceProvider::getAmazonService()->isAmazonSessionActive()) ||
            $exclude
        ) {
            Registry::getUtilsView()->addErrorToDisplay('MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT');
            OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
            return;
        }

        // if payment is 'oxidamazon' call parent::execute to validate and finalize order
        // then try to complete order at Amazon Pay
        else if ($basket->getPaymentId() === 'oxidamazon' ) {
            $ret = parent::execute();
            // if order is validated and finalized complete Amazon Pay
            if ($ret === 'thankyou' ) {
                $this->completeAmazonPayment();
            }
        }

        // in all other cases return parent
        if (!$ret) {
            $ret = parent::execute();
        }
        return $ret;
    }

    protected function completeAmazonPayment()
    {
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

        if (
            isset($result['response']) &&
            isset($result['status']) &&
            $result['status'] === 200 &&
            $oOrder->isLoaded() &&
            !empty((PhpHelper::getArrayValue('amazonPayRedirectUrl', PhpHelper::jsonToArray($result['response']))))
        ) {
            $response = PhpHelper::jsonToArray($result['response']);
            Registry::getUtils()->redirect(PhpHelper::getArrayValue('amazonPayRedirectUrl', $response), false, 301);
        } else {
            Registry::getUtilsView()->addErrorToDisplay('MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT');
            OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
            if ($oOrder && $oOrder->isLoaded()) {
                $oOrder->delete();
            }
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=payment', false, 302);
        }

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

    protected function setAmazonPayAsPaymentMethod($countryOxId = null)
    {
        $basket = $this->getBasket();
        $user = $this->getUser();
        $session = Registry::getSession();
        $countryOxId = $countryOxId ?? $user->getActiveCountry();
        $session->setVariable('amazonCountryOxId', $countryOxId);

        $actShipSet = null;
        $fallbackShipSet = null;
        if ($basket) {
            $lastShipSet = $basket->getShippingId();
        }

        $deliverySetListObj = Registry::get(DeliverySetList::class);
        $deliverySetList = $deliverySetListObj->getDeliverySetList($user, $countryOxId);
        if ($deliverySetListObj->count()) {
            $payListObj = Registry::get(PaymentList::class);
            $delListObj = Registry::get(DeliveryList::class);
            $currency = Registry::getConfig()->getActShopCurrencyObject();

            $basketPrice = $basket->getPriceForPayment() / $currency->rate;

            foreach ($deliverySetList as $shipSetId => $shipSet) {
                $paymentList = $payListObj->getPaymentList($shipSetId, $basketPrice, $user);
                if (
                    isset($paymentList['oxidamazon']) &&
                    $delListObj->hasDeliveries($basket, $user, $countryOxId, $shipSetId)
                ) {
                    if (is_null($fallbackShipSet)) {
                        $fallbackShipSet = $shipSetId;
                    }
                    if ($shipSetId == $lastShipSet) {
                        $actShipSet = $lastShipSet;
                    }
                }
            }

            if (!$actShipSet) {
                if ($lastShipSet) {
                    Registry::getUtilsView()->addErrorToDisplay('AMAZON_PAY_LASTSHIPSETNOTVALID');
                }
                $actShipSet = $fallbackShipSet;
            }
        }

        if ($actShipSet) {
            $basket->setPayment('oxidamazon');
            $basket->setShipping($actShipSet);
            $session->setVariable('paymentid', 'oxidamazon');
        } else {
            OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        }
    }
}
