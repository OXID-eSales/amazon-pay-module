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

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core\Helper;

use OxidSolutionCatalysts\AmazonPay\Core\Helper\Address;
use OxidEsales\TestingLibrary\UnitTestCase;

class AddressTest extends \OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core\AmazonTestCase
{
    public function amazonDefaultAddressProvider(): array
    {
        $address = $this->getAddressArray();

        return [
            [$address, 'Firstname', 'Some'],
            [$address, 'Lastname', 'Name'],
            [$address, 'CountryIso', 'DE'],
            [$address, 'CountryId', 'testcountry_de'],
            [$address, 'Country', 'Deutschland'],
            [$address, 'Street', 'Bayreuther Straße'],
            [$address, 'StreetNo', '108'],
            [$address, 'Company', 'Wiesent center'],
            [$address, 'PostalCode', '12345'],
            [$address, 'City', 'Freiburg'],
            [$address, 'PhoneNumber', '+44989383728'],
        ];
    }

    /**
     * @dataProvider amazonDefaultAddressProvider
     * @param array $address
     * @param string $key
     * @param string $value
     */
    public function testParseAddress(array $address, string $key, string $value)
    {
        $result = Address::parseAddress($address);
        $this->assertSame($result[$key], $value);
    }

    public function amazonDbMapBillingAddressProvider(): array
    {
        $address = $this->getAddressArray();

        return [
            [$address, 'oxuser__oxcompany', 'Wiesent center'],
            [$address, 'oxuser__oxfname', 'Some'],
            [$address, 'oxuser__oxlname', 'Name'],
            [$address, 'oxuser__oxstreet', 'Bayreuther Straße'],
            [$address, 'oxuser__oxcity', 'Freiburg'],
            [$address, 'oxuser__oxstreetnr', '108'],
            [$address, 'oxuser__oxcountryid', 'testcountry_de'],
            [$address, 'oxuser__oxzip', '12345'],
        ];
    }

    /**
     * @dataProvider amazonDbMapBillingAddressProvider
     * @covers \OxidSolutionCatalysts\AmazonPay\Core\Helper\Address::getAddressLines
     * @param array $address
     * @param string $key
     * @param string $value
     */
    public function testMapBillingAddressToDb(array $address, string $key, string $value)
    {
        $result = Address::mapAddressToDb($address, 'oxuser__');
        $this->assertSame($result[$key], $value);
    }

    public function amazonDbMapShippingAddressProvider(): array
    {
        $address = $this->getAddressArray();

        return [
            [$address, 'oxaddress__oxcompany', 'Wiesent center'],
            [$address, 'oxaddress__oxfname', 'Some'],
            [$address, 'oxaddress__oxlname', 'Name'],
            [$address, 'oxaddress__oxstreet', 'Bayreuther Straße'],
            [$address, 'oxaddress__oxcity', 'Freiburg'],
            [$address, 'oxaddress__oxstreetnr', '108'],
            [$address, 'oxaddress__oxcountryid', 'testcountry_de'],
            [$address, 'oxaddress__oxzip', '12345'],
        ];
    }

    /**
     * @dataProvider amazonDbMapShippingAddressProvider
     * @covers \OxidSolutionCatalysts\AmazonPay\Core\Helper\Address::getAddressLines
     * @param array $address
     * @param string $key
     * @param string $value
     */
    public function testMapShippingAddressToDb(array $address, string $key, string $value)
    {
        $result = Address::mapAddressToDb($address, 'oxaddress__');
        $this->assertSame($result[$key], $value);
    }

    public function amazonViewMapAddressProvider(): array
    {
        $address = $this->getAddressArray();

        return [
            [$address, 'oxcompany', 'Wiesent center'],
            [$address, 'oxfname', 'Some'],
            [$address, 'oxlname', 'Name'],
            [$address, 'oxstreet', 'Bayreuther Straße'],
            [$address, 'oxcity', 'Freiburg'],
            [$address, 'oxstreetnr', '108'],
            [$address, 'oxcountryid', 'testcountry_de'],
            [$address, 'oxzip', '12345'],
            [$address, 'oxstateid', 'BW'],
            [$address, 'oxfon', '+44989383728'],
            [$address, 'oxaddinfo', '2. Stock'],
            [$address, 'oxfax', ''],
            [$address, 'oxsal', ''],
        ];
    }

    /**
     * @dataProvider  amazonViewMapAddressProvider
     * @covers        \OxidSolutionCatalysts\AmazonPay\Core\Helper\Address::getAddressLines
     * @param array $address
     * @param string $key
     * @param string $value
     */
    public function testMapAddressToView(array $address, string $key, string $value)
    {
        $result = Address::mapAddressToView($address);
        $this->assertSame($result[$key], $value);
    }

    /**
     * @return array
     */
    protected function getAddressArray(): array
    {
        $address = [];
        $address['name'] = 'Some Name';
        $address['countryCode'] = 'DE';
        $address['addressLine1'] = 'Wiesent center';
        $address['addressLine2'] = 'Bayreuther Straße 108';
        $address['addressLine3'] = '2. Stock';
        $address['postalCode'] = '12345';
        $address['city'] = 'Freiburg';
        $address['phoneNumber'] = '+44989383728';
        $address['stateOrRegion'] = 'BW';

        return $address;
    }
}
