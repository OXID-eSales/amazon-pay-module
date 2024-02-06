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

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\AmazonPay\Core\Logger as AmazonLogger;

class LoggerTest extends \OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core\AmazonTestCase
{
    const TEST_LOG_NAME = 'amazon_logger_test_log.log';

    public function testSaveLogMessage()
    {
        $amazonLogger = new AmazonLogger(self::TEST_LOG_NAME);

        $id = UtilsObject::getInstance()->generateUId();

        $amazonLogger->info('test response message', [
            'userId' => $id,
            'orderId' => $id,
            'shopId' => $id,
            'requestType' => 'test request',
            'statusCode' => 200
        ]);

        $messages = $amazonLogger->getRepository()->findLogMessageForUserId($id);
        $this->assertSame($messages[0]['OSC_AMAZON_OXUSERID'], $id);

        $logFileContents = file_get_contents(Registry::getConfig()->getLogsDir() . self::TEST_LOG_NAME);

        $expectedLogFileRow = 'amazonpaylog.INFO: test response message {'
            . '"userId":"' . $id . '",'
            . '"orderId":"' . $id . '",'
            . '"shopId":"' . $id . '",'
            . '"requestType":"test request","statusCode":200}';

        $this->assertStringContainsString($expectedLogFileRow, $logFileContents);

        unlink(Registry::getConfig()->getLogsDir() . self::TEST_LOG_NAME);
    }
}
