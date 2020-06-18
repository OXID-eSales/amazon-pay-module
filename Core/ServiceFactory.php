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

namespace OxidProfessionalServices\AmazonPay\Core;

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
}
