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

namespace OxidProfessionalServices\AmazonPay\Tests\Unit\Core\Helper;

use OxidProfessionalServices\AmazonPay\Core\Helper\Address;
use OxidEsales\TestingLibrary\UnitTestCase;

class AddressTest extends UnitTestCase
{
    public function amazonDefaultAddressProvider(): array
    {
        $address = $this->getAddressArray();

        return [
            [$address, 'Firstname', 'Some'],
            [$address, 'Lastname', 'Name'],
            [$address, 'Country', 'DE'],
            [$address, 'Street', 'Some street'],
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
    public function testParseAddress(array $address, $key, $value): void
    {
        $result = Address::parseAddress($address);
        $this->assertSame($result[$key], $value);
    }

    public function amazonDbMapAddressProvider(): array
    {
        $address = $this->getAddressArray();

        return [
            [$address, 'oxcompany', 'Some street 521, Some city'],
            [$address, 'oxuser__oxfname', 'Some'],
            [$address, 'oxuser__oxlname', 'Name'],
            [$address, 'oxuser__oxstreet', 'Some street'],
            [$address, 'oxuser__oxcity', 'Freiburg'],
            [$address, 'oxuser__oxstreetnr', '521'],
            [$address, 'oxuser__oxcountryid', 'a7c40f631fc920687.20179984'],
            [$address, 'oxuser__oxzip', '12345'],
            [$address, 'oxaddress__oxfname', 'Some'],
            [$address, 'oxaddress__oxlname', 'Name'],
            [$address, 'oxaddress__oxstreet', 'Some street'],
            [$address, 'oxaddress__oxcity', 'Freiburg'],
            [$address, 'oxaddress__oxstreetnr', '521'],
            [$address, 'oxaddress__oxcountryid', 'a7c40f631fc920687.20179984'],
            [$address, 'oxaddress__oxzip', '12345'],
        ];
    }

    /**
     * @dataProvider amazonDbMapAddressProvider
     * @covers \OxidProfessionalServices\AmazonPay\Core\Helper\Address::getAddressLines
     * @param array $address
     * @param string $key
     * @param string $value
     */
    public function testMapAddressToDb(array $address, $key, $value): void
    {
        $result = Address::mapAddressToDb($address);
        $this->assertSame($result[$key], $value);
    }

    public function amazonViewMapAddressProvider(): array
    {
        $address = $this->getAddressArray();

        return [
            [$address, 'oxcompany', 'Some street 521, Some city'],
            [$address, 'oxfname', 'Some'],
            [$address, 'oxlname', 'Name'],
            [$address, 'oxstreet', 'Some street'],
            [$address, 'oxcity', 'Freiburg'],
            [$address, 'oxstreetnr', '521'],
            [$address, 'oxaddinfo', ''],
            [$address, 'oxcountryid', 'a7c40f631fc920687.20179984'],
            [$address, 'oxzip', '12345'],
            [$address, 'oxstateid', 'BW'],
            [$address, 'oxfon', '+44989383728'],
            [$address, 'oxfax', ''],
            [$address, 'oxsal', ''],
        ];
    }

    /**
     * @dataProvider  amazonViewMapAddressProvider
     * @covers        \OxidProfessionalServices\AmazonPay\Core\Helper\Address::getAddressLines
     * @param array $address
     * @param string $key
     * @param string $value
     */
    public function testMapAddressToView(array $address, $key, $value): void
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
        $address['addressLine1'] = 'Some street 521';
        $address['addressLine2'] = 'Some street';
        $address['addressLine3'] = 'Some city';
        $address['postalCode'] = '12345';
        $address['city'] = 'Freiburg';
        $address['phoneNumber'] = '+44989383728';
        $address['company'] = 'Some street 521, Some city';
        $address['stateOrRegion'] = 'BW';

        return $address;
    }
}
