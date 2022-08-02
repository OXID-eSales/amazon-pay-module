<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core\Helper;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Country;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
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
     * This is used as a prefilter for OXID functions below.
     * @param $addr
     * @return array
     */
    public static function parseAddress(array $address): array
    {
        $name = trim($address['name']);
        $last_name = self::getLastName($name);
        $first_name = self::getFirstName($name);

        // Country
        $countryIsoCode = $address["countryCode"];
        $country = oxNew(Country::class);
        $countryOxId = $country->getIdByCode($countryIsoCode ?? '');
        $country->loadInLang(
            (int)Registry::getLang()->getBaseLanguage(),
            $countryOxId
        );
        $countryName = $country->oxcountry__oxtitle->value;

        $company = '';
        $street = '';
        $streetNo = '';
        $additionalInfo = '';

        $addressData = null;

        $addressLines = self::getAddressLines($address);

        if ($countryIsoCode === 'DE' || $countryIsoCode === 'AT') {
            // special Amazon-Case: Street in first line, StreetNo in second line
            if (isset($addressLines[1]) && preg_match('/^\d.{0,8}$/', $addressLines[1])) {
                $streetTmp = $addressLines[0] . ' ' . $addressLines[1];
            // Company-Case: Company in first line Street and StreetNo in second line
            } elseif (isset($addressLines[1]) && $addressLines[1] != '') {
                $streetTmp = $addressLines[1];
                $company = $addressLines[0];
            // Normal-Case: No Company, Street & StreetNo in first line
            } else {
                $streetTmp = $addressLines[0];
            }
            if ($addressLines[2] != '') {
                $additionalInfo = $addressLines[2];
            }

            try {
                $addressData = AddressSplitter::splitAddress($streetTmp);
                $street = $addressData['streetName'] ?? '';
                $streetNo = $addressData['houseNumber'] ?? '';
            } catch (SplittingException $e) {
                // The Address could not be split
                // we have an exception, bit we did not log the message because of sensible Address-Informations
                // $logger = new Logger();
                // $logger->error($e->getMessage(), ['status' => $e->getCode()]);
                $street = $streetTmp;
            }
        } else {
            try {
                $addressLinesAsString = implode(', ', $addressLines);
                $addressData = AddressSplitter::splitAddress($addressLinesAsString);

                $company = $addressData['additionToAddress1'] ?? '';
                $street = $addressData['streetName'] ?? '';
                $streetNo = $addressData['houseNumber'] ?? '';
                $additionalInfo = $addressData['additionToAddress2'] ?? '';
            } catch (SplittingException $e) {
                // The Address could not be split
                // we have an exception, bit we did not log the message because of sensible Address-Informations
                // $logger = new Logger();
                // $logger->error($e->getMessage(), ['status' => $e->getCode()]);
                $street = $addressLinesAsString;
            }
        }

        return [
            'Firstname' => $first_name,
            'Lastname' => $last_name,
            'CountryIso' => $countryIsoCode,
            'CountryId' => $countryOxId,
            'Country' => $countryName,
            'Street' => $street,
            'StreetNo' => $streetNo,
            'AddInfo' => $additionalInfo,
            'Company' => $company,
            'PostalCode' => $address['postalCode'],
            'City' => $address['city'],
            'PhoneNumber' => $address['phoneNumber']
        ];
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

        return [
            $DBTablePrefix . 'oxcompany' => $parsedAddress['Company'],
            $DBTablePrefix . 'oxfname' => $parsedAddress['Firstname'],
            $DBTablePrefix . 'oxlname' => $parsedAddress['Lastname'],
            $DBTablePrefix . 'oxstreet' => $parsedAddress['Street'],
            $DBTablePrefix . 'oxstreetnr' => $parsedAddress['StreetNo'],
            $DBTablePrefix . 'oxcity' => $parsedAddress['City'],
            $DBTablePrefix . 'oxcountryid' => $parsedAddress['CountryId'],
            $DBTablePrefix . 'oxcountry' => $parsedAddress['Country'],
            $DBTablePrefix . 'oxzip' => $parsedAddress['PostalCode'],
            $DBTablePrefix . 'oxfon' => $parsedAddress['PhoneNumber'],
            $DBTablePrefix . 'oxaddinfo' => $parsedAddress['AddInfo']
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
        $config = Registry::get(Config::class);

        $DBTablePrefix = self::validateDBTablePrefix($DBTablePrefix);

        $parsedAddress = self::parseAddress($address);

        return [
            'oxcompany' => $parsedAddress['Company'],
            'oxfname' => $parsedAddress['Firstname'],
            'oxlname' => $parsedAddress['Lastname'],
            'oxstreet' => $parsedAddress['Street'],
            'oxstreetnr' => $parsedAddress['StreetNo'],
            'oxcity' => $parsedAddress['City'],
            'oxcountryid' => $parsedAddress['CountryId'],
            'oxcountry' => $parsedAddress['Country'],
            'oxstateid' => $address['stateOrRegion'],
            'oxzip' => $parsedAddress['PostalCode'],
            'oxfon' => $parsedAddress['PhoneNumber'],
            'oxaddinfo' => $parsedAddress['AddInfo'],
            'oxfax' => '',
            'oxsal' => ''
        ];
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
     * Firstname of a Name
     *
     */
    private static function getFirstName(string $name): string
    {
        return implode(' ', array_slice(explode(' ', $name), 0, -1));
    }

    /**
     * Lastname of a Name
     *
     */
    private static function getLastName(string $name): string
    {
        return array_slice(explode(' ', $name), -1)[0];
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
