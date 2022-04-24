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

namespace OxidProfessionalServices\AmazonPay\Core;

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
    * Amazonpay default currency
    *
    * @var string
    */
    protected $amazonDefaultCurrency = 'EUR';

    /**
    * all allowed Amazonpay EU Addresses
    * @link https://amazonpaycheckoutintegrationguide.s3.amazonaws.com/amazon-pay-checkout/address-restriction-samples.html#allow-eu-addresses-only
    * @var array
    */
    protected $amazonEUAddresses = [
        'BE', 'BG', 'DK', 'DE', 'EE',
        'FI', 'FR', 'GR', 'IE', 'IT',
        'HR', 'LV', 'LT', 'LU', 'MT',
        'NL', 'AT', 'PL', 'PT', 'RO',
        'SM', 'SE', 'SK', 'SI', 'ES',
        'CZ', 'HU', 'GB', 'VA', 'FO',
        'CY', 'AL', 'AD', 'BA', 'LI',
        'MC', 'IS', 'YU', 'MK', 'MD',
        'NO', 'CH', 'UA', 'BY', 'RU',
        'TR', 'RS'
    ];

    /**
    * Amazonpay default EU Address
    *
    * @var string
    */
    protected $amazonDefaultEUAddresses = 'DE';

    /**
     * returns Country.
     *
     * @var OXID CountryList
     */
    protected $countryList = null;


    /**
     * Checks if module configurations are valid
     *
     * @throws StandardException
     */
    public function checkHealth(): void
    {
        if (
            !$this->getPrivateKey() ||
            !$this->getPublicKeyId() ||
            !$this->getMerchantId() ||
            !$this->getStoreId()
        ) {
            throw oxNew(StandardException::class);
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
        return Registry::getConfig()->getConfigParam('sAmazonPayPrivKey');
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
        return Registry::getConfig()->getConfigParam('sAmazonPayPubKeyId');
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return Registry::getConfig()->getConfigParam('sAmazonPayMerchantId');
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return Registry::getConfig()->getConfigParam('sAmazonPayStoreId');
    }

    /**
     * @return string
     */
    public function getCheckoutLanguage(): string
    {
        $lang = Registry::getLang();
        $langAbbr = $lang->getLanguageAbbr();
        if (isset($this->amazonLanguages[$langAbbr])) {
            return $this->amazonLanguages[$langAbbr];
        } else {
            return $this->amazonLanguages[$this->amazonDefaultLanguage];
        }
    }

    /**
     * @return string
     */
    public function getPresentmentCurrency(): string
    {
        $shopCurrency = Registry::getConfig()->getActShopCurrencyObject();
        $currencyAbbr = $shopCurrency->name;

        if (in_array($currencyAbbr, $this->amazonCurrencies)) {
            return $currencyAbbr;
        } else {
            return $this->amazonDefaultCurrency;
        }
    }

    /**
     * @return string
     */
    public function getLedgerCurrency(): string
    {
        return $this->amazonDefaultCurrency;
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

    /**
     * @return array
     */
    public function getPossibleEUAddresses(): array
    {
        $result = [];
        $oxidCountryList = $this->getCountryList();

        foreach ($oxidCountryList as $oxidCountryIsoCode) {
            if (in_array($oxidCountryIsoCode, $this->amazonEUAddresses)) {
                $result[$oxidCountryIsoCode] = (object) null;
            }
        }
        if (count($result) == 0) {
            $result[$this->amazonDefaultEUAddresses] = (object) null;
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
        return html_entity_decode(
            Registry::getConfig()->getCurrentShopUrl(false) . 'index.php?cl=amazondispatch&action=ipn'
        );
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
    public function displayInPDP(): bool
    {
        return (bool)Registry::getConfig()->getConfigParam('blAmazonPayPDP');
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
    public function displayInMiniCart(): bool
    {
        return (bool)Registry::getConfig()->getConfigParam('blAmazonPayMinicartAndModal');
    }

    /**
     * Review page before confirming checkout
     *
     * @return string
     */
    public function checkoutReviewUrl(): string
    {
        return html_entity_decode(
            Registry::getConfig()->getCurrentShopUrl(false) . 'index.php?cl=amazondispatch&action=review'
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
            Registry::getConfig()->getCurrentShopUrl(false) . 'index.php?cl=amazondispatch&action=result'
        );
    }

    /**
     * Return country list
     *
     * @return oxcountrylist
     */
    public function getCountryList(): array
    {
        $activeUser = false;
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        if ($user->loadActiveUser()) {
            $activeUser = $user;
        }

        if ($this->countryList === null) {
            $this->countryList = [];
            $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
            $payment->load('oxidamazon');
            foreach ($payment->getCountries() as $countryOxId) {
                // check deliverysets
                $deliverySetList = oxNew(\OxidEsales\Eshop\Application\Model\DeliverySetList::class);
                $deliverySetData = $deliverySetList->getDeliverySetList($activeUser, $countryOxId);
                if (count($deliverySetData)) {
                    $oxidCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
                    $oxidCountry->load($countryOxId);
                    $this->countryList[$countryOxId] = $oxidCountry->oxcountry__oxisoalpha2->value;
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
            $uuid = md5(uniqid('', true) . '|' . microtime()) . substr(md5(mt_rand()), 0, 24);
        }
        return $uuid;
    }

    /**
     * This Placeholder is public. The only function is to fill this string
     * in the empty required fields during checkout. After checkout the fields would be cleaned from
     * the database
     *
     * @return string
     */
    public function getPlaceholder(): string
    {
        return Constants::CHECKOUT_PLACEHOLDER;
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
     * @return string
     */
    public function getPayType(): string
    {
        return Registry::getConfig()->getConfigParam('amazonPayType');
    }

}
