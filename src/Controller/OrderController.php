<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidEsales\Eshop\Application\Component\UserComponent;
use OxidEsales\Eshop\Application\Model\DeliveryList;
use OxidEsales\Eshop\Application\Model\DeliverySetList;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\Address;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Payload;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

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

        $session = Registry::getSession();
        $exclude = $this->getViewConfig()->isAmazonExclude();
        $amazonService = OxidServiceProvider::getAmazonService();
        $oBasket = $this->getBasket();
        $paymentId = $oBasket->getPaymentId();
        if ($exclude || ($paymentId && !Constants::isAmazonPayment($paymentId))) {
            parent::init();
            return;
        }

        $amazonServiceIsActive = $amazonService->isAmazonSessionActive();
        if ($amazonServiceIsActive) {
            $this->initAmazonPayExpress($amazonService, $session);
        } else {
            $this->initAmazonPay();
        }
        parent::init();
    }

    protected function initAmazonPay()
    {
        $this->setAmazonPayAsPaymentMethod(Constants::PAYMENT_ID);
    }

    protected function initAmazonPayExpress(
        \OxidSolutionCatalysts\AmazonPay\Core\AmazonService $amazonService,
        \OxidEsales\Eshop\Core\Session $session
    ): void {
        $user = $this->getUser();
        $activeUser = false;
        if ($user) {
            $activeUser = $user->loadActiveUser();
        }
        $amazonSession = $amazonService->getCheckoutSession();
        // Create guest user if not logged in
        if (!$activeUser) {
            $userComponent = oxNew(UserComponent::class);
            $userComponent->createGuestUser($amazonSession);

            $this->setAmazonPayAsPaymentMethod(Constants::PAYMENT_ID_EXPRESS);
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=order', false, 302);
        } else {
            // if Amazon provides a shipping address use it
            if ($amazonSession['response']['shippingAddress']) {
                $deliveryAddress = Address::mapAddressToDb(
                    $amazonSession['response']['shippingAddress'],
                    'oxaddress__'
                );
                $session->setVariable(Constants::SESSION_DELIVERY_ADDR, $deliveryAddress);
                $this->setAmazonPayAsPaymentMethod(Constants::PAYMENT_ID_EXPRESS);
            } else {
                // if amazon does not provide a shipping address, and we already have an oxid user,
                // use oxid-user-data
                $this->setAmazonPayAsPaymentMethod(Constants::PAYMENT_ID_EXPRESS);
                $session->deleteVariable(Constants::SESSION_DELIVERY_ADDR);
            }
        }
    }

    public function execute()
    {
        $basket = $this->getSession()->getBasket();
        $exclude = $this->getViewConfig()->isAmazonExclude();
        $paymentId = $basket->getPaymentId() ?? '';
        $isAmazonPayment = Constants::isAmazonPayment($paymentId);

        $amazonSessionId = Registry::getRequest()->getRequestParameter(Constants::CHECKOUT_REQUEST_PARAMETER_ID);
        if (!empty($amazonSessionId)) {
            OxidServiceProvider::getAmazonService()->storeAmazonSession($amazonSessionId);
        }

        $isAmazonSessionActive = OxidServiceProvider::getAmazonService()->isAmazonSessionActive();
        // if payment is 'oxidamazon' but we do not have a Amazon Pay Session
        // or Amazon Pay is excluded stop executing order
        if (
            (
                $isAmazonPayment &&
                !$isAmazonSessionActive
            ) ||
            $exclude
        ) {
            Registry::getUtilsView()->addErrorToDisplay('MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT');
            OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
            return;
        } elseif ($isAmazonPayment) {
            // if payment is 'oxidamazon' call parent::execute to validate and finalize order
            // then try to complete order at Amazon Pay
            $ret = parent::execute();
            // if order is validated and finalized complete Amazon Pay
            if ($ret === 'thankyou') {
                if ($paymentId === Constants::PAYMENT_ID_EXPRESS) {
                    $this->completeAmazonPaymentExpress();
                } elseif ($paymentId === Constants::PAYMENT_ID) {
                    $logger = new Logger();
                    OxidServiceProvider::getAmazonService()->processOneStepPayment($amazonSessionId, $basket, $logger);
                    $this->completeAmazonPayment();
                }
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
        $payload->setCheckoutChargeAmount(PhpHelper::getMoneyValue(
            (float)$this->getBasket()->getPrice()->getBruttoPrice()
        ));
        $amazonConfig = oxNew(Config::class);
        $payload->setCurrencyCode($amazonConfig->getPresentmentCurrency());
        $payload = $payload->removeMerchantMetadata($payload->getData());
        $amazonSessionId = OxidServiceProvider::getAmazonService()->getCheckoutSessionId();
        $orderOxId = Registry::getSession()->getVariable('sess_challenge');
        $oOrder = oxNew(Order::class);

        $isOrderLoaded = $oOrder->load($orderOxId);
        $result = OxidServiceProvider::getAmazonClient()->completeCheckoutSession(
            $amazonSessionId,
            $payload
        );

        if (
            isset($result['response']) &&
            isset($result['status']) &&
            $result['status'] === 200 &&
            $isOrderLoaded
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

    protected function completeAmazonPaymentExpress(): void
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
    public function getBillingAddressAsObj(): object
    {
        return OxidServiceProvider::getAmazonService()->getBillingAddressAsObj();
    }

    /**
     * @param string $paymentId
     * @return void
     */
    protected function setAmazonPayAsPaymentMethod(string $paymentId): void
    {
        $basket = $this->getBasket();
        $user = $this->getUser();
        $session = Registry::getSession();
        $countryOxId = $user->getActiveCountry();
        $session->setVariable('amazonCountryOxId', $countryOxId);
        $session->setVariable('paymentId', $paymentId);
        $session->setVariable('_selected_paymentid', $paymentId);


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
                    isset($paymentList[$paymentId]) &&
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
            $basket->setPayment($paymentId);
            $basket->setShipping($actShipSet);
            $session->setVariable('paymentid', $paymentId);
        } else {
            OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        }
    }
}
