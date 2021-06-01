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

namespace OxidProfessionalServices\AmazonPay\Core\Helper;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\RequiredAddressFields;
use OxidProfessionalServices\AmazonPay\Core\Logger;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;
use VIISON\AddressSplitter\AddressSplitter;
use VIISON\AddressSplitter\Exceptions\SplittingException;

class Address
{
     /**
     * possible DBTable Prefix
     *
     * @var array
     */
    protected static $possibleDBTablePrefix = [
        'oxuser__' , 'oxaddress__'
    ];

     /**
     * possible DBTable Prefix
     *
     * @var string
     */
    protected static $defaultDBTablePrefix = 'oxaddress__';

    /**
     * Copied verbatim from AmazonPay Demo v2
     * because amazon expects this exact result
     * This is used as a prefilter for OXID functions below.
     * @param $addr
     * @return array
     */
    public static function parseAddress(array $addr): array
    {
        $name = trim($addr['name']);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#' . $last_name . '#', '', $name));

        $country = $addr["countryCode"];
        $addressLine1 = trim($addr["addressLine1"]);
        $addressLine2 = trim($addr["addressLine2"]);
        $addressLine3 = trim($addr["addressLine3"]);
        $street = "";
        $company = "";
        if ($country == 'DE' || $country == 'AT') {
            if ($addressLine2 != '') {
                //if line 2 starts with number
                if (preg_match('/\d+/m', $addressLine2)) {
                    $street = $addressLine1 . ' ' . $addressLine2;
                } else {
                    $street = $addressLine2;
                    $company = $addressLine1;
                }
            } elseif ($addressLine1 != '') {
                $street = $addressLine1;
            } else {
                //invalid address
                // Handled by address splitter
            }
            if ($addressLine3 != '') {
                $company = $company . ', ' . $addressLine3;
            }
        } else {
            if ($addressLine1 != "") {
                $street = $addressLine1;
                $company = "";
                if ($addressLine2 != "") {
                    $company = $addressLine2;
                }
                if ($addressLine3 != "") {
                    $company = $company . ', ' . $addressLine3;
                }
            } elseif ($addressLine2 != '') {
                $street = $addressLine2;
                if ($addressLine3 != '') {
                    $company = $addressLine3;
                }
            }
        }

        return array(
            'Firstname' => $first_name,
            'Lastname' => $last_name,
            'Country' => $country,
            'Street' => $street,
            'Company' => $company,
            'PostalCode' => $addr['postalCode'],
            'City' => $addr['city'],
            'PhoneNumber' => $addr['phoneNumber']
        );
    }

    /**
     * @param array $address
     * @return array
     */
    public static function collectMissingRequiredBillingFields(array $address): array
    {
        $oRequiredAddressFields = oxNew(RequiredAddressFields::class);
        $aRequiredBillingFields = $oRequiredAddressFields->getBillingFields();

        $missingFields = [];

        foreach ($aRequiredBillingFields as $billingKey) {
            if (
                (
                    isset($address[$billingKey]) &&
                    !$address[$billingKey]
                ) ||
                !isset($address[$billingKey])
            ) {
                // we collect the missing fields and filled as dummy with the Amazon-SessionID
                $missingFields[$billingKey] = OxidServiceProvider::getAmazonService()->getCheckoutSessionId();
            }
        }

        return $missingFields;
    }

    /**
     * @param array $address
     * @return array
     */
    public static function collectMissingRequiredDeliveryFields(array $address): array
    {
        $oRequiredAddressFields = oxNew(RequiredAddressFields::class);
        $aRequiredDeliveryFields = $oRequiredAddressFields->getDeliveryFields();

        $missingFields = [];

        foreach ($aRequiredDeliveryFields as $deliveryKey) {
            if (
                (
                    isset($address[$deliveryKey]) &&
                    !$address[$deliveryKey]
                ) ||
                !isset($address[$deliveryKey])
            ) {
                // we collect the missing fields and filled as dummy with the Amazon-SessionID
                $missingFields[$deliveryKey] = OxidServiceProvider::getAmazonService()->getCheckoutSessionId();
            }
        }
        return $missingFields;
    }

