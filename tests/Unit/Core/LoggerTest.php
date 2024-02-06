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

namespace OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core;

use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Repository\LogRepository;

class LoggerTest extends \OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core\AmazonTestCase
{
    /** @var Logger */
    private $logger;

    protected function setUp(): void
    {
        $mockLogFileName = 'amazonpay-test.log';
        $this->logger = new Logger(
            $mockLogFileName
        );
    }

    public function testResolveLogContent()
    {
        /** @var array $content */
        $content = json_decode(file_get_contents(__DIR__ . '/../../Fixtures/amazonresponse.json'), true) ?: [];
        $result = $this->logger->resolveLogContent($content);

        $this->assertSame('IPN', $result['requestType']);
        $this->assertSame($content['chargePermissionId'], $result['ChargePermissionId']);
        $this->assertSame($content['identifier'], $result['ChargePermissionId']);
        $this->assertSame($content['objectId'], $result['ObjectId']);
    }

    public function testGetRepository()
    {
        $this->assertInstanceOf(LogRepository::class, $this->logger->getRepository());
    }
}
