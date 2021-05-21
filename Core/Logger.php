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

use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogLogger;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
use OxidProfessionalServices\AmazonPay\Core\Logger\LogMessage;
use OxidProfessionalServices\AmazonPay\Core\Repository\LogRepository;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger
{
    /**
     * @var LogRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $logFileName;

    public function __construct($logFileName = 'amazonpay.log')
    {
        $this->logFileName = $logFileName;
        $this->repository = oxNew(LogRepository::class);
    }

    /**
     * @param $message
     * @param array $context
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function logMessage($message, array $context = []): void
    {
        $context = $this->resolveLogContent($context);

        $user = Registry::getSession()->getUser();
        $basket = Registry::getSession()->getBasket();
        $defaultUser = $user !== false ? $user->getId() : 'guest';
        $userId = !empty($context['userId']) ? $context['userId'] : $defaultUser;

        $logMessage = new LogMessage();
        $logMessage->setUserId($userId);
        $logMessage->setOrderId($context['orderId'] ?? $basket->getOrderId() ?? 'no basket');
        $logMessage->setShopId($context['shopId'] ?? Registry::getConfig()->getShopId());
        $logMessage->setRequestType($context['requestType'] ?? 'amazonpay');
        $logMessage->setResponseMessage($message);
        $logMessage->setStatusCode($context['statusCode'] ?? '200');
        $logMessage->setIdentifier($context['identifier'] ?? $context['orderId'] ?? $userId);
        $logMessage->setChargeId($context['chargeId'] ?? 'null');
        $logMessage->setChargePermissionId($context['chargePermissionId'] ?? 'null');
        $logMessage->setObjectId($context['objectId'] ?? 'null');
        $logMessage->setObjectType($context['objectType'] ?? 'null');

        $this->repository->saveLogMessage($logMessage);
    }

    /**
     * @param $result
     * @return array
     */
    public function resolveLogContent($result): array
    {
        $context = [];
        $response = null;

        if (!empty($result['response'])) {
            $response = PhpHelper::jsonToArray($result['response']);

            if (!empty($response['statusDetails']['state'])) {
                $context['message'] = $response['statusDetails']['state'];
            }

            if (!empty($response['deliveryDetails'])) {
                $context['identifier'] = $response['amazonOrderReferenceId'];
                $context['objectId'] = $result['identifier'];
                $context['requestType'] = 'Alexa Notification';
            }

            if (!empty($response['chargePermissionId'])) {
                $context['chargePermissionId'] = $response['chargePermissionId'];
                $context['identifier'] = $response['chargePermissionId'];
            }

            if (!empty($response['chargeId'])) {
                $context['chargeId'] = $response['chargeId'];
            }

            if (!empty($response['reasonCode'])) {
                $context['requestType'] = 'Error:' . $response['reasonCode'];
            }
        }

        if (!empty($result['ChargePermissionId'])) {
            $context['requestType'] = 'IPN';
            $context['chargePermissionId'] = $result['ChargePermissionId'];
            $context['identifier'] = $result['ChargePermissionId'];
            $context['objectId'] = $result['ObjectId'];
            $context['objectType'] = $result['ObjectType'];
        }

        if (!empty($result['request_id'])) {
            $context['requestId'] = $result['request_id'];
        }

        if (!empty($result['userId'])) {
            $context['userId'] = $result['userId'];
        }

        if (!empty($result['orderId'])) {
            $context['orderId'] = $result['orderId'];
        }

        if (!empty($result['shopId'])) {
            $context['shopId'] = $result['shopId'];
        }

        if (!empty($result['identifier'])) {
            $context['identifier'] = $result['identifier'];
        }

        return $context;
    }

    /**
     * @return LogRepository
     */
    public function getRepository(): LogRepository
    {
        return $this->repository;
    }

    /**
     * @param int $log_level
     * @return MonoLogLogger
     * @throws \Exception
     */
    private function getLogger(int $log_level): LoggerInterface
    {
        $logger = new MonoLogLogger('amazonpaylog');
        $logger->pushHandler(
            new StreamHandler(
                Registry::getConfig()->getLogsDir() . $this->logFileName,
                $log_level
            )
        );

        return $logger;
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function log($level, $message, array $context = [])
    {
        $levelName = MonoLogLogger::getLevels()[strtoupper($level)];
        $this->getLogger($levelName)->addRecord($levelName, $message, $context);
        $context['log_level'] = $levelName;
        $this->logMessage($message, $context);
    }
}
