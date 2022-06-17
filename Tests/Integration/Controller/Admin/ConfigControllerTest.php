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

namespace OxidProfessionalServices\AmazonPay\Tests\Integration\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\AmazonPay\Controller\Admin\ConfigController;
use OxidProfessionalServices\AmazonPay\Core\Config;

class ConfigControllerTest extends UnitTestCase
{
    public function testRender(): void
    {
        $c = new ConfigController();
        $this->assertSame('amazonpay/amazonconfig.tpl', $c->render());
    }

    public function configValueProvider(): array
    {
        return [
            [['blAmazonPaySandboxMode' => 'sandbox'], true, 'getterMethod' => 'isSandbox'],
            [['blAmazonPaySandboxMode' => 'prod'], false, 'getterMethod' => 'isSandbox'],
            [['blAmazonPaySandboxMode' => 'undefined'], false, 'getterMethod' => 'isSandbox'],
            [['sAmazonPayPrivKey' => 'key'], 'key', 'getterMethod' => 'getPrivateKey'],
            [['sAmazonPayPubKeyId' => 'key id '], 'key id', 'getterMethod' => 'getPublicKeyId'],
            [['sAmazonPayMerchantId' => 'merchant id'], 'merchant id', 'getterMethod' => 'getMerchantId'],
            [['sAmazonPayStoreId' => 'store id'], 'store id', 'getterMethod' => 'getStoreId'],
            [['blAmazonPayPDP' => 'on'], true, 'getterMethod' => 'displayInPDP'],
            [['blAmazonPayPDP' => 1], true, 'getterMethod' => 'displayInPDP'],
            [['blAmazonPayPDP' => ''], false, 'getterMethod' => 'displayInPDP'],
            [['blAmazonPayPDP' => null], false, 'getterMethod' => 'displayInPDP'],
            [['blAmazonPayUseExclusion' => 'on'], true, 'getterMethod' => 'useExclusion'],
            [['blAmazonPayUseExclusion' => 1], true, 'getterMethod' => 'useExclusion'],
            [['blAmazonPayUseExclusion' => ''], false, 'getterMethod' => 'useExclusion'],
            [['blAmazonPayUseExclusion' => null], false, 'getterMethod' => 'useExclusion'],
            [['blAmazonPayMinicartAndModal' => 'on'], true, 'getterMethod' => 'displayInMiniCartAndModal'],
            [['blAmazonPayMinicartAndModal' => ''], false, 'getterMethod' => 'displayInMiniCartAndModal'],
        ];
    }

    /**
     * @dataProvider configValueProvider
     * @covers ConfigController::handleSpecialFields
     *
     * @param $conf array Configuration values
     * @param $expected mixed Expected return value
     * @param $getterMethod string Getter method in config object
     */
    public function testSave($conf, $expected, $getterMethod): void
    {
        $config = new Config();
        $configController = new ConfigController();
        $this->setRequestParameter('conf', $conf);
        $configController->save();
        $this->assertSame($expected, $config->$getterMethod());
    }
}
