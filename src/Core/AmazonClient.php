<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use Amazon\Pay\API\Client;
use Exception;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use Psr\Log\LoggerInterface;

class AmazonClient extends Client
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Config
     */
    private $moduleConfig;

    /**
     * AmazonClient constructor.
     *
     * @param array $config
     * @param Config $moduleConfig
     * @param $logger
     *
     * @throws Exception
     */
    public function __construct(array $config, Config $moduleConfig, LoggerInterface $logger)
    {
        parent::__construct($config);
        $this->logger = $logger;
        $this->moduleConfig = $moduleConfig;
    }


    /**
     * @inheritDoc
     * @param array $payload
     * @param array $headers
     */
    public function createCheckoutSession($payload = [], $headers = []): array
    {
        $config = $this->getModuleConfig();

        if (!$headers) {
            $headers = [
                'x-amz-pay-Idempotency-Key' => $config->getUuid()
            ];
        }

        if (!$payload) {
            $payload = [
                'webCheckoutDetails' => [
                    'checkoutReviewReturnUrl' => $config->checkoutReviewUrl(),
                    'checkoutResultReturnUrl' => $config->checkoutResultUrl()
                ],
                'storeId' => $config->getStoreId(),
                'deliverySpecifications' => [
                    'addressRestrictions' => [
                        'type' => 'Allowed',
                        'restrictions' => $config->getPossibleEUAddresses()
                    ]
                ],
                'paymentDetails' => [
                    'presentmentCurrency' => $config->getPresentmentCurrency()
                ],
                'platformId' => $config->getPlatformId()
            ];
        }

        return parent::createCheckoutSession($payload, $headers);
    }

    /**
     * @inheritDoc
     * @param string $checkoutSessionId
     * @param array $headers
     * @return array
     */
    public function getCheckoutSession($checkoutSessionId, $headers = []): array
    {
        return $this->decodeResponse(parent::getCheckoutSession($checkoutSessionId, $headers));
    }

    /**
     * @param array $result
     *
     * @return array
     */
    private function decodeResponse(array $result): array
    {
        $result['response'] = PhpHelper::jsonToArray($result['response']);

        return $result;
    }

    /**
     * @return Config
     */
    public function getModuleConfig(): Config
    {
        return $this->moduleConfig;
    }
}
