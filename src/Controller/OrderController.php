<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
use OxidProfessionalServices\AmazonPay\Core\Constants;
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
    /**
     * @return void
     */
    public function init()
    {
        /** @var User $user */
        $user = $this->getUser();
        $session = Registry::getSession();
        $exclude = $this->getViewConfig()->isAmazonExclude();
        $amazonService = OxidServiceProvider::getAmazonService();

        if (
            !$exclude &&
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
                    $deliveryAddress = Address::mapAddressToDb(
                        $amazonSession['response']['shippingAddress'],
                        'oxaddress__'
                    );
                    $session->setVariable(Constants::SESSION_DELIVERY_ADDR, $deliveryAddress);
                    $this->setAmazonPayAsPaymentMethod();
                } else {
                    // if amazon does not provide a shipping address and we already have an oxid user,
                    // use oxid-user-data
                    $this->setAmazonPayAsPaymentMethod();
                    $session->deleteVariable(Constants::SESSION_DELIVERY_ADDR);
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
            ($basket->getPaymentId() === Constants::PAYMENT_ID &&
                !OxidServiceProvider::getAmazonService()->isAmazonSessionActive()) ||
            $exclude
        ) {
            Registry::getUtilsView()->addErrorToDisplay('MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT');
            OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
            return;
        } elseif ($basket->getPaymentId() === Constants::PAYMENT_ID) {
            // if payment is 'oxidamazon' call parent::execute to validate and finalize order
            // then try to complete order at Amazon Pay
            $ret = parent::execute();
            // if order is validated and finalized complete Amazon Pay
            if ($ret === 'thankyou') {
                $this->completeAmazonPayment();
            }
        }

        // in all other cases return parent
        if (!$ret) {
            $ret = parent::execute();
        }
        return $ret;
    }

    protected function completeAmazonPayment(): void
    {
        $payload = new Payload();
        $orderOxId = Registry::getSession()->getVariable('sess_challenge');
        $oOrder = oxNew(Order::class);
        if ($oOrder->load($orderOxId)) {
            $payload->setMerchantReferenceId($oOrder->oxorder__oxordernr->value);
        }
        $payload->setPaymentDetailsChargeAmount(PhpHelper::getMoneyValue(
            (float)$this->getBasket()->getPrice()->getBruttoPrice()
        ));

        $activeShop = Registry::getConfig()->getActiveShop();
        $payload->setMerchantStoreName($activeShop->oxshops__oxcompany->value);
        $payload->setNoteToBuyer($activeShop->oxshops__oxordersubject->value);

        $amazonConfig = oxNew(Config::class);
        $payload->setCurrencyCode($amazonConfig->getPresentmentCurrency());

        if (OxidServiceProvider::getAmazonClient()->getModuleConfig()->isOneStepCapture()) {
            $payload->setPaymentIntent('AuthorizeWithCapture');
            $payload->setCanHandlePendingAuthorization('false');
        } else {
            $payload->setPaymentIntent('Authorize');
            $payload->setCanHandlePendingAuthorization('true');
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
            $redirectUrl = PhpHelper::getArrayValue('amazonPayRedirectUrl', $response);
            if ($redirectUrl !== false) {
                Registry::getUtils()->redirect($redirectUrl, false, 301);
            }
        } else {
            Registry::getUtilsView()->addErrorToDisplay('MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT');
            OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
            if ($oOrder->isLoaded()) {
                $oOrder->delete();
            }
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=payment', false, 302);
        }
    }

    /**
     * Template getter for amazon bill address
     *
     * @return object
     */
    public function getDeliveryAddressAsObj()
    {
        return OxidServiceProvider::getAmazonService()->getDeliveryAddressAsObj();
    }

    /**
     * Template getter for amazon bill address
     *
     * @return object
     */
    public function getBillingAddressAsObj()
    {
        return OxidServiceProvider::getAmazonService()->getBillingAddressAsObj();
    }

    protected function setAmazonPayAsPaymentMethod(): void
    {
        $basket = $this->getBasket();
        $user = $this->getUser();
        $session = Registry::getSession();
        $countryOxId = $user->getActiveCountry();
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
                    isset($paymentList[Constants::PAYMENT_ID]) &&
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
            $basket->setPayment(Constants::PAYMENT_ID);
            $basket->setShipping($actShipSet);
            $session->setVariable('paymentid', Constants::PAYMENT_ID);
        } else {
            OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        }
    }
}
