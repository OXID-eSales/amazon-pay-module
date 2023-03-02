<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core\Provider;

use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonClient;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonService;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\ServiceFactory;
use Psr\Log\LoggerInterface;

class OxidServiceProvider
{
    /**
     * @var OxidServiceProvider|null
     */
    private static ?OxidServiceProvider $instance = null;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var AmazonClient
     */
    private AmazonClient $amazonClient;

    /**
     * @var AmazonService
     */
    private AmazonService $amazonService;

    /**
     * @var User
     */
    private User $oxidUser;

    private function __construct()
    {
        $this->logger = new Logger();
        $this->amazonClient = oxNew(ServiceFactory::class)->getClient();
        $this->amazonService = oxNew(ServiceFactory::class)->getService();
        $this->oxidUser = oxNew(User::class);
    }

    /**
     * @return OxidServiceProvider
     */
    public static function getInstance(): OxidServiceProvider
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return AmazonClient
     */
    public static function getAmazonClient(): AmazonClient
    {
        return self::getInstance()->amazonClient;
    }

    /**
     * @return AmazonService
     */
    public static function getAmazonService(): AmazonService
    {
        return self::getInstance()->amazonService;
    }

    /**
     * @return User
     */
    public static function getOxidUser(): User
    {
        return self::getInstance()->oxidUser;
    }

    /**
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        return self::getInstance()->logger;
    }
}
