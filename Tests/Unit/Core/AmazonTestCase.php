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

namespace OxidProfessionalServices\AmazonPay\Tests\Unit\Core;

use Mockery;
use Mockery\MockInterface;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\AmazonPay\Core\AmazonClient;
use OxidProfessionalServices\AmazonPay\Core\AmazonService;
use OxidProfessionalServices\AmazonPay\Core\Config;
use Psr\Log\LoggerInterface;

class AmazonTestCase extends UnitTestCase
{
    /** @var AmazonService */
    protected $amazonService;

    /** @var AmazonClient */
    protected $amazonClient;

    /** @var array */
    protected $mockConfig;

    /** @var Config | MockInterface */
    protected $mockModuleConfig;

    /** @var LoggerInterface | MockInterface */
    protected $mockLogger;

    protected function setUp()
    {
        parent::setUp();

        $this->mockModuleConfig = Mockery::mock(Config::class);

        $this->mockConfig = [
            'public_key_id' => Registry::getConfig()->getConfigParam('sAmazonPayPubKeyId'),
            'private_key' => Registry::getConfig()->getConfigParam('sAmazonPayPrivKey'),
            'region' => 'eu',
            'sandbox' => true
        ];

        $this->mockLogger = Mockery::mock(LoggerInterface::class);

        $this->amazonClient = new AmazonClient(
            $this->mockConfig,
            $this->mockModuleConfig,
            $this->mockLogger
        );

        $this->amazonService = new AmazonService(
            $this->amazonClient
        );
    }

    /**
     * @return array
     */
    protected function createTestCheckoutSession(): array
    {
        $amazonConfig = new Config();

        $this->mockModuleConfig
            ->shouldReceive('getUuid')
            ->andReturn($amazonConfig->getUuid());

        $this->mockModuleConfig
            ->shouldReceive('checkoutReviewUrl')
            ->andReturn('http://localhost');

        $this->mockModuleConfig
            ->shouldReceive('checkoutResultUrl')
            ->andReturn('http://localhost');

        $this->mockModuleConfig
            ->shouldReceive('getPossibleEUAddresses')
            ->andReturn(
                [
                    'DE' => (object) null
                ]
            );

        $this->mockModuleConfig
            ->shouldReceive('getPresentmentCurrency')
            ->andReturn('EUR');

        $this->mockModuleConfig
            ->shouldReceive('getStoreId')
            ->andReturn(Registry::getConfig()->getConfigParam('sAmazonPayStoreId'));

        return $this->amazonClient->createCheckoutSession();
    }

    protected function createAmazonSession(): string
    {
        $result = $this->createTestCheckoutSession();
        $response = json_decode($result['response'], true);
        $checkoutSessionId = $response['checkoutSessionId'];
        $this->amazonService->storeAmazonSession($checkoutSessionId);

        return $checkoutSessionId;
    }

    /**
     * @return array
     */
    protected function getAddressArray(): array
    {
        $address = [];
        $address['name'] = 'Some Name';
        $address['countryCode'] = 'DE';
        $address['addressLine1'] = 'Some street 521';
        $address['addressLine2'] = 'Some street';
        $address['addressLine3'] = 'Some city';
        $address['postalCode'] = '12345';
        $address['city'] = 'Freiburg';
        $address['phoneNumber'] = '+44989383728';
        $address['company'] = 'Some street 521, Some city';
        $address['stateOrRegion'] = 'BW';

        return $address;
    }
}
