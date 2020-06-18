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

namespace OxidProfessionalServices\AmazonPay\Tests\Unit\Core\Provider;

use OxidEsales\Eshop\Application\Model\User;
use OxidProfessionalServices\AmazonPay\Core\AmazonClient;
use OxidProfessionalServices\AmazonPay\Core\AmazonService;
use OxidProfessionalServices\AmazonPay\Core\Logger;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidEsales\TestingLibrary\UnitTestCase;

class OxidServiceProviderTest extends UnitTestCase
{
    /** @var OxidServiceProvider */
    private $oxidServiceProvider;

    protected function setUp()
    {
        $this->oxidServiceProvider = OxidServiceProvider::getInstance();
    }

    public function testGetInstance(): void
    {
        $this->assertInstanceOf(OxidServiceProvider::class, OxidServiceProvider::getInstance());
    }

    public function testGetAmazonClient(): void
    {
        $this->assertInstanceOf(AmazonClient::class, OxidServiceProvider::getAmazonClient());
    }

    public function testGetAmazonService(): void
    {
        $this->assertInstanceOf(AmazonService::class, OxidServiceProvider::getAmazonService());
    }

    public function testGetOxidUser(): void
    {
        $this->assertInstanceOf(User::class, OxidServiceProvider::getOxidUser());
    }

    public function testGetLogger(): void
    {
        $this->assertInstanceOf(Logger::class, OxidServiceProvider::getLogger());
    }
}
