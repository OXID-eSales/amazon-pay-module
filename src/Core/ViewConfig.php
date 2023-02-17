<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
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
    protected ?bool $isFlowCompatibleTheme = null;

    /**
     * is this a "Wave"-Theme Compatible Theme?
     * @var null|boolean $isWaveCompatibleTheme
     */
    protected ?bool $isWaveCompatibleTheme = null;

    /**
     * articlesId for the checkout review url
     */
    protected string $articlesId = '';

    public string $signature = '';

    /**
     * @return Config
     */
    public function getAmazonConfig()
    {
        return Registry::get(Config::class);
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
        } catch (StandardException $e) {
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
    public function useExclusion(): bool
    {
        return $this->getAmazonConfig()->useExclusion();
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
        if ($paymentId !== '') {
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
        return OxidServiceProvider::getAmazonService()->getMaximalRefundAmount($orderId);
    }

    /**
     * @param string $oxid
     * @return bool
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function isAmazonExclude(string $oxid = ''): bool
    {
        if (!$this->useExclusion()) {
            return false;
        }

        $session = Registry::getSession();

        $basket = $session->getBasket();

        $productIds = [];

        foreach ($basket->getContents() as $product) {
            $productIds[] = $product->getProductId();
        }

        if ($oxid !== '') {
            $productIds[] = $oxid;
        }

        $productIds = array_unique($productIds);

        if (count(array_filter($productIds)) < 1) {
            return false;
        }

        // generates the string "?,?,?,?," for an array with count() = 4 and strips the trailing comma
        $questionMarks = trim(
            str_pad("", count($productIds) * 2, '?,'),
            ','
        );
        $sql = "SELECT oa.OSC_AMAZON_EXCLUDE as excludeArticle,
               oc.OSC_AMAZON_EXCLUDE as excludeCategory
          FROM oxarticles oa
          JOIN oxobject2category o2c
            ON (o2c.OXOBJECTID = oa.OXID)
          JOIN oxcategories oc
            ON (oc.OXID = o2c.OXCATNID)
         WHERE oa.OXID in (" . $questionMarks . ")";

        $results = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql, $productIds);

        foreach ($results as $result) {
            if ($result['excludeArticle'] === '1' || $result['excludeCategory'] === '1') {
                OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
                return true;
            }
        }

        return false;
    }

    /**
     * Template variable getter. Check if is a Flow Theme Compatible Theme
     *
     * @return boolean
     */
    public function isFlowCompatibleTheme(): bool
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
    public function isWaveCompatibleTheme(): bool
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
    public function isThemeBasedOn(string $themeId)
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

    public function setArticlesId(string $articlesId): void
    {
        $this->articlesId = $articlesId;
    }

    public function getPaymentDescriptor(): string
    {
        $amazonSession = OxidServiceProvider::getAmazonService()->getCheckoutSession();
        $paymentDescriptor = $amazonSession['response']['paymentPreferences'][0]['paymentDescriptor'];
        return $paymentDescriptor;
    }

    /**
     * Template variable getter. Get payload in JSON Format
     *
     * @return false|string
     * @throws \Exception
     */
    public function getPayloadExpress(): false|string
    {
        /** @var string $anid */
        $anid = Registry::getRequest()->getRequestParameter('anid');
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
        $this->signature = $this->getSignature($payloadJSON);
        return $payloadJSON;
    }

    /**
     * Template variable getter. Get payload in JSON Format
     *
     * @return false|string
     * @throws \Exception
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
        $payload->setPaymentIntent('AuthorizeWithCapture');
        $payload->setAddressDetails($user);

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
        $this->signature = $this->getSignature($payloadJSON);
        return $payloadJSON;
    }

    /**
     * Template variable getter. Get payload in JSON Format for Sign In
     *
     * @return false|string
     * @throws \Exception
     */
    public function getPayloadSignIn(): false|string
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
        $this->signature = $this->getSignature($payloadJSON);
        return $payloadJSON;
    }

    /**
     * Template variable getter. Get Signature for Payload
     *
     * @param string $payload
     * @return string
     * @throws \Exception
     */
    public function getSignature($payload): string
    {
        $amazonClient = OxidServiceProvider::getAmazonClient();
        $signature = $amazonClient->generateButtonSignature($payload);
        return $signature;
    }
}
