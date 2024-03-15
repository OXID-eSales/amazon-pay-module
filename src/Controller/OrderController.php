<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use Exception;
use OxidEsales\Eshop\Application\Component\UserComponent;
use OxidEsales\Eshop\Application\Model\Address as CoreAddress;
use OxidEsales\Eshop\Application\Model\DeliveryList;
use OxidEsales\Eshop\Application\Model\DeliverySetList;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonService;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\Address;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Payload;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use stdClass;

/**
 * Class OrderController
 * @mixin \OxidEsales\Eshop\Application\Controller\OrderController
 */
class OrderController extends OrderController_parent
{
    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws Exception
     */
    public function init()
    {
        $session = Registry::getSession();
        $exclude = Registry::get(Config::class)->isAmazonExcluded('');
        $oBasket = $this->getBasket();
        $paymentId = $oBasket->getPaymentId() ?: '';

        if (!$exclude && ($paymentId === '' || Constants::isAmazonPayment($paymentId))) {
            $amazonService = OxidServiceProvider::getAmazonService();
            $isAmazonSessionActive = $amazonService->isAmazonSessionActive();

            if ($isAmazonSessionActive) {
                $this->initAmazonPayExpress($amazonService, $session);
            }

            if (!$isAmazonSessionActive && Constants::isAmazonPayment($paymentId)) {
                $this->initAmazonPay();
            }
        }
        parent::init();
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $service = OxidServiceProvider::getTermsAndConditionService();
        $service->resetConfirmOnGet();

        return parent::render();
    }

    /**
     * @return void
     */
    protected function initAmazonPay()
    {
        $this->setAmazonPayAsPaymentMethod(Constants::PAYMENT_ID);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function initAmazonPayExpress(AmazonService $amazonService, Session $session)
    {
        $user = $this->getUser();
        $amazonSession = $amazonService->getCheckoutSession();
        // Create guest user if not logged in
        if (!$user instanceof User) {
            /** @var \OxidSolutionCatalysts\AmazonPay\Component\UserComponent $userComponent */
            $userComponent = oxNew(UserComponent::class);
            $userComponent->createGuestUser($amazonSession);

            $this->setAmazonPayAsPaymentMethod(Constants::PAYMENT_ID_EXPRESS);
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=order', false);
        }
        if ($user instanceof User) {
            // if Amazon provides a shipping address use it
            if ($amazonSession['response']['shippingAddress']) {
                $deliveryAddress = Address::mapAddressToDb(
                    $amazonSession['response']['shippingAddress'],
                    'oxaddress__'
                );
                $session->setVariable(Constants::SESSION_DELIVERY_ADDR, $deliveryAddress);
            }
            if (empty($amazonSession['response']['shippingAddress'])) {
                // if amazon does not provide a shipping address, and we already have an oxid user,
                // use oxid-user-data
                $session->deleteVariable(Constants::SESSION_DELIVERY_ADDR);
            }
            $this->setAmazonPayAsPaymentMethod(Constants::PAYMENT_ID_EXPRESS);
        }
    }

    /**
     * @return mixed|string|null
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function execute()
    {
        $basket = Registry::getSession()->getBasket();
        $exclude = Registry::get(Config::class)->isAmazonExcluded('');

        $paymentId = $basket->getPaymentId();
        $isAmazonPayment = Constants::isAmazonPayment($paymentId);


        /** @var string $amazonSessionId */
        $amazonSessionId = Registry::getRequest()->getRequestParameter(Constants::CHECKOUT_REQUEST_PARAMETER_ID);
        if (!empty($amazonSessionId)) {
            OxidServiceProvider::getAmazonService()->storeAmazonSession($amazonSessionId);
        }

        $isAmazonSessionActive = OxidServiceProvider::getAmazonService()->isAmazonSessionActive();
        // if payment is 'oxidamazon' but we do not have an Amazon Pay Session
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
            return null;
        }

