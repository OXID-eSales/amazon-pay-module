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
     * is this a "Flow"-Theme Compatible Theme?
     * @var null|boolean $isFlowCompatibleTheme
     */
    protected $isFlowCompatibleTheme = null;

    /**
     * is this a "Wave"-Theme Compatible Theme?
     * @var null|boolean $isWaveCompatibleTheme
     */
    protected $isWaveCompatibleTheme = null;

    /**
     * articlesId for the checkout review url
     */
    protected $articlesId = '';

    public $signature = '';

    /**
     * @return Config
     */
    public function getAmazonConfig(): Config
    {
        $config = Registry::get(Config::class);
        return $config;
    }

    /**
     * @return bool
     */
    public function isAmazonActive(): bool
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
    public function displayExpressInPDP(): bool
    {
        return $this->getAmazonConfig()->displayExpressInPDP();
    }

    /**
     * @return bool
     */
    public function socialLoginDeactivated(): bool
    {
        return $this->getAmazonConfig()->socialLoginDeactivated();
    }

    /**
     * @return bool
     */
    public function displayExpressInMiniCartAndModal(): bool
    {
        return $this->getAmazonConfig()->displayExpressInMiniCartAndModal();
    }

    /**
     * @return string
     */
    public function getAmazonSessionId(): string
    {
        return OxidServiceProvider::getAmazonService()->getCheckoutSessionId();
    }

    /**
     * @return bool
     */
    public function isAmazonSessionActive(): bool
    {
        return OxidServiceProvider::getAmazonService()->isAmazonSessionActive();
    }

    /**
     * Get webhook controller url
     *
     * @return string
     */
    public function getCancelAmazonPaymentUrl(): string
    {
        return $this->getSelfLink() . 'cl=amazoncheckout&fnc=cancelAmazonPayment';
    }

    /**
     * Template getter isAmazonPaymentPossible
     *
     * @param string $paymentId
     * @return boolean
     */
    public function isAmazonPaymentPossible(string $paymentId = ''): bool
    {
        if ($paymentId === '') {
            /** @var string $paymentId */
            $paymentId = Registry::getSession()->getVariable('paymentid') ?? '';
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
    public function getAmazonPaymentId(): string
    {
        return Constants::PAYMENT_ID;
    }

    public function getAmazonExpressPaymentId(): string
    {
        return Constants::PAYMENT_ID_EXPRESS;
    }

    public function isAmazonPaymentId(string $paymentId): bool
    {
        return Constants::isAmazonPayment($paymentId);
    }

    public function getMaximalRefundAmount(string $orderId): float
    {
        return PhpHelper::getMoneyValue(OxidServiceProvider::getAmazonService()->getMaximalRefundAmount($orderId));
    }

    /**
     * @param string $oxid
     * @return bool
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function isAmazonExclude(string $oxid = ''): bool
    {
        return $this->getAmazonConfig()->isAmazonExcluded($oxid);
    }

    /**
     * Template variable getter. Check if is a Flow Theme Compatible Theme
     *
     * @return boolean
     */
    public function isFlowCompatibleTheme()
    {
        if (is_null($this->isFlowCompatibleTheme)) {
            $this->isFlowCompatibleTheme = $this->isThemeBasedOn('flow');
        }
        return $this->isFlowCompatibleTheme;
    }

    /**
     * Template variable getter. Check if is a Wave Theme Compatible Theme
     *
     * @return boolean
     */
    public function isWaveCompatibleTheme()
    {
        if (is_null($this->isWaveCompatibleTheme)) {
            $this->isWaveCompatibleTheme = $this->isThemeBasedOn('wave');
        }
        return $this->isWaveCompatibleTheme;
    }

    /**
     * Template variable getter. Check if is a ??? Theme Compatible Theme
     *
     * @param string $themeId
     *
     * @psalm-param 'flow'|'wave' $themeId
     * @return boolean
     *
     * @psalm-suppress InternalMethod
     *
     */
    public function isThemeBasedOn(string $themeId): bool
    {
        $result = false;

        $theme = oxNew(Theme::class);
        $theme->load($theme->getActiveThemeId());
        // check active theme or parent theme
        if (
            $theme->getActiveThemeId() == $themeId ||
            $theme->getInfo('parentTheme') == $themeId
        ) {
            $result = true;
        }

        return $result;
    }

    public function setArticlesId(string $articlesId)
    {
        $this->articlesId = $articlesId;
    }

    public function getPaymentDescriptor(): string
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
    public function getPayloadExpress(): string
    {
        /** @var string $anid */
        $anid = Registry::getRequest()->getRequestParameter('anid') ?? '';
        $this->setArticlesId($anid);
        $payload = new Payload();
        $payload->setCheckoutReviewReturnUrl($this->articlesId);
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
    public function getPayload(): string
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
        $payload->setPaymentIntent('AuthorizeWithCapture');

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
    public function getPayloadSignIn(): string
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
    public function getSignature(string $payload): string
    {
        return $this->getCachedSignature($payload);
    }

    private function getCachedSignature(string $payload): string
    {
        // TODO: add configuration to make whole functionality optional

        $cacheName = 'amzn_signature_cache.json';
        $session = Registry::getSession();
        $config = Registry::getConfig();
        $token = '';

        // personal url means, the payload contain an URL with a "stoken" parameter.
        // These are only cacheable within the session of the user, and the cache
        // will be reset upon user login/logout.
        $isPersonalUrl = false;
        if ((strstr($payload, '&stoken='))) {
            if (preg_match('/stoken=([A-Z0-9]+)/', $payload, $matches) && isset($matches[1])) {
                $token = $matches[1];
                if (!empty($token)) {
                    $isPersonalUrl = true;
                }
            }
        }
        // empty cache
        $cache = [
            'meta' => ['token' => $token,],
            'cache' => [],
        ];

        // path to global cache file
        $compileDir = $config->getConfigParam('sCompileDir');
        $signatureCache = Registry::getUtilsFile()->normalizeDir($compileDir) . $cacheName;

        // load either global (tmp file) or local cache (session)
        if ($isPersonalUrl) {
            $cacheContent = $session->getVariable($cacheName);

        } else {
            $cacheContent = file_get_contents($signatureCache);
        }

        if ($cacheContent) {
            $cache = json_decode($cacheContent, true);
            // reset cache on token change, eg. the user changed the login status
            if ($cache['meta']['token'] != $token) {
                $cache['cache'] = [];
            }
        }
        // TODO: use better method than md5
        $cacheKey = md5($payload);
        if (!isset($cache['cache'][$cacheKey])) {
            // no cache key found, we must create a new signature and cache it
            // TODO: writing the global cache must be protected against race condition
            $signature = $this->createSignature($payload);
            $cache['cache'][$cacheKey] = $signature;
            if ($isPersonalUrl) {
                $session->setVariable($cacheName, json_encode($cache));
            } else {
                file_put_contents($signatureCache, json_encode($cache));
            }
        }
        else {
            // found a cached signature.. congrats: we saved 1-2 seconds computing time
            $signature = $cache['cache'][$cacheKey];
        }

        return $signature;
    }

    private function createSignature(string $payload): string
    {
        $amazonClient = OxidServiceProvider::getAmazonClient();
        return  $amazonClient->generateButtonSignature($payload);
    }
}
