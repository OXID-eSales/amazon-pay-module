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

use OxidEsales\EshopCommunity\Application\Model\Country;
use OxidProfessionalServices\AmazonPay\Core\Logger;
use VIISON\AddressSplitter\AddressSplitter;
use VIISON\AddressSplitter\Exceptions\SplittingException;

class Address
{
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
    public static function mapAddressToDb(array $address): array
    {
        $parsedAddress = self::parseAddress($address);
        $addressLines = self::getAddressLines($address);
        $addressData = AddressSplitter::splitAddress(implode(',', $addressLines));
        $country = oxNew(Country::class);
        $countryCode = $country->getIdByCode($address['countryCode'] ?? '');

        $streetNr = $addressData['houseNumber'];

        $finalAddress = [
            'oxcompany' => $parsedAddress['Company'],
            'oxuser__oxfname' =>
                $parsedAddress['Firstname'] == "" ? $parsedAddress['Lastname'] : $parsedAddress['Firstname'],
            'oxuser__oxlname' => $parsedAddress['Lastname'],
            'oxuser__oxstreet' => $addressData['streetName'],
            'oxuser__oxcity' => $parsedAddress['City'],
            'oxuser__oxstreetnr' => $streetNr,
            'oxuser__oxcountryid' => $countryCode,
            'oxuser__oxzip' => $parsedAddress['PostalCode'],
            'oxaddress__oxfname' =>
                $parsedAddress['Firstname'] == "" ? $parsedAddress['Lastname'] : $parsedAddress['Firstname'],
            'oxaddress__oxlname' => $parsedAddress['Lastname'],
            'oxaddress__oxstreet' => $addressData['streetName'],
            'oxaddress__oxcity' => $parsedAddress['City'],
            'oxaddress__oxstreetnr' => $streetNr,
            'oxaddress__oxcountryid' => $countryCode,
            'oxaddress__oxzip' => $parsedAddress['PostalCode'],
        ];

        return $finalAddress;
    }

    /**
     * Maps Amazon address fields to oxid fields
     *
     * @param array $address
     *
     * @return array
     */
    public static function mapAddressToView(array $address): array
    {
        $parsedAddress = self::parseAddress($address);
        $addressLines = self::getAddressLines($address);

        try {
            $addressData = AddressSplitter::splitAddress(implode(',', $addressLines));
        } catch (SplittingException $e) {
            $logger = new Logger();
            $logger->error($e->getMessage(), ['status' => $e->getCode()]);
        }

        $country = oxNew(Country::class);
        $countryCode = $country->getIdByCode($address['countryCode'] ?? '');
        $streetNr = $addressData['houseNumber'];

        return [
            'oxcompany' => $parsedAddress['Company'],
            'oxfname' => $parsedAddress['Firstname'],
            'oxlname' => $parsedAddress['Lastname'],
            'oxstreet' => $addressData['streetName'],
            'oxcity' => $parsedAddress['City'],
            'oxstreetnr' => $streetNr,
            'oxaddinfo' => '',
            'oxcountryid' => $countryCode,
            'oxstateid' => $address['stateOrRegion'],
            'oxzip' => $parsedAddress['PostalCode'],
            'oxfon' => $address['phoneNumber'] ?? '',
            'oxfax' => '',
            'oxsal' => '',
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
}
