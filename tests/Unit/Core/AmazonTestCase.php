<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core;

use Dotenv\Dotenv;
use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonClient;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonService;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AmazonTestCase extends TestCase
{

    protected AmazonService $amazonService;
    protected AmazonClient $amazonClient;
    protected Config $moduleConfig;

    protected MockObject $mockLogger;
    public static $modulConfig = [];

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleConfig = oxNew(Config::class);


        if (empty(self::$modulConfig)) {
            /**
             * On a second run of this method, $_ENV won't have the .env-files content.
             * That's why we store the data from the first run in a static attribute.
             */
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();
            self::$modulConfig = [
                'sAmazonPayStoreId' => $_ENV['MODULE_AMAZON_PAY_STORE_ID'],
                'sAmazonPayMerchantId' => $_ENV['MODULE_AMAZON_PAY_MERCHANT_ID'],
                'sAmazonPayPubKeyId' => $_ENV['MODULE_AMAZON_PAY_PUB_KEY_ID'],
                'sAmazonPayPrivKey' => $_ENV['MODULE_AMAZON_PAY_PRIV_KEY']
            ];
        }

        $this->setConfigParam('sAmazonPayStoreId', self::$modulConfig['sAmazonPayStoreId']);
        $this->setConfigParam('sAmazonPayMerchantId', self::$modulConfig['sAmazonPayMerchantId']);
        $this->setConfigParam('sAmazonPayPubKeyId', self::$modulConfig['sAmazonPayPubKeyId']);
        $this->setConfigParam('sAmazonPayPrivKey', self::$modulConfig['sAmazonPayPrivKey']);

        $configurations = [
            'public_key_id' => self::$modulConfig['sAmazonPayPubKeyId'],
            'private_key' => self::$modulConfig['sAmazonPayPrivKey'],
            'region' => $this->moduleConfig->getPaymentRegion(),
            'sandbox' => true
        ];

        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->amazonClient = new AmazonClient(
            $configurations,
            $this->moduleConfig,
            $this->mockLogger
        );

        $this->amazonService = new AmazonService(
            $this->amazonClient
        );
    }

    protected function createTestCheckoutSession(): array
    {
        return $this->amazonClient->createCheckoutSession([], []);
    }

    protected function createAmazonSession(): string
    {
        $result = $this->createTestCheckoutSession();
        $response = json_decode($result['response'], true);
        $checkoutSessionId = $response['checkoutSessionId'];
        $this->amazonService->storeAmazonSession($checkoutSessionId);

        return $checkoutSessionId;
    }

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

    public function setConfigParam(string $name, mixed $value): void
    {
        Registry::getConfig()->setConfigParam($name, $value);
    }

    public function setRequestParameter(string $paramName, mixed $paramValue): void
    {
        $_POST[$paramName] = $paramValue;
    }
}
