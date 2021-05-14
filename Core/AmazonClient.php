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

use Amazon\Pay\API\Client;
use Exception;
use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
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
     */
    public function createCheckoutSession($payload = null, $headers = null)
    {
        if (!$headers) {
            $headers = ['x-amz-pay-Idempotency-Key' => uniqid()];
        }

        if (!$payload) {
            $payload = [
                'webCheckoutDetails' => [
                    'checkoutReviewReturnUrl' => $this->moduleConfig->checkoutReviewUrl(),
                    'checkoutResultReturnUrl' => $this->moduleConfig->checkoutResultUrl()
                ],
                'storeId' => $this->moduleConfig->getStoreId(),
                'deliverySpecifications' => [
                    'addressRestrictions' => [
                        'type' => 'Allowed',
                        'restrictions' => $this->moduleConfig->getPossibleEUAddresses()
                    ]
                ],
                'paymentDetails' => [
                    'presentmentCurrency' => $this->moduleConfig->getPresentmentCurrency()
                ]
            ];
        }

        return parent::createCheckoutSession($payload, $headers);
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutSession($checkoutSessionId, $headers = null)
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
