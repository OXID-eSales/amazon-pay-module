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
    private static $instance = null;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AmazonClient
     */
    private $amazonClient;

    /**
     * @var AmazonService
     */
    private $amazonService;

    /**
     * @var User
     */
    private $oxidUser;

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
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return AmazonClient
     */
    public static function getAmazonClient()
    {
        return self::getInstance()->amazonClient;
    }

    /**
     * @return AmazonService
     */
    public static function getAmazonService()
    {
        return self::getInstance()->amazonService;
    }

    /**
     * @return User
     */
    public static function getOxidUser()
    {
        return self::getInstance()->oxidUser;
    }

    /**
     * @return LoggerInterface
     */
    public static function getLogger()
    {
        return self::getInstance()->logger;
    }
}
