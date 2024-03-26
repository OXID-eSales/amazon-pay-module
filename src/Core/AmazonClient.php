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
use Psr\Log\LogLevel;

class AmazonClient extends Client
{
    private LoggerInterface $logger;
    private Config $moduleConfig;

    /**
     * AmazonClient constructor.
     *
     * @param array $config
     * @param Config $moduleConfig
     * @param LoggerInterface $logger
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
     * @param array $payload
     * @param array $headers
     */
    public function createCheckoutSession($payload, $headers)
    {
        $config = $this->getModuleConfig();

        if (empty($headers)) {
            $headers = [
                'x-amz-pay-Idempotency-Key' => $config->getUuid()
            ];
        }

        if (empty($payload)) {
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
     * @param string $chargePermissionId
     * @param array $headers
     * @return array
     */
    public function getChargePermission($chargePermissionId, $headers = []): array
    {
        return $this->decodeResponse(parent::getChargePermission($chargePermissionId, $headers));
    }

    /**
     * @param string $chargeId
     * @param array $headers
     * @return array
     */
    public function getCharge($chargeId, $headers = []): array
    {
        return $this->decodeResponse(parent::getChargePermission($chargeId, $headers));
    }

    /**
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
        if ($this->moduleConfig->isSandbox()) {
            $logger = new Logger();
            $logger->log(LogLevel::DEBUG, (string)$result['response']);
        }

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
