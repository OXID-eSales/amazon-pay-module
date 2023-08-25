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

namespace OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core\Repository;

use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidSolutionCatalysts\AmazonPay\Core\Logger\LogMessage;
use OxidSolutionCatalysts\AmazonPay\Core\Repository\LogRepository;
use OxidEsales\TestingLibrary\UnitTestCase;

class LogRepositoryTest extends UnitTestCase
{
    /** @var LogRepository */
    private $logRepository;

    protected function setUp()
    {
        $this->logRepository = oxNew(LogRepository::class);
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function testSaveLogMessage()
    {
        $logMessage = $this->prepareLogMessage();
        $this->logRepository->saveLogMessage($logMessage);

        $results = $this->logRepository->findLogMessageForChargeId($logMessage->getChargeId());

        $this->assertNotEmpty($results[0]);
        $this->assertSame($results[0]['OSC_AMAZON_OXUSERID'], $logMessage->getUserId());
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function testFindLogMessageForUserId()
    {
        $logMessage = $this->prepareLogMessage();
        $this->logRepository->saveLogMessage($logMessage);

        $results = $this->logRepository->findLogMessageForUserId($logMessage->getUserId());

        $this->assertNotEmpty($results[0]);
        $this->assertSame($results[0]['OSC_AMAZON_OXUSERID'], $logMessage->getUserId());
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function testFindLogMessageForIdentifier()
    {
        $logMessage = $this->prepareLogMessage();
        $this->logRepository->saveLogMessage($logMessage);

        $results = $this->logRepository->findLogMessageForIdentifier($logMessage->getIdentifier());

        $this->assertNotEmpty($results[0]);
        $this->assertSame($results[0]['OSC_AMAZON_OXUSERID'], $logMessage->getUserId());
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function testFindLogMessageForChargePermissionId()
    {
        $logMessage = $this->prepareLogMessage();
        $this->logRepository->saveLogMessage($logMessage);

        $results = $this->logRepository->findLogMessageForChargePermissionId($logMessage->getChargePermissionId());

        $this->assertNotEmpty($results[0]);
        $this->assertSame($results[0]['OSC_AMAZON_OXUSERID'], $logMessage->getUserId());
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function testFindLogMessageForOrderId()
    {
        $logMessage = $this->prepareLogMessage();
        $this->logRepository->saveLogMessage($logMessage);

        $results = $this->logRepository->findLogMessageForOrderId($logMessage->getOrderId());

        $this->assertNotEmpty($results[0]);
        $this->assertSame($results[0]['OSC_AMAZON_OXUSERID'], $logMessage->getUserId());
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function testFindLogMessageForChargeId()
    {
        $logMessage = $this->prepareLogMessage();
        $this->logRepository->saveLogMessage($logMessage);

        $results = $this->logRepository->findLogMessageForChargeId($logMessage->getChargeId());

        $this->assertNotEmpty($results[0]);
        $this->assertSame($results[0]['OSC_AMAZON_OXUSERID'], $logMessage->getUserId());
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function testFindOrderIdByChargeId()
    {
        $logMessage = $this->prepareLogMessage();
        $this->logRepository->saveLogMessage($logMessage);

        $result = $this->logRepository->findOrderIdByChargeId($logMessage->getChargeId());

        $this->assertNotEmpty($result);
        $this->assertSame($result, $logMessage->getOrderId());
    }

    /**
     * @return LogMessage
     */
    private function prepareLogMessage(): LogMessage
    {
        $rand = RAND(1000, 9999);
        $logMessage = new LogMessage();
        $logMessage->setObjectType('TestObjectType' . $rand);
        $logMessage->setObjectId('TestObjectId' . $rand);
        $logMessage->setIdentifier('TestIdentifier' . $rand);
        $logMessage->setShopId('1');
        $logMessage->setChargePermissionId('testCPI' . $rand);
        $logMessage->setChargeId('testCP' . $rand);
        $logMessage->setUserId('1234' . $rand);
        $logMessage->setOrderId('1234' . $rand);
        $logMessage->setResponseMessage('test' . $rand);
        $logMessage->setRequestType('test' . $rand);
        $logMessage->setStatusCode('test' . $rand);

        return $logMessage;
    }
}
