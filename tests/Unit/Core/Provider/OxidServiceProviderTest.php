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

namespace OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core\Provider;

use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonClient;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonService;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidEsales\TestingLibrary\UnitTestCase;

class OxidServiceProviderTest extends UnitTestCase
{
    protected function setUp()
    {
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(OxidServiceProvider::class, OxidServiceProvider::getInstance());
    }

    public function testGetAmazonClient()
    {
        $this->assertInstanceOf(AmazonClient::class, OxidServiceProvider::getAmazonClient());
    }

    public function testGetAmazonService()
    {
        $this->assertInstanceOf(AmazonService::class, OxidServiceProvider::getAmazonService());
    }

    public function testGetOxidUser()
    {
        $this->assertInstanceOf(User::class, OxidServiceProvider::getOxidUser());
    }

    public function testGetLogger()
    {
        $this->assertInstanceOf(Logger::class, OxidServiceProvider::getLogger());
    }
}
