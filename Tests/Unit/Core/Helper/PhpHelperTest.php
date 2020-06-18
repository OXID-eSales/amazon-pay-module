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

use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
use OxidEsales\TestingLibrary\UnitTestCase;

class PhpHelperTest extends UnitTestCase
{
    public function testJsonToArray(): void
    {
        $json = json_encode([
            'a' => 1,
            'b' => '1',
            'c' => [1, 2, 'c' => 'd']
        ]);

        $array = PhpHelper::jsonToArray($json);

        $this->assertEquals($array['a'], 1);
        $this->assertEquals($array['b'], '1');
        $this->assertEquals($array['c'][0], 1);
        $this->assertEquals($array['c'][1], 2);
        $this->assertEquals($array['c']['c'], 'd');
    }

    public function testGetArrayValue(): void
    {
        $testArray = [
            'a' => 'b',
            'b' => [
                'c' => 'd',
                'e' => ['f' => 6]
            ]
        ];

        $result = PhpHelper::getArrayValue('f', $testArray);
        $this->assertEquals(6, $result);

        $result = PhpHelper::getArrayValue('c', $testArray);
        $this->assertEquals('d', $result);

        $result = PhpHelper::getArrayValue('a', $testArray);
        $this->assertEquals('b', $result);
    }

    public function dataProviderMoneyValue(): array
    {
        return [
            [12, 12.00],
            [13.354, 13.35],
            [13.356, 13.36],
            [.3, 0.3],
            [0.01, 0.01],
        ];
    }

    /**
     * @dataProvider dataProviderMoneyValue
     * @param $testValue
     * @param $expectedResult
     */
    public function testGetMoneyValue($testValue, $expectedResult): void
    {
        $this->assertEquals($expectedResult, PhpHelper::getMoneyValue($testValue));
    }
}
