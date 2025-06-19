<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use Exception;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Theme;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Model\User;

/**
 * Amazon Pay getters for templates
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    /**
     * @var string
     */
    public $signature = '';

    public function getAmazonConfig()
    {
        return Registry::get(Config::class);
    }

    /**
     * @return bool
     */
    public function isAmazonActive()
    {
        $config = $this->getAmazonConfig();
        $blIsActive = true;
        try {
            $config->checkHealth();
        } catch (StandardException $ex) {
            $blIsActive = false;
        }
        return $blIsActive;
    }

    /**
     * @return bool
     */
    public function displayExpressInPDP()
    {
        return $this->getAmazonConfig()->displayExpressInPDP();
    }

    /**
     * @return bool
     */
    public function useExclusion()
    {
        return $this->getAmazonConfig()->useExclusion();
    }
    /**
     * @return bool
     */
    public function socialLoginDeactivated()
    {
        return $this->getAmazonConfig()->socialLoginDeactivated();
    }

    /**
     * @return bool
     */
    public function displayExpressInMiniCartAndModal()
    {
        return $this->getAmazonConfig()->displayExpressInMiniCartAndModal();
    }

    /**
     * @return string
     */
    public function getAmazonSessionId()
    {
        return OxidServiceProvider::getAmazonService()->getCheckoutSessionId();
    }

    /**
     * @return bool
     */
    public function isAmazonSessionActive()
    {
        return OxidServiceProvider::getAmazonService()->isAmazonSessionActive();
    }

    /**
     * Get webhook controller url
     *
     * @return string
     */
    public function getCancelAmazonPaymentUrl()
    {
        return $this->getSelfLink() . 'cl=amazoncheckout&fnc=cancelAmazonPayment';
    }

    /**
     * Template getter isAmazonPaymentPossible
     *
     * @param string $paymentId
     * @return boolean
     */
    public function isAmazonPaymentPossible($paymentId)
    {
        if (empty($paymentId)) {
            /** @var string $paymentId */
            $paymentId = Registry::getSession()->getVariable('paymentid') !== null
                ? Registry::getSession()->getVariable('paymentid') : '';
        }
        return (
            Registry::getSession()->getVariable('sShipSet') &&
            Constants::isAmazonPayment($paymentId)
        );
    }

    /**
     * Template getter getAmazonPaymentId
     *
     * @return string
     */
    public function getAmazonPaymentId()
    {
        return Constants::PAYMENT_ID;
    }

    public function getAmazonExpressPaymentId()
    {
        return Constants::PAYMENT_ID_EXPRESS;
    }

    public function isAmazonPaymentId($paymentId)
    {
        return Constants::isAmazonPayment($paymentId);
    }


    /**
     * @param string $oxid
     * @return bool
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function isAmazonExclude($oxid = '')
    {
        return $this->getAmazonConfig()->isAmazonExcluded($oxid);
    }


    public function getPaymentDescriptor()

    {
        $amazonSession = OxidServiceProvider::getAmazonService()->getCheckoutSession();
        return $amazonSession['response']['paymentPreferences'][0]['paymentDescriptor'];
    }

    /**
     * Template variable getter. Get payload in JSON Format
     *
     * @return string
     * @throws Exception
     */
    public function getPayloadExpress()
    {
        /** @var string $anid */
        $anid = Registry::getRequest()->getRequestParameter('anid') !== null
            ? Registry::getRequest()->getRequestParameter('anid') : '';
        $payload = new Payload();
        $payload->setCheckoutReviewReturnUrl($anid);
        $payload->setCheckoutResultReturnUrlExpress();
        $payload->setStoreId();
        $payload->addScopes([
            "name",
            "email",
            "phoneNumber",
            "billingAddress"
        ]);

        $amazonConfig = $this->getAmazonConfig();
        $payload->setAddressRestrictions($amazonConfig->getPossibleEUAddresses());
        $payload->setPlatformId($amazonConfig->getPlatformId());

        $payloadData = $payload->getData();
        /** @var string $payloadJSON */
        $payloadJSON = json_encode($payloadData, JSON_UNESCAPED_UNICODE);
        $payloadJSON = $payloadJSON ?: '';
        $this->signature = $this->getSignature($payloadJSON);
        return $payloadJSON;
    }

    /**
     * Template variable getter. Get payload in JSON Format
     *
     * @return string
     * @throws Exception
     */
    public function getPayload()
    {
        $amazonConfig = $this->getAmazonConfig();

        /** @var User $user */
        $user = $this->getUser();
        $payload = new Payload();
        $payload->setCheckoutResultReturnUrl();
        $payload->setStoreId();
        $payload->addScopes([
            "name",
            "email",
            "phoneNumber",
            "billingAddress"
        ]);
        $paymentIntent = 'Authorize';
        $canHandlePendingAuth = true;
        if (OxidServiceProvider::getAmazonClient()->getModuleConfig()->isOneStepCapture()) {
            $paymentIntent = 'AuthorizeWithCapture';
            $canHandlePendingAuth = false;
        }
        $payload->setPaymentIntent($paymentIntent);
        $payload->setCanHandlePendingAuthorization($canHandlePendingAuth);
        $delAddress = OxidServiceProvider::getDeliveryAddressService();
        $address = $delAddress->getTempDeliveryAddressAddress();
        if ($address->getId()) {
            $payload->setAddressDetailsFromDeliveryAddress($address);
        } else {
        $payload->setAddressDetails($user);
        }

        $payload->setPlatformId($amazonConfig->getPlatformId());

        $payload->setCurrencyCode($amazonConfig->getPresentmentCurrency());
        $session = Registry::getSession();
        $basket = $session->getBasket();
        $payload->setPaymentDetailsChargeAmount(PhpHelper::getMoneyValue($basket->getPrice()->getBruttoPrice()));

        $activeShop = Registry::getConfig()->getActiveShop();
        /** @var string $oxcompany */
        $oxcompany = $activeShop->getFieldData('oxcompany');
        /** @var string $oxordersubject */
        $oxordersubject = $activeShop->getFieldData('oxordersubject');
        $payload->setMerchantStoreName($oxcompany);
        $payload->setNoteToBuyer($oxordersubject);
        $payloadData = $payload->getData();
        /** @var string $payloadJSON */
        $payloadJSON = json_encode($payloadData, JSON_UNESCAPED_UNICODE);
        $payloadJSON = $payloadJSON ?: '';
        $this->signature = $this->getSignature($payloadJSON);
        return $payloadJSON;
    }

    /**
     * Template variable getter. Get payload in JSON Format for Sign In
     *
     * @return string
     * @throws Exception
     */
    public function getPayloadSignIn()
    {
        $payload = new Payload();
        $payload->setSignInReturnUrl();
        $payload->setSignInCancelUrl();
        $payload->setStoreId();
        $payload->addSignInScopes([
            "name",
            "email",
            "postalCode",
            "shippingAddress",
            "billingAddress",
            "phoneNumber"
        ]);

        $amazonConfig = $this->getAmazonConfig();
        $payload->setPlatformId($amazonConfig->getPlatformId());

        $payloadData = $payload->getData();
        /** @var string $payloadJSON */
        $payloadJSON = json_encode($payloadData, JSON_UNESCAPED_UNICODE);
        $payloadJSON = $payloadJSON ?: '';
        $this->signature = $this->getSignature($payloadJSON);
        return $payloadJSON;
    }

    /**
     * Template variable getter. Get Signature for Payload
     *
     * @param string $payload
     * @return string
     * @throws Exception
     */
    public function getSignature($payload)
    {
        try {
            return OxidServiceProvider::getAmazonClient()->generateButtonSignature($payload);
        } catch (Exception $exception) {
            $logger = new Logger();
            $logger->log('ERROR', $exception->getMessage(), [$exception]);
            return '';
        }
    }
}
