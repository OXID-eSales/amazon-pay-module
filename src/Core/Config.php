<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use Exception;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\DeliverySetList;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\LanguageNotFoundException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\CountryList;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use stdClass;

/**
 * Class Config
 */
class Config
{
    /**
     * all languages supported by Amazonpay
     *
     * @var array
     */
    protected $amazonLanguages = [
        'en' => 'en_GB',
        'de' => 'de_DE',
        'fr' => 'fr_FR',
        'it' => 'it_IT',
        'es' => 'es_ES'
    ];

    /**
     * Amazonpay default language
     *
     * @var string
     */
    protected $amazonDefaultLanguage = 'de';

    /**
     * all currencies supported by Amazonpay
     * @link https://amazonpaycheckoutintegrationguide.s3.amazonaws.com/amazon-pay-checkout/multi-currency-integration.html
     *
     * @var array
     */
    protected $amazonCurrencies = [
        'AUD',
        'GBP',
        'DKK',
        'EUR',
        'HKD',
        'JPY',
        'NZD',
        'NOK',
        'ZAR',
        'SEK',
        'CHF',
        'USD'
    ];

    /**
     * Amazonpay Ledger currency
     * @link https://developer.amazon.com/de/docs/amazon-pay-checkout/multi-currency-integration.html
     *
     * @var string
     */
    protected $amazonLedgerCurrency = 'EUR';

    /**
     * returns Country.
     *
     * @var array|null
     */
    protected $countryList = null;

    /**
     * is AmazonPayExpress as Paymentmethod active
     *
     * @var bool|null
     */
    protected $bIsAmazonExpressActive = null;

    /**
     * Checks if module configuration is valid
     * @throws StandardException
     */
    public function checkHealth()
    {
        if (
            !$this->getPrivateKey() ||
            !$this->getPublicKeyId() ||
            !$this->getMerchantId() ||
            !$this->getStoreId() ||
            !$this->getPresentmentCurrency()
        ) {
            throw new StandardException('OSC_AMAZONPAY_ERR_CONF_INVALID');
        }
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->getBoolConfigValue('blAmazonPaySandboxMode');
    }

    public function setSandbox($value): void
    {
        $this->saveModuleSetting('blAmazonPaySandboxMode', $value);
    }

    public function getAmazonPayLogging(): bool
    {
        return $this->getBoolConfigValue('blAmazonPayLogging');
    }

    public function setAmazonPayLogging($value): void
    {
        $this->saveModuleSetting('blAmazonPayLogging', $value);
    }

    /**
     * @return bool
     */
    public function isOneStepCapture(): bool
    {
        $amazonPayCapType = $this->getStringConfigValue('amazonPayCapType');
        return $amazonPayCapType !== '2';
    }

    /**
     * @return bool
     */
    public function isTwoStepCapture(): bool
    {
        $amazonPayCapType = $this->getStringConfigValue('amazonPayCapType');
        return  $amazonPayCapType === '2';
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->getStringConfigValue('sAmazonPayPrivKey');
    }

    public function setPrivateKey($value): void
    {
        $this->saveModuleSetting('sAmazonPayPrivKey', $value);
    }

    /**
     * @return string
     */
    public function getFakePrivateKey(): string
    {
        return str_repeat('*', 10);
    }

    /**
     * @return string
     */
    public function getPublicKeyId(): string
    {
        return $this->getStringConfigValue('sAmazonPayPubKeyId');
    }

    public function setPublicKeyId($value): void
    {
        $this->saveModuleSetting('sAmazonPayPubKeyId', $value);
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->getStringConfigValue('sAmazonPayMerchantId');
    }

    public function setMerchantId($value): void
    {
        $this->saveModuleSetting('sAmazonPayMerchantId', $value);
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->getStringConfigValue('sAmazonPayStoreId');
    }

    public function setStoreId($value): void
    {
        $this->saveModuleSetting('sAmazonPayStoreId', $value);
    }


    /**
     * @return string
     * @throws LanguageNotFoundException
     */
    public function getCheckoutLanguage(): string
    {
        $lang = Registry::getLang();
        $langAbbr = $lang->getLanguageAbbr();
        return $this->amazonLanguages[$langAbbr] ?? $this->amazonLanguages[$this->amazonDefaultLanguage];
    }

