<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use OxidEsales\Eshop\Application\Model\DeliverySetList;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

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
    protected array $amazonLanguages = [
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
    protected string $amazonDefaultLanguage = 'de';

    /**
     * all currencies supported by Amazonpay
     * @link https://amazonpaycheckoutintegrationguide.s3.amazonaws.com/amazon-pay-checkout/multi-currency-integration.html
     *
     * @var array
     */
    protected array $amazonCurrencies = [
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
    protected string $amazonLedgerCurrency = 'EUR';

    /**
     * all allowed Amazonpay EU Addresses
     * @link https://amazonpaycheckoutintegrationguide.s3.amazonaws.com/amazon-pay-checkout/address-restriction-samples.html#allow-eu-addresses-only
     * @var array
     */
    protected array $amazonEUAddresses = [
        'AT', 'BE', 'BG', 'HR', 'CY',
        'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT',
        'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SK', 'SI',
        'ES', 'SE'
    ];

    /**
     * returns Country.
     *
     * @var array|null
     */
    protected ?array $countryList = null;


    /**
     * Checks if module configuration is valid
     * @throws StandardException
     */
    public function checkHealth(): void
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
        return (bool)Registry::getConfig()->getConfigParam('blAmazonPaySandboxMode');
    }

    /**
     * @return bool
     */
    public function isOneStepCapture(): bool
    {
        return Registry::getConfig()->getConfigParam('amazonPayCapType') === '1';
    }

    /**
     * @return bool
     */
    public function isTwoStepCapture(): bool
    {
        return Registry::getConfig()->getConfigParam('amazonPayCapType') === '2';
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        /** @var string $sAmazonPayPrivateKey */
        $sAmazonPayPrivateKey = Registry::getConfig()->getConfigParam('sAmazonPayPrivKey');
        return $sAmazonPayPrivateKey;
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
        /** @var string $sAmazonPayPubKeyId */
        $sAmazonPayPubKeyId = Registry::getConfig()->getConfigParam('sAmazonPayPubKeyId');
        return $sAmazonPayPubKeyId;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        /** @var string $sAmazonPayMerchantId */
        $sAmazonPayMerchantId = Registry::getConfig()->getConfigParam('sAmazonPayMerchantId');
        return $sAmazonPayMerchantId;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        /** @var string $sAmazonPayStoreId */
        $sAmazonPayStoreId = Registry::getConfig()->getConfigParam('sAmazonPayStoreId');
        return $sAmazonPayStoreId;
    }

    /**
     * @return string
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

        if (in_array($shopCurrency->name, $this->amazonCurrencies)) {
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
            if (in_array($currencyAbbr, $this->amazonCurrencies)) {
                $result[] = $currencyAbbr;
            }
        }
        return $result;
    }

    public function getPossibleEUAddressesAbbr(): array
    {
        // if there are no specific countries, then all countries are allowed.
        // Then the Countrylist corresponds to the amazonEUAddresses
        return count($this->getCountryList()) ? $this->getCountryList() : $this->amazonEUAddresses;
    }

    /**
     * @return array
     */
    public function getPossibleEUAddresses(): array
    {
        $result = [];
        foreach ($this->getPossibleEUAddressesAbbr() as $isoCode) {
            if (in_array($isoCode, $this->amazonEUAddresses)) {
                $result[$isoCode] = (object)null;
            }
        }
        return $result;
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
     * Shop's create checkout controller. Used in amazon buttons.
     *
     * @return string
     */
    public function getCreateCheckoutUrl(): string
    {
        return html_entity_decode(
            Registry::getConfig()->getCurrentShopUrl(false) . 'index.php?cl=amazoncheckout&fnc=createCheckout'
        );
    }

    /**
     * @return bool
     */
    public function displayExpressInPDP(): bool
    {
        return (bool)Registry::getConfig()->getConfigParam('blAmazonPayExpressPDP');
    }

    /**
     * @return bool
     */
    public function useExclusion(): bool
    {
        return (bool)Registry::getConfig()->getConfigParam('blAmazonPayUseExclusion');
    }

    /**
     * @return bool
     */
    public function socialLoginDeactivated(): bool
    {
        return (bool)Registry::getConfig()->getConfigParam('blAmazonSocialLoginDeactivated');
    }

    /**
     * @return bool
     */
    public function displayExpressInMiniCartAndModal(): bool
    {
        return (bool)Registry::getConfig()->getConfigParam('blAmazonPayExpressMinicartAndModal');
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
            $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
            /** TODO should be variable for any amazonpay ID */
            $payment->load(Constants::PAYMENT_ID_EXPRESS);
            foreach ($payment->getCountries() as $countryOxId) {
                // check deliverysets
                $deliverySetList = oxNew(DeliverySetList::class);
                $deliverySetData = $deliverySetList->getDeliverySetList($activeUser, $countryOxId);
                if (count($deliverySetData)) {
                    $oxidCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
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
        try {
            // throws Exception if it was not possible to gather sufficient entropy.
            $uuid = bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            $uuid = md5(uniqid('', true) . '|' . microtime()) . substr(md5((string)mt_rand()), 0, 24);
        }
        return $uuid;
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
}
