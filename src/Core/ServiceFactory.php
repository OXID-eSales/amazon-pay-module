<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use Psr\Log\NullLogger;

/**
 * Builds configured amazon api clients
 */
class ServiceFactory
{
    /**
     * @return AmazonClient
     */
    public function getClient(): AmazonClient
    {
        $config = oxNew(Config::class);
        $logger = new NullLogger();

        $amazonConfig = [
            'public_key_id' => $config->getPublicKeyId(),
            'private_key' => $config->getPrivateKey(),
            'region' => $config->getPaymentRegion(),
            'sandbox' => $config->isSandbox()
        ];

        /** @var AmazonClient $client */
        $client = oxNew(AmazonClient::class, $amazonConfig, $config, $logger);

        return $client;
    }

    /**
     * @return AmazonService
     */
    public function getService(): AmazonService
    {
        return oxNew(AmazonService::class);
    }

    public function getDeliveryAddress(): DeliveryAddressService
    {
        return oxNew(DeliveryAddressService::class);
    }

    public function getTermsAndCondition(): TermsAndConditionService
    {
        return oxNew(TermsAndConditionService::class);
    }

}
