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

namespace OxidSolutionCatalysts\AmazonPay\Tests\Integration\Core;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidSolutionCatalysts\AmazonPay\Core\Config;

/**
 *
 */
class ConfigTest extends \OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core\AmazonTestCase
{
    protected function setUp(): void
    {
//        $this->markTestSkipped('All tests in this class are skipped.');
        parent::setUp();
    }

    public function testIsSandbox()
    {
        $config = new Config();
        $this->setSettingsParamBool('blAmazonPaySandboxMode', true);
        $this->assertTrue($config->isSandbox());
        $this->setSettingsParamBool('blAmazonPaySandboxMode', false);
        $this->assertFalse($config->isSandbox());
    }

    public function testGetPrivateKey()
    {
        $config = new Config();
        $this->setSettingsParamStr('sAmazonPayPrivKey', 'someKey1234');
        $this->assertSame('someKey1234', $config->getPrivateKey());
    }

    public function testGetPublicKeyId()
    {
        $config = new Config();
        $this->setSettingsParamStr('sAmazonPayPubKeyId', 'keyid');
        $this->assertSame('keyid', $config->getPublicKeyId());
    }

    public function testGetMerchantId()
    {
        $config = new Config();
        $this->setSettingsParamStr('sAmazonPayMerchantId', 'merchid');
        $this->assertSame('merchid', $config->getMerchantId());
    }

    public function testGetStoreId()
    {
        $config = new Config();
        $this->setSettingsParamStr('sAmazonPayStoreId', 'storeid');
        $this->assertSame('storeid', $config->getStoreId());
    }

    public function testGetIPNUrl()
    {
        $config = new Config();
        $this->assertStringContainsString('cl=amazondispatch&action=ipn', $config->getIPNUrl());
    }

    public function testDisplayExpressInPDP()
    {
        $config = new Config();
        $this->setSettingsParamBool('blAmazonPayExpressPDP', true);
        $this->assertTrue($config->displayExpressInPDP());
        $this->setSettingsParamBool('blAmazonPayExpressPDP', false);
        $this->assertFalse($config->displayExpressInPDP());
    }

    public function testUseExclusion()
    {
        $config = new Config();
        $this->setSettingsParamBool('blAmazonPayUseExclusion', true);
        $this->assertTrue($config->useExclusion());
        $this->setSettingsParamBool('blAmazonPayUseExclusion', false);
        $this->assertFalse($config->useExclusion());
    }

    public function testDisplayExpressInMinicartAndModal()
    {
        $config = new Config();
        $this->setSettingsParamBool('blAmazonPayExpressMinicartAndModal', true);
        $this->assertTrue($config->displayExpressInMiniCartAndModal());
        $this->setSettingsParamBool('blAmazonPayExpressMinicartAndModal', false);
        $this->assertFalse($config->displayExpressInMiniCartAndModal());
    }

    /**
     * @throws StandardException
     */
    public function testCheckHealthMissingPrivKey()
    {
        $this->setSettingsParamStr('sAmazonPayPrivKey', '');
        $this->setSettingsParamStr('sAmazonPayPubKeyId', 'set');
        $this->setSettingsParamStr('sAmazonPayMerchantId', 'set');
        $this->setSettingsParamStr('sAmazonPayStoreId', 'set');
        $config = new Config();
        $this->expectException(StandardException::class);
        $config->checkHealth();
        $this->expectExceptionMessageMatches('OSC_AMAZONPAY_ERR_CONF_INVALID');
    }

    /**
     * @throws StandardException
     */
    public function testCheckHealthMissingPrivKeyId()
    {
        $this->setSettingsParamStr('sAmazonPayPrivKey', 'set');
        $this->setSettingsParamStr('sAmazonPayPubKeyId', '');
        $this->setSettingsParamStr('sAmazonPayMerchantId', 'set');
        $this->setSettingsParamStr('sAmazonPayStoreId', 'set');
        $config = new Config();
        $this->expectException(StandardException::class);
        $config->checkHealth();
        $this->assertLoggedException(StandardException::class, 'OSC_AMAZONPAY_ERR_CONF_INVALID');
    }

    /**
     * @throws StandardException
     */
    public function testCheckHealthMissingMerchantId()
    {
        $this->setSettingsParamStr('sAmazonPayPrivKey', 'set');
        $this->setSettingsParamStr('sAmazonPayPubKeyId', 'set');
        $this->setSettingsParamStr('sAmazonPayMerchantId', '');
        $this->setSettingsParamStr('sAmazonPayStoreId', 'set');
        $config = new Config();
        $this->expectException(StandardException::class);
        $config->checkHealth();
        $this->assertLoggedException(StandardException::class, 'OSC_AMAZONPAY_ERR_CONF_INVALID');
    }

    /**
     * @throws StandardException
     */
    public function testCheckHealthMissingStoreId()
    {
        $this->setSettingsParamStr('sAmazonPayPrivKey', 'set');
        $this->setSettingsParamStr('sAmazonPayPubKeyId', 'set');
        $this->setSettingsParamStr('sAmazonPayMerchantId', 'set');
        $this->setSettingsParamStr('sAmazonPayStoreId', '');
        $config = new Config();
        $this->expectException(StandardException::class);
        $config->checkHealth();
        $this->assertLoggedException(StandardException::class, 'OSC_AMAZONPAY_ERR_CONF_INVALID');
    }

    /**
     * @throws StandardException
     */
    public function testCheckHealthOK()
    {
        $this->setSettingsParamStr('sAmazonPayPrivKey', 'set');
        $this->setSettingsParamStr('sAmazonPayPubKeyId', 'set');
        $this->setSettingsParamStr('sAmazonPayMerchantId', 'set');
        $this->setSettingsParamStr('sAmazonPayStoreId', 'set');
        $config = new Config();
        $config->checkHealth();
        $this->assertTrue(true);
    }
}