        $ret = null;
        if ($isAmazonPayment) {
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
                }
            }
        }

        // in all other cases return parent
        if (!$ret) {
            $ret = parent::execute();
        }
        return $ret;
    }

    /**
     * @return bool
     */
    protected function _validateTermsAndConditions()
    {
        $basket = $this->getBasket();
        $paymentId = $basket->getPaymentId();
        $isAmazonPayment = Constants::isAmazonPayment($paymentId);
        $isAmazonExpress = Constants::isAmazonExpressPayment($paymentId);

        // check T&C only for regular amazon (with express, some certain steps are skipped)
        return ($isAmazonPayment && !$isAmazonExpress) ?
            $this->validateTermsAndConditionsByAmazon() :
            parent::_validateTermsAndConditions();
    }

    /**
     * @return bool
     */
    protected function validateTermsAndConditionsByAmazon(): bool
    {
        $valid = $this->confirmAGBbyAmazon();
        if (
            $valid &&
            !$this->confirmIntangibleProdAgreementbyAmazon()
        ) {
            $valid = false;
        }

        return $valid;
    }

    /**
     * @return bool
     */
    protected function confirmAGBbyAmazon(): bool
    {
        $valid = true;

        $confirmAGB = Registry::getConfig()->getConfigParam('blConfirmAGB');

        $termsAndConditionService = OxidServiceProvider::getTermsAndConditionService();
        if (
            $confirmAGB &&
            !$termsAndConditionService->getAGBConfirmFromSession()
        ) {
            $valid = false;
        }
        return $valid;
    }

    /**
     * @return bool
     */
    protected function confirmIntangibleProdAgreementbyAmazon(): bool
    {
        $valid = true;

        $basket = $this->getBasket();
        $confirmIPA = Registry::getConfig()->getConfigParam('blEnableIntangibleProdAgreement');

        if ($confirmIPA) {
            $termsAndConditionService = OxidServiceProvider::getTermsAndConditionService();
            if (
                $basket->hasArticlesWithDownloadableAgreement() &&
                !$termsAndConditionService->getDPAConfirmFromSession()
            ) {
                $valid = false;
            }
            if (
                $valid &&
                $basket->hasArticlesWithIntangibleAgreement() &&
                !$termsAndConditionService->getSPAConfirmFromSession()
            ) {
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * @return CoreAddress|object
     */
    public function getDelAddress()
    {
        $deliveryAddressService = OxidServiceProvider::getDeliveryAddressService();
        if ($deliveryAddressService->isPaymentInSessionIsAmazonPayExpress()) {
            $delAddress = $deliveryAddressService->getTempDeliveryAddressAddress();
            if ($delAddress->getId()) {
                return $delAddress;
            }
        }

        return parent::getDelAddress();
    }

    /**
     * @return void
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    protected function completeAmazonPaymentExpress()
    {
        $payload = new Payload();
        /** @var string $orderOxId */
        $orderOxId = Registry::getSession()->getVariable('sess_challenge');
        $oOrder = oxNew(Order::class);
        if ($oOrder->load($orderOxId)) {
            /** @var string $oxOrderNr */
            $oxOrderNr = $oOrder->getFieldData('oxordernr');
            $payload->setMerchantReferenceId($oxOrderNr);
        }

        $payload->setPaymentDetailsChargeAmount(PhpHelper::getMoneyValue(
            $this->getBasket()->getPrice()->getBruttoPrice()
        ));

        $activeShop = Registry::getConfig()->getActiveShop();
        /** @var string $oxCompany */
        $oxCompany = $activeShop->getFieldData('oxcompany');
        $payload->setMerchantStoreName($oxCompany);
        /** @var string $oxOrderSubject */
        $oxOrderSubject = $activeShop->getFieldData('oxordersubject');
        $payload->setNoteToBuyer($oxOrderSubject);

        $amazonConfig = oxNew(Config::class);
        $payload->setCurrencyCode($amazonConfig->getPresentmentCurrency());

        $paymentIntent = 'Authorize';
        $canHandlePendingAuth = true;
        if (OxidServiceProvider::getAmazonClient()->getModuleConfig()->isOneStepCapture()) {
            $paymentIntent = 'AuthorizeWithCapture';
            $canHandlePendingAuth = false;
        }
        $payload->setPaymentIntent($paymentIntent);
        $payload->setCanHandlePendingAuthorization($canHandlePendingAuth);
        $payloadData = $payload->getData();
        $result = OxidServiceProvider::getAmazonClient()->updateCheckoutSession(
            OxidServiceProvider::getAmazonService()->getCheckoutSessionId(),
            $payloadData
        );

        if (
            isset($result['response']) &&
            isset($result['status']) &&
            $result['status'] === 200 &&
            $oOrder->isLoaded() &&
            !empty((PhpHelper::getArrayValue('amazonPayRedirectUrl', PhpHelper::jsonToArray($result['response']))))
        ) {
            $response = PhpHelper::jsonToArray($result['response']);
            /** @var string $redirectUrl */
            $redirectUrl = PhpHelper::getArrayValue('amazonPayRedirectUrl', $response) ?: '';
            if ($redirectUrl !== '') {
                Registry::getUtils()->redirect($redirectUrl, false, 301);
            }
            return;
        }

        Registry::getUtilsView()->addErrorToDisplay('MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT');
        $logger = new Logger();
        $logger->log('ERROR', $result['response']);
        OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        if ($oOrder->isLoaded()) {
            $oOrder->delete();
        }
        Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=payment', false);
    }

    /**
     * Template getter for amazon bill address
     *
     * @return stdClass
     */
    public function getDeliveryAddressAsObj(): stdClass
    {
        return OxidServiceProvider::getAmazonService()->getDeliveryAddressAsObj();
    }

    /**
     * Template getter for amazon bill address
     *
     * @return stdClass
     */
    public function getBillingAddressAsObj(): stdClass
    {
        return OxidServiceProvider::getAmazonService()->getBillingAddressAsObj();
    }

    /**
     * @param string $paymentId
     * @return void
     */
    protected function setAmazonPayAsPaymentMethod(string $paymentId)
    {
        $basket = $this->getBasket();
        $user = $this->getUser();
        $session = Registry::getSession();
        $countryOxId = $user->getActiveCountry();
        $session->setVariable('amazonCountryOxId', $countryOxId);
        $session->setVariable('paymentid', $paymentId);
        $session->setVariable('_selected_paymentid', $paymentId);

        $actShipSet = null;
        $fallbackShipSet = null;
        $lastShipSet = null;
        if (is_object($basket)) {
            $lastShipSet = $basket->getShippingId();
        }

        $deliverySetListObj = Registry::get(DeliverySetList::class);
        $deliverySetList = $deliverySetListObj->getDeliverySetList($user, $countryOxId);
        if ($deliverySetListObj->count()) {
            $payListObj = Registry::get(PaymentList::class);
            $delListObj = Registry::get(DeliveryList::class);
            $currency = Registry::getConfig()->getActShopCurrencyObject();

            $basketPrice = $basket->getPriceForPayment() / $currency->rate;

            foreach (array_keys($deliverySetList) as $shipSetId) {
                $paymentList = $payListObj->getPaymentList($shipSetId, $basketPrice, $user);
                if (
                    isset($paymentList[$paymentId]) &&
                    $delListObj->hasDeliveries($basket, $user, $countryOxId, $shipSetId)
                ) {
                    if (is_null($fallbackShipSet)) {
                        $fallbackShipSet = $shipSetId;
                    }
                    if ($shipSetId === $lastShipSet) {
                        $actShipSet = (string)$lastShipSet;
                    }
                }
            }

            if (!$actShipSet) {
                if ($lastShipSet) {
                    Registry::getUtilsView()->addErrorToDisplay('AMAZON_PAY_LASTSHIPSETNOTVALID');
                }
                $actShipSet = (string)$fallbackShipSet;
            }
        }

        if ($actShipSet) {
            $basket->setPayment($paymentId);
            $basket->setShipping($actShipSet);
            $session->setVariable('paymentid', $paymentId);
            return;
        }
        OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
    }
}