    /**
     * @param array $address
     * @param string $DBTablePrefix
     * @return array
     */
    public static function mapAddressToDb(array $address, $DBTablePrefix): array
    {
        $DBTablePrefix = self::validateDBTablePrefix($DBTablePrefix);
        $parsedAddress = self::parseAddress($address);
        $addressLines = self::getAddressLines($address);

        try {
            $addressData = AddressSplitter::splitAddress(implode(',', $addressLines));
        } catch (SplittingException $e) {
            $logger = new Logger();
            $logger->error($e->getMessage(), ['status' => $e->getCode()]);
        }

        $country = oxNew(Country::class);
        $countryOxId = $country->getIdByCode($address['countryCode'] ?? '');
        $country->loadInLang(
            Registry::getLang()->getBaseLanguage(),
            $countryOxId
        );
        $countryName = $country->oxcountry__oxtitle->value;



        $streetNr = $addressData['houseNumber'] ?? '';

        return [
            $DBTablePrefix . 'oxcompany' => $parsedAddress['Company'],
            $DBTablePrefix . 'oxfname' =>
                $parsedAddress['Firstname'] == "" ? $parsedAddress['Lastname'] : $parsedAddress['Firstname'],
            $DBTablePrefix . 'oxlname' => $parsedAddress['Lastname'],
            $DBTablePrefix . 'oxstreet' => $addressData['streetName'],
            $DBTablePrefix . 'oxcity' => $parsedAddress['City'],
            $DBTablePrefix . 'oxstreetnr' => $streetNr,
            $DBTablePrefix . 'oxcountryid' => $countryOxId,
            $DBTablePrefix . 'oxcountry' => $countryName,
            $DBTablePrefix . 'oxzip' => $parsedAddress['PostalCode'],
            $DBTablePrefix . 'oxfon' => $address['phoneNumber'] ?? ''
        ];
    }

    /**
     * Maps Amazon address fields to oxid fields
     *
     * @param array $address
     * @param string $DBTablePrefix
     *
     * @return array
     */
    public static function mapAddressToView(array $address, $DBTablePrefix): array
    {
        $DBTablePrefix = self::validateDBTablePrefix($DBTablePrefix);
        $parsedAddress = self::parseAddress($address);
        $addressLines = self::getAddressLines($address);

        try {
            $addressData = AddressSplitter::splitAddress(implode(',', $addressLines));
        } catch (SplittingException $e) {
            $logger = new Logger();
            $logger->error($e->getMessage(), ['status' => $e->getCode()]);
        }

        $country = oxNew(Country::class);
        $countryOxId = $country->getIdByCode($address['countryCode'] ?? '');
        $country->loadInLang(
            Registry::getLang()->getBaseLanguage(),
            $countryOxId
        );
        $countryName = $country->oxcountry__oxtitle->value;
        $streetNr = $addressData['houseNumber'] ?? '';

        $result = [
            'oxcompany' => $parsedAddress['Company'],
            'oxfname' => $parsedAddress['Firstname'],
            'oxlname' => $parsedAddress['Lastname'],
            'oxstreet' => $addressData['streetName'],
            'oxstreetnr' => $streetNr,
            'oxcity' => $parsedAddress['City'],
            'oxcountryid' => $countryOxId,
            'oxcountry' => $countryName,
            'oxstateid' => $address['stateOrRegion'],
            'oxzip' => $parsedAddress['PostalCode'],
            'oxfon' => $address['phoneNumber'] ?? '',
            'oxaddinfo' => '',
            'oxfax' => '',
            'oxsal' => ''
        ];

        $oRequiredAddressFields = oxNew(RequiredAddressFields::class);

        $aRequiredFields = $DBTablePrefix == 'oxuser__' ?
            $oRequiredAddressFields->getBillingFields() :
            $oRequiredAddressFields->getDeliveryFields();

        foreach ($aRequiredFields as $key) {
            $key = str_replace($DBTablePrefix, '', $key);
            if (
                (
                    isset($result[$key]) &&
                    !$result[$key]
                ) ||
                !isset($result[$key])
            ) {
                $result[$key] = OxidServiceProvider::getAmazonService()->getCheckoutSessionId();
            }
        }
        return $result;
    }

    /**
     * Returns filled address lines from the address array
     *
     * @param array $address
     *
     * @return array
     */
    private static function getAddressLines(array $address): array
    {
        $lines = [];
        for ($i = 1; $i <= 3; $i++) {
            if (isset($address["addressLine$i"]) && $address["addressLine$i"]) {
                $line = $address["addressLine$i"];
                preg_match_all('!\d+!', $line, $matches2);
                if (!empty($matches2[0])) {
                    $line = str_replace(implode(' ', $matches2[0]), implode(',', $matches2[0]), $line);
                }
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * validate the DBTablePrefix
     *
     * @param string $DBTablePrefix
     *
     * @return string
     */
    private static function validateDBTablePrefix($DBTablePrefix)
    {
        return in_array($DBTablePrefix, self::$possibleDBTablePrefix) ?
            $DBTablePrefix :
            self::$defaultDBTablePrefix;
    }
}
