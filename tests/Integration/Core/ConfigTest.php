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

class ConfigTest extends \OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core\AmazonTestCase
{
    public function testIsSandbox()
    {
        $config = new Config();
        $config->setSandbox(true);
        $this->assertTrue($config->isSandbox());
        $config->setSandbox(false);
        $this->assertFalse($config->isSandbox());
    }

    public function testGetPrivateKey()
    {
        $config = new Config();
        $this->setConfigParam('sAmazonPayPrivKey', 'someKey1234');
        $this->assertSame('someKey1234', $config->getPrivateKey());
    }

    public function testGetPublicKeyId()
    {
        $config = new Config();
        $this->setConfigParam('sAmazonPayPubKeyId', 'keyid');
        $this->assertSame('keyid', $config->getPublicKeyId());
    }

    public function testGetMerchantId()
    {
        $config = new Config();
        $this->setConfigParam('sAmazonPayMerchantId', 'merchid');
        $this->assertSame('merchid', $config->getMerchantId());
    }

    public function testGetStoreId()
    {
        $config = new Config();
        $config->setStoreId('storeid');
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
        $config->setDisplayExpressInPDP(true);
        $this->assertTrue($config->displayExpressInPDP());
        $config->setDisplayExpressInPDP(false);
        $this->assertFalse($config->displayExpressInPDP());
    }

    public function testUseExclusion()
    {
        $config = new Config();
        $config->setUseExclusion(true);
        $this->assertTrue($config->useExclusion());
        $config->setUseExclusion(false);
        $this->assertFalse($config->useExclusion());
    }

    public function testDisplayExpressInMinicartAndModal()
    {
        $config = new Config();
        $config->setDisplayExpressInMiniCartAndModal(true);
        $this->assertTrue($config->displayExpressInMiniCartAndModal());
        $config->setDisplayExpressInMiniCartAndModal(false);
        $this->assertFalse($config->displayExpressInMiniCartAndModal());
    }

    /**
     * @throws StandardException
     */
    public function testCheckHealthMissingPrivKey()
    {
        $config = new Config();
        $config->setPrivateKey('');
        $config->setPublicKeyId('set');
        $config->setMerchantId('set');
        $config->setStoreId('set');
        $this->expectException(StandardException::class);
        $config->checkHealth();
        $this->expectExceptionMessageMatches('OSC_AMAZONPAY_ERR_CONF_INVALID');
    }

    /**
     * @throws StandardException
     */
    public function testCheckHealthMissingPrivKeyId()
    {
        $config = new Config();
        $config->setPrivateKey('set');
        $config->setPublicKeyId('');
        $config->setMerchantId('set');
        $config->setStoreId('set');
        $this->expectException(StandardException::class);
        $config->checkHealth();
        $this->assertLoggedException(StandardException::class, 'OSC_AMAZONPAY_ERR_CONF_INVALID');
    }

    /**
     * @throws StandardException
     */
    public function testCheckHealthMissingMerchantId()
    {
        $config = new Config();
        $config->setPrivateKey('set');
        $config->setPublicKeyId('set');
        $config->setMerchantId('');
        $config->setStoreId('set');
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
        $config = new Config();
        $config->setPrivateKey('set');
        $config->setPublicKeyId('set');
        $config->setMerchantId('set');
        $config->setStoreId('');
        $this->expectException(StandardException::class);
        $config->checkHealth();
        $this->assertLoggedException(StandardException::class, 'OSC_AMAZONPAY_ERR_CONF_INVALID');
    }

    /**
     * @throws StandardException
     */
    public function testCheckHealthOK()
    {
        $config = new Config();
        $config->setPrivateKey('set');
        $config->setPublicKeyId('set');
        $config->setMerchantId('set');
        $config->setStoreId('set');
        $config->checkHealth();
        $this->assertTrue(true);
    }
}
