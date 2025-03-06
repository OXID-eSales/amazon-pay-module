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

namespace OxidSolutionCatalysts\AmazonPay\Tests\Integration\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\AmazonPay\Controller\Admin\ConfigController;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ConfigControllerTest extends UnitTestCase
{
    public function testRender()
    {
        $c = new ConfigController();
        $this->assertSame('amazonpay/amazonconfig.tpl', $c->render());
    }

    public function configValueProvider()
    {
        return [
            [
                ['blAmazonPaySandboxMode' => 'sandbox'],
                true, 'getterMethod' => 'isSandbox'],
            [
                ['blAmazonPaySandboxMode' => 'prod'],
                false, 'getterMethod' => 'isSandbox'],
            [
                ['blAmazonPaySandboxMode' => 'undefined'],
                false,
                'getterMethod' => 'isSandbox'
            ],
            [
                ['sAmazonPayPrivKey' => 'key'],
                'key',
                'getterMethod' => 'getPrivateKey'
            ],
            [
                ['sAmazonPayPubKeyId' => 'key id '],
                'key id',
                'getterMethod' => 'getPublicKeyId'
            ],
            [
                ['sAmazonPayMerchantId' => 'merchant id'],
                'merchant id',
                'getterMethod' => 'getMerchantId'
            ],
            [
                ['sAmazonPayStoreId' => 'store id'],
                'store id',
                'getterMethod' => 'getStoreId'
            ],
            [
                ['blAmazonPayUseExclusion' => 'on'],
                true,
                'getterMethod' => 'useExclusion'
            ],
            [
                ['blAmazonPayUseExclusion' => 1],
                true,
                'getterMethod' => 'useExclusion'
            ],
            [
                ['blAmazonPayUseExclusion' => ''],
                false,
                'getterMethod' => 'useExclusion'
            ],
            [
                ['blAmazonPayUseExclusion' => null],
                false,
                'getterMethod' => 'useExclusion'
            ],
        ];
    }

    /**
     * @dataProvider configValueProvider
     * @covers \OxidSolutionCatalysts\AmazonPay\Controller\Admin\ConfigController::handleSpecialFields
     *
     * @param $conf array Configuration values
     * @param $expected mixed Expected return value
     * @param $getterMethod string Getter method in config object
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSave($conf, $expected, $getterMethod)
    {
        $config = new Config();
        $configController = new ConfigController();
        $this->setRequestParameter('conf', $conf);
        $configController->save();
        $this->assertSame($expected, $config->$getterMethod());
    }

    public function testDisplayExpressInPDP()
    {
        // Mock Payment class
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['load', 'isLoaded', 'getFieldData'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo('oscpayamazonpayexpress'))
            ->willReturn(true);

        // 1. Test: Option enabled, payment method active, frontend context
        $paymentMock->expects($this->any())
            ->method('isLoaded')
            ->willReturn(true);
        $paymentMock->expects($this->any())
            ->method('getFieldData')
            ->with($this->equalTo('oxactive'))
            ->willReturn(true);

        $this->setConfigParam('blAmazonPayExpressPDP', true);

        // Override Oxid Registry to return our mock object
        Registry::set(Payment::class, $paymentMock);

        $config = new Config();
        // Without bIsAdmin parameter (= false)
        $this->assertTrue($config->displayExpressInPDP());

        // 2. Test: Option enabled, payment method inactive, frontend context
        $newPaymentMock = clone $paymentMock;
        $newPaymentMock->expects($this->any())
            ->method('getFieldData')
            ->with($this->equalTo('oxactive'))
            ->willReturn(false);
        Registry::set(Payment::class, $newPaymentMock);

        $config = new Config();
        $this->assertFalse($config->displayExpressInPDP());

        // 3. Test: Option enabled, payment method inactive, admin context
        $this->assertTrue($config->displayExpressInPDP(true));

        // 4. Test: Option disabled (regardless of other factors)
        $this->setConfigParam('blAmazonPayExpressPDP', false);
        $this->assertFalse($config->displayExpressInPDP());
        $this->assertFalse($config->displayExpressInPDP(true));
    }

    public function testDisplayExpressInMiniCartAndModal()
    {
        // Mock Payment class
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['load', 'isLoaded', 'getFieldData'])
            ->getMock();
        $paymentMock->expects($this->any())
            ->method('load')
            ->with($this->equalTo('oscpayamazonpayexpress'))
            ->willReturn(true);

        // 1. Test: Option enabled, payment method active, frontend context
        $paymentMock->expects($this->any())
            ->method('isLoaded')
            ->willReturn(true);
        $paymentMock->expects($this->any())
            ->method('getFieldData')
            ->with($this->equalTo('oxactive'))
            ->willReturn(true);

        $this->setConfigParam('blAmazonPayExpressMinicartAndModal', true);

        // Override Oxid Registry to return our mock object
        Registry::set(Payment::class, $paymentMock);

        $config = new Config();
        // Without bIsAdmin parameter (= false)
        $this->assertTrue($config->displayExpressInMiniCartAndModal());

        // 2. Test: Option enabled, payment method inactive, frontend context
        $newPaymentMock = clone $paymentMock;
        $newPaymentMock->expects($this->any())
            ->method('getFieldData')
            ->with($this->equalTo('oxactive'))
            ->willReturn(false);
        Registry::set(Payment::class, $newPaymentMock);

        $config = new Config();
        $this->assertFalse($config->displayExpressInMiniCartAndModal());

        // 3. Test: Option enabled, payment method inactive, admin context
        $this->assertTrue($config->displayExpressInMiniCartAndModal(true));

        // 4. Test: Option disabled (regardless of other factors)
        $this->setConfigParam('blAmazonPayExpressMinicartAndModal', false);
        $this->assertFalse($config->displayExpressInMiniCartAndModal());
        $this->assertFalse($config->displayExpressInMiniCartAndModal(true));
    }
}