    /**
     * @return string
     */
    public function getPresentmentCurrency(): string
    {
        $currencyAbbr = '';

        $shopCurrency = Registry::getConfig()->getActShopCurrencyObject();

        if (in_array($shopCurrency->name, $this->amazonCurrencies, true)) {
            $currencyAbbr = $shopCurrency->name;
        }
        return $currencyAbbr;
    }

    /**
     * @return string
     */
    public function getLedgerCurrency(): string
    {
        return $this->amazonLedgerCurrency;
    }

    /**
     * @return array
     */
    public function getPossiblePresentmentCurrenciesAbbr(): array
    {
        $result = [];
        $shopCurrencies = Registry::getConfig()->getCurrencyArray();
        foreach ($shopCurrencies as $shopCurrency) {
            $currencyAbbr = $shopCurrency->name;
            if (in_array($currencyAbbr, $this->amazonCurrencies, true)) {
                $result[] = $currencyAbbr;
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getPossibleAddresses(): array
    {
        return array_fill_keys($this->getCountryList(), new stdClass());
    }

    /**
     * @return string
     */
    public function getPaymentRegion(): string
    {
        return 'eu'; //todo also add in tpl
    }

    /**
     * @return string
     */
    public function getIPNUrl(): string
    {
        $config = Registry::getConfig();
        return html_entity_decode(sprintf(
            '%sindex.php?cl=amazondispatch&action=ipn&shp=%s',
            $config->getCurrentShopUrl(false),
            $config->getShopId()
        ));
    }

    /**
     * @return bool
     */
    public function useExclusion(): bool
    {
        return $this->getBoolConfigValue('blAmazonPayUseExclusion');
    }

    public function setUseExclusion($value): void
    {
        $this->saveModuleSetting('blAmazonPayUseExclusion', $value);
    }

    /**
     * @return bool
     */
    public function socialLoginDeactivated(): bool
    {
        return $this->getBoolConfigValue('blAmazonSocialLoginDeactivated');
    }

    /**
     * @return bool
     */
    public function automatedRefundActivated(): bool
    {
        return $this->getBoolConfigValue('blAmazonAutomatedRefundActivated');
    }

    /**
     * @return bool
     */
    public function automatedCancelActivated(): bool
    {
        return $this->getBoolConfigValue('blAmazonAutomatedCancelActivated');
    }

    /**
     * @return bool
     */
    public function amazonpayLogging(): bool
    {
        return $this->getBoolConfigValue('blAmazonPayLogging');
    }

    /**
     * @param bool $bIsAdmin
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function displayExpressInPDP(bool $bIsAdmin = false): bool
    {
        return $this->displayExpressButton('blAmazonPayExpressPDP', $bIsAdmin);
    }

    public function setDisplayExpressInPDP($value): void
    {
        $this->saveModuleSetting('blAmazonPayExpressPDP', $value);
    }

    /**
     * @param bool $bIsAdmin
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function displayExpressInMiniCartAndModal(bool $bIsAdmin = false): bool
    {
        return $this->displayExpressButton('blAmazonPayExpressMinicartAndModal', $bIsAdmin);
    }

    public function setDisplayExpressInMiniCartAndModal($value): void
    {
        $this->saveModuleSetting('blAmazonPayExpressMinicartAndModal', $value);
    }

    /**
     * @param string $sVarName
     * @param bool $bIsAdmin
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function displayExpressButton(string $sVarName, bool $bIsAdmin = false): bool
    {
        $bShowButton = $this->getBoolConfigValue($sVarName);
        if (is_null($this->bIsAmazonExpressActive)) {
            $this->bIsAmazonExpressActive = false;
            $oPayment = oxNew(Payment::class);
            $oPayment->load(Constants::PAYMENT_ID_EXPRESS);
            $this->bIsAmazonExpressActive = $oPayment->isLoaded() &&
                $oPayment->getFieldData('oxactive');
        }
        return $bShowButton && ($this->bIsAmazonExpressActive || $bIsAdmin);
    }

    /**
     * Review page before confirming checkout
     *
     * @return string
     */
    public function checkoutReviewUrl(): string
    {

        return html_entity_decode(
            Registry::getConfig()->getCurrentShopUrl(false)
            . 'index.php?cl=amazondispatch&action=review&stoken='
            . Registry::getSession()->getSessionChallengeToken()
        );
    }

    /**
     * Confirmation page after checkout is finished
     *
     * @return string
     */
    public function checkoutResultUrl(): string
    {
        return html_entity_decode(
            Registry::getConfig()->getCurrentShopUrl(false)
            . 'index.php?cl=amazondispatch&action=result&stoken='
            . Registry::getSession()->getSessionChallengeToken()
        );
    }

    /**
     * @return string
     */
    public function signInReturnUrl(): string
    {
        return html_entity_decode(
            Registry::getConfig()->getCurrentShopUrl(false)
            . 'index.php?cl=amazondispatch&action=signin&stoken='
            . Registry::getSession()->getSessionChallengeToken()
        );
    }

    /**
     * @return string
     */
    public function signInCancelUrl(): string
    {
        return html_entity_decode(
            Registry::getConfig()->getCurrentShopUrl(false)
            . 'index.php?cl=user&stoken='
            . Registry::getSession()->getSessionChallengeToken()
        );
    }

    /**
     * Return country list
     *
     * @return array
     */
    public function getCountryList(): array
    {
        $user = oxNew(User::class);
        $user->loadActiveUser();
        $activeUser = $user;

        if ($this->countryList === null) {
            $this->countryList = [];
            $payment = oxNew(Payment::class);
            /** TODO should be variable for any amazonpay ID */
            $payment->load(Constants::PAYMENT_ID_EXPRESS);
            $allowedCountries = $payment->getCountries();
            // fallback if countries are not restricted by Paymentmethod ...
            if (!$allowedCountries) {
                $allowedCountries = [];
                $countries = oxNew(CountryList::class);
                $countries->loadActiveCountries();
                foreach ($countries as $allowedCountry) {
                    $allowedCountries[] = $allowedCountry->getId();
                }
            }
            foreach ($allowedCountries as $countryOxId) {
                // check deliverysets
                $deliverySetList = oxNew(DeliverySetList::class);
                $deliverySetData = $deliverySetList->getDeliverySetList($activeUser, $countryOxId);
                if (count($deliverySetData)) {
                    $oxidCountry = oxNew(Country::class);
                    $oxidCountry->load($countryOxId);
                    /** @var string $oxisoalpha2 */
                    $oxisoalpha2 = $oxidCountry->getFieldData('oxisoalpha2');
                    $this->countryList[$countryOxId] = $oxisoalpha2;
                }
            }
        }
        return $this->countryList;
    }

    /**
     * create a unique Id
     *
     * @return string
     */
    public function getUuid(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * PlatformId to identify the Module-Integration
     *
     * @return string
     */
    public function getPlatformId(): string
    {
        return Constants::PLATTFORM_ID;
    }

    /**
     * @param string $oxid
     * @return bool
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function isAmazonExcluded(string $oxid): bool
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
            str_pad(
                "",
                count($productIds) * 2,
                '?,'
            ),
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

    private function getStringConfigValue(string $key): string
    {
        $moduleSettingBridge = $this->getModuleSettingsBridge();

        /** @var string $result */
        $result = $moduleSettingBridge->get($key, AmazonPayModule::MODULE_ID);
        return $result;
    }

    private function getBoolConfigValue(string $key): bool
    {
        $moduleSettingBridge = $this->getModuleSettingsBridge();

        /** @var bool $result */
        $result = $moduleSettingBridge->get($key, AmazonPayModule::MODULE_ID);
        return $result;
    }

    private function saveModuleSetting(string $key, $value): void
    {
        $moduleSettingBridge = $this->getModuleSettingsBridge();
        $moduleSettingBridge->save($key, $value, AmazonPayModule::MODULE_ID);
    }

    private function getModuleSettingsBridge(): ModuleSettingBridgeInterface
    {
        return ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettingBridgeInterface::class);
    }
}
