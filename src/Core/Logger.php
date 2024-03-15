<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogLogger;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Logger\LogMessage;
use OxidSolutionCatalysts\AmazonPay\Core\Repository\LogRepository;
use OxidSolutionCatalysts\AmazonPay\Model\User;
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

    public function __construct(string $logFileName = 'amazonpay.log')
    {
        $this->logFileName = $logFileName;
        $this->repository = oxNew(LogRepository::class);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function logMessage(string $message, array $context = [])
    {
        $context = $this->resolveLogContent($context);
        $basket = Registry::getSession()->getBasket();
        #$userId = $context['userId'] ?? Registry::getSession()->getUser();

        $userId = $context['userId'] ?? 'guest';
        if ($userId === 'guest') {
            $user = Registry::getSession()->getUser();
            if ($user instanceof User) {
                $userId = $user->getId();
            }
        }

        $logMessage = new LogMessage();
        $logMessage->setUserId($userId);
        $logMessage->setOrderId($context['orderId'] ?: $basket->getOrderId() ?: 'no basket');
        $logMessage->setShopId($context['shopId'] ?: Registry::getConfig()->getShopId());
        $logMessage->setRequestType($context['requestType'] ?? 'amazonpay');
        $logMessage->setResponseMessage($message);
        $logMessage->setStatusCode($context['statusCode'] ?: '200');
        $logMessage->setIdentifier($context['identifier'] ?: $context['orderId'] ?: $userId);
        $logMessage->setChargeId($context['chargeId'] ?: 'null');
        $logMessage->setChargePermissionId($context['chargePermissionId'] ?: 'null');
        $logMessage->setObjectId($context['objectId'] ?: 'null');
        $logMessage->setObjectType($context['objectType'] ?: 'null');

        $this->repository->saveLogMessage($logMessage);
    }

    /**
     * @param array $result
     * @return array
     */
    public function resolveLogContent(array $result): array
    {
        $context = [];

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
     * @throws Exception
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
