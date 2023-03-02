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

namespace OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core\Logger;

use OxidSolutionCatalysts\AmazonPay\Core\Logger\LogMessage;
use OxidEsales\TestingLibrary\UnitTestCase;

class LogMessageTest extends UnitTestCase
{
    /** @var LogMessage */
    private LogMessage $logMessage;

    protected function setUp(): void
    {
        $this->logMessage = new LogMessage();

        $this->logMessage->setObjectType('PhpUnitTest');
        $this->logMessage->setIdentifier('PhpUnitTestIdentifier');
        $this->logMessage->setObjectId('PhpUnitTestObjectIdentifier');
        $this->logMessage->setChargeId('PhpUnitTestChargeId');
        $this->logMessage->setChargePermissionId('PhpUnitTestChargePermissionId');
        $this->logMessage->setShopId('PhpUnitTestShopId');
        $this->logMessage->setStatusCode('PhpUnitTestStatusCode');
        $this->logMessage->setResponseMessage('PhpUnitTestResponseMessage');
        $this->logMessage->setOrderId('PhpUnitTestOrderId');
        $this->logMessage->setUserId('PhpUnitTestUserId');
        $this->logMessage->setRequestType('PhpUnitRequestType');
    }

    public function testGetShopId(): void
    {
        $this->assertEquals('PhpUnitTestShopId', $this->logMessage->getShopId());
    }

    public function testSetShopId(): void
    {
        $this->logMessage->setShopId('PhpUnitTestShopId2');
        $this->assertEquals('PhpUnitTestShopId2', $this->logMessage->getShopId());
    }

    public function testGetUserId(): void
    {
        $this->assertEquals('PhpUnitTestUserId', $this->logMessage->getUserId());
    }

    public function testSetUserId(): void
    {
        $this->logMessage->setUserId('PhpUnitTestUserId2');
        $this->assertEquals('PhpUnitTestUserId2', $this->logMessage->getUserId());
    }

    public function testGetOrderId(): void
    {
        $this->assertEquals('PhpUnitTestOrderId', $this->logMessage->getOrderId());
    }

    public function testSetOrderId(): void
    {
        $this->logMessage->setOrderId('PhpUnitTestOrderId2');
        $this->assertEquals('PhpUnitTestOrderId2', $this->logMessage->getOrderId());
    }

    public function testGetResponseMessage(): void
    {
        $this->assertEquals('PhpUnitTestResponseMessage', $this->logMessage->getResponseMessage());
    }

    public function testSetResponseMessage(): void
    {
        $this->logMessage->setResponseMessage('PhpUnitTestResponseMessage2');
        $this->assertEquals('PhpUnitTestResponseMessage2', $this->logMessage->getResponseMessage());
    }

    public function testGetStatusCode(): void
    {
        $this->assertEquals('PhpUnitTestStatusCode', $this->logMessage->getStatusCode());
    }

    public function testSetStatusCode(): void
    {
        $this->logMessage->setStatusCode('PhpUnitTestStatusCode2');
        $this->assertEquals('PhpUnitTestStatusCode2', $this->logMessage->getStatusCode());
    }

    public function testGetRequestType(): void
    {
        $this->assertEquals('PhpUnitRequestType', $this->logMessage->getRequestType());
    }

    public function testSetRequestType(): void
    {
        $this->logMessage->setRequestType('PhpUnitRequestType2');
        $this->assertEquals('PhpUnitRequestType2', $this->logMessage->getRequestType());
    }

    public function testGetIdentifier(): void
    {
        $this->assertEquals('PhpUnitTestIdentifier', $this->logMessage->getIdentifier());
    }

    public function testSetIdentifier(): void
    {
        $this->logMessage->setIdentifier('PhpUnitTestIdentifier2');
        $this->assertEquals('PhpUnitTestIdentifier2', $this->logMessage->getIdentifier());
    }

    public function testGetChargeId(): void
    {
        $this->assertEquals('PhpUnitTestChargeId', $this->logMessage->getChargeId());
    }

    public function testSetChargeId(): void
    {
        $this->logMessage->setChargeId('PhpUnitTestChargeId2');
        $this->assertEquals('PhpUnitTestChargeId2', $this->logMessage->getChargeId());
    }

    public function testGetChargePermissionId(): void
    {
        $this->assertEquals('PhpUnitTestChargePermissionId', $this->logMessage->getChargePermissionId());
    }

    public function testSetChargePermissionId(): void
    {
        $this->logMessage->setChargePermissionId('PhpUnitTestChargePermissionId2');
        $this->assertEquals('PhpUnitTestChargePermissionId2', $this->logMessage->getChargePermissionId());
    }

    public function testGetObjectId(): void
    {
        $this->assertEquals('PhpUnitTestObjectIdentifier', $this->logMessage->getObjectId());
    }

    public function testSetObjectId(): void
    {
        $this->logMessage->setObjectId('PhpUnitTestObjectIdentifier2');
        $this->assertEquals('PhpUnitTestObjectIdentifier2', $this->logMessage->getObjectId());
    }

    public function testGetObjectType(): void
    {
        $this->assertEquals('PhpUnitTest', $this->logMessage->getObjectType());
    }

    public function testSetObjectType(): void
    {
        $this->logMessage->setObjectType('PhpUnitTest2');
        $this->assertEquals('PhpUnitTest2', $this->logMessage->getObjectType());
    }
}
