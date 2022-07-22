<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidProfessionalServices\AmazonPay\Tests\Unit\Core;

use Mockery;
use Mockery\MockInterface;
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

    /** @var Config */
    protected $moduleConfig;

    /** @var LoggerInterface | MockInterface */
    protected $mockLogger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleConfig = oxNew(Config::class);

        $configurations = [
            'public_key_id' => $this->moduleConfig->getPublicKeyId(),
            'private_key' => $this->moduleConfig->getPrivateKey(),
            'region' => $this->moduleConfig->getPaymentRegion(),
            'sandbox' => true
        ];

        $this->mockLogger = Mockery::mock(LoggerInterface::class);

        $this->amazonClient = new AmazonClient(
            $configurations,
            $this->moduleConfig,
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
