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

namespace OxidProfessionalServices\AmazonPay\Core\Provider;

use OxidEsales\Eshop\Application\Model\User;
use OxidProfessionalServices\AmazonPay\Core\AmazonClient;
use OxidProfessionalServices\AmazonPay\Core\AmazonService;
use OxidProfessionalServices\AmazonPay\Core\Logger;
use OxidProfessionalServices\AmazonPay\Core\ServiceFactory;
use Psr\Log\LoggerInterface;

class OxidServiceProvider
{
    /**
     * @var OxidServiceProvider
     */
    private static $instance;

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
    public static function getInstance(): OxidServiceProvider
    {
        if (self::$instance === null) {
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
