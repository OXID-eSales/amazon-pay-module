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
use OxidEsales\Eshop\Core\Model\ListModel;

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
        'AT', 'BE', 'BG',
        'HR', 'CY', 'CZ',
        'DK', 'EE', 'FI',
        'FR', 'DE', 'GR',
        'HU', 'IE', 'IT',
        'LV', 'LT', 'LU',
        'MT', 'NL', 'PL',
        'RO', 'SK', 'SI',
        'ES', 'SE'
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

        foreach ($oxidCountryList as $oxidCountry) {
            $oxidCountryIsoCode = $oxidCountry->oxcountry__oxisoalpha2->value;
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
        return Registry::getConfig()->getShopSecureHomeURL() . 'index.php?cl=amazondispatch&action=ipn';
    }

    /**
     * Shop's create checkout controller. Used in amazon buttons.
     *
     * @return string
     */
    public function getCreateCheckoutUrl(): string
    {
        return Registry::getConfig()->getShopSecureHomeURL() . 'index.php?cl=amazoncheckout&fnc=createCheckout';
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
        return html_entity_decode(Registry::getConfig()->getShopHomeUrl() . 'cl=amazondispatch&action=review');
    }

    /**
     * Confirmation page after checkout is finished
     *
     * @return string
     */
    public function checkoutResultUrl(): string
    {
        return html_entity_decode(Registry::getConfig()->getShopHomeUrl() . 'cl=amazondispatch&action=result');
    }

    /**
     * Return country list
     *
     * @return oxcountrylist
     */
    public function getCountryList(): ListModel
    {
        if ($this->countryList === null) {
            // passing country list
            $this->countryList = oxNew(\OxidEsales\Eshop\Application\Model\CountryList::class);
            $this->countryList->loadActiveCountries();
        }

        return $this->countryList;
    }
}
