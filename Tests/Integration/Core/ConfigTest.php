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

namespace OxidProfessionalServices\AmazonPay\Tests\Integration\Core;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\AmazonPay\Core\Config;

class ConfigTest extends UnitTestCase
{
    public function testIsSandbox(): void
    {
        $config = new Config();
        $this->setConfigParam('blAmazonPaySandboxMode', true);
        $this->assertTrue($config->isSandbox());
        $this->setConfigParam('blAmazonPaySandboxMode', false);
        $this->assertFalse($config->isSandbox());
    }

    public function testGetPrivateKey(): void
    {
        $config = new Config();
        $this->setConfigParam('sAmazonPayPrivKey', 'someKey1234');
        $this->assertSame('someKey1234', $config->getPrivateKey());
    }

    public function testGetPublicKeyId(): void
    {
        $config = new Config();
        $this->setConfigParam('sAmazonPayPubKeyId', 'keyid');
        $this->assertSame('keyid', $config->getPublicKeyId());
    }

    public function testGetMerchantId(): void
    {
        $config = new Config();
        $this->setConfigParam('sAmazonPayMerchantId', 'merchid');
        $this->assertSame('merchid', $config->getMerchantId());
    }

    public function testGetStoreId(): void
    {
        $config = new Config();
        $this->setConfigParam('sAmazonPayStoreId', 'storeid');
        $this->assertSame('storeid', $config->getStoreId());
    }

    public function testGetIPNUrl(): void
    {
        $config = new Config();
        $this->assertStringContainsString('cl=amazondispatch&action=ipn', $config->getIPNUrl());
    }

    public function testDisplayInPDP(): void
    {
        $config = new Config();
        $this->setConfigParam('blAmazonPayPDP', true);
        $this->assertTrue($config->displayInPDP());
        $this->setConfigParam('blAmazonPayPDP', false);
        $this->assertFalse($config->displayInPDP());
    }

    public function testUseExclusion(): void
    {
        $config = new Config();
        $this->setConfigParam('blAmazonPayUseExclusion', true);
        $this->assertTrue($config->useExclusion());
        $this->setConfigParam('blAmazonPayUseExclusion', false);
        $this->assertFalse($config->useExclusion());
    }

    public function testDisplayInMinicartAndModal(): void
    {
        $config = new Config();
        $this->setConfigParam('blAmazonPayMinicartAndModal', true);
        $this->assertTrue($config->displayInMiniCartAndModal());
        $this->setConfigParam('blAmazonPayMinicartAndModal', false);
        $this->assertFalse($config->displayInMiniCartAndModal());
    }

    public function testCheckHealthMissingPrivKey(): void
    {
        $this->setConfigParam('sAmazonPayPrivKey', '');
        $this->setConfigParam('sAmazonPayPubKeyId', 'set');
        $this->setConfigParam('sAmazonPayMerchantId', 'set');
        $this->setConfigParam('sAmazonPayStoreId', 'set');
        $config = new Config();
        $this->expectException(StandardException::class);
        $config->checkHealth();
    }

    public function testCheckHealthMissingPrivKeyId(): void
    {
        $this->setConfigParam('sAmazonPayPrivKey', 'set');
        $this->setConfigParam('sAmazonPayPubKeyId', '');
        $this->setConfigParam('sAmazonPayMerchantId', 'set');
        $this->setConfigParam('sAmazonPayStoreId', 'set');
        $config = new Config();
        $this->expectException(StandardException::class);
        $config->checkHealth();
    }

    public function testCheckHealthMissingMerchantId(): void
    {
        $this->setConfigParam('sAmazonPayPrivKey', 'set');
        $this->setConfigParam('sAmazonPayPubKeyId', 'set');
        $this->setConfigParam('sAmazonPayMerchantId', '');
        $this->setConfigParam('sAmazonPayStoreId', 'set');
        $config = new Config();
        $this->expectException(StandardException::class);
        $config->checkHealth();
    }

    public function testCheckHealthMissingStoreId(): void
    {
        $this->setConfigParam('sAmazonPayPrivKey', 'set');
        $this->setConfigParam('sAmazonPayPubKeyId', 'set');
        $this->setConfigParam('sAmazonPayMerchantId', 'set');
        $this->setConfigParam('sAmazonPayStoreId', '');
        $config = new Config();
        $this->expectException(StandardException::class);
        $config->checkHealth();
    }

    public function testCheckHealthOK(): void
    {
        $this->setConfigParam('sAmazonPayPrivKey', 'set');
        $this->setConfigParam('sAmazonPayPubKeyId', 'set');
        $this->setConfigParam('sAmazonPayMerchantId', 'set');
        $this->setConfigParam('sAmazonPayStoreId', 'set');
        $config = new Config();
        $config->checkHealth();
        $this->assertTrue(true);
    }
}
