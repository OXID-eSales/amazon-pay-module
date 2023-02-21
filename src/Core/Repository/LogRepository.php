<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Logger\LogMessage;

class LogRepository
{
    public const TABLE_NAME = 'amazonpaylog';

    /**
     * @param LogMessage $logMessage
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     * @psalm-suppress InternalMethod
     */
    public function saveLogMessage(LogMessage $logMessage): void
    {
        $uid = Registry::getUtilsObject()->generateUID();

        $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (
                `OSC_AMAZON_PAYLOGID`,
                `OSC_AMAZON_OXSHOPID`,
                `OSC_AMAZON_OXUSERID`,
                `OSC_AMAZON_OXORDERID`,
                `OSC_AMAZON_RESPONSE_MSG`,
                `OSC_AMAZON_STATUS_CODE`,
                `OSC_AMAZON_REQUEST_TYPE`,
                `OSC_AMAZON_IDENTIFIER`,
                `OSC_AMAZON_CHARGE_ID`,
                `OSC_AMAZON_CHARGE_PERMISSION_ID`,
                `OSC_AMAZON_OBJECT_ID`,
                `OSC_AMAZON_OBJECT_TYPE`
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';

        DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute($sql, [
            $uid,
            $logMessage->getShopId(),
            $logMessage->getUserId(),
            $logMessage->getOrderId(),
            $logMessage->getResponseMessage(),
            $logMessage->getStatusCode(),
            $logMessage->getRequestType(),
            $logMessage->getIdentifier(),
            $logMessage->getChargeId(),
            $logMessage->getChargePermissionId(),
            $logMessage->getObjectId(),
            $logMessage->getObjectType()
        ]);
    }

    /**
     * @param string $userId
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function findLogMessageForUserId(string $userId): array
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OSC_AMAZON_OXUSERID = ? ORDER BY OXTIMESTAMP',
            [$userId]
        );
    }

    /**
     * @param string $identifier
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function findLogMessageForIdentifier(string $identifier): array
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OSC_AMAZON_IDENTIFIER = ? ORDER BY OXTIMESTAMP',
            [$identifier]
        );
    }

    /**
     * @param string $chargePermissionId
     * @param string $orderBy
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function findLogMessageForChargePermissionId(
        string $chargePermissionId,
        string $orderBy = 'OXTIMESTAMP'
    ): array {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OSC_AMAZON_CHARGE_PERMISSION_ID = ? ORDER BY ' . $orderBy,
            [$chargePermissionId]
        );
    }

    /**
     * @param string $orderId
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function findLogMessageForOrderId(string $orderId): array
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OSC_AMAZON_OXORDERID = ? ORDER BY OXTIMESTAMP',
            [$orderId]
        );
    }

    /**
     * @param string $chargeId
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function findLogMessageForChargeId(string $chargeId): array
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OSC_AMAZON_CHARGE_ID = ? ORDER BY OXTIMESTAMP',
            [$chargeId]
        );
    }

    /**
     * @param string $chargeId
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function findOrderIdByChargeId(string $chargeId): string
    {
        $orderId = '';

        $logMessages = $this->findLogMessageForChargeId($chargeId);

        foreach ($logMessages as $logMessage) {
            if (isset($logMessage['OSC_AMAZON_OXORDERID']) && $logMessage['OSC_AMAZON_OXORDERID'] !== 'no basket') {
                $orderId = $logMessage['OSC_AMAZON_OXORDERID'];
                break;
            }
        }

        return $orderId;
    }

    /**
     * @param $orderId
     * @param $remark
     * @param string $chargeId
     * @param string $transStatus
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function markOrderPaid(string $orderId, string $remark, $transStatus = 'OK', $chargeId = ''): void
    {
        $sql = 'UPDATE oxorder SET OXPAID = ?, OXTRANSSTATUS = ?, OSC_AMAZON_REMARK = ?, OXTRANSID= ? WHERE OXID=?';
        DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute(
            $sql,
            [
                date('Y-m-d H:i:s'),
                $transStatus,
                $remark,
                $chargeId,
                $orderId
            ]
        );
    }

    /**
     * @param string $orderId
     * @param string $transStatus
     * @param string $chargeId
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function updateOrderStatus(string $orderId, string $transStatus = 'OK', string $chargeId = ''): void
    {
        $sql = 'UPDATE oxorder SET OXTRANSSTATUS = ?, OXTRANSID= ? WHERE OXID=?';
        DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute(
            $sql,
            [
                $transStatus,
                $chargeId,
                $orderId
            ]
        );
    }

    /**
     * @param string $orderId
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function deleteLogMessageByOrderId(string $orderId): void
    {
        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE OSC_AMAZON_OXORDERID =' . $orderId;
        DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute(
            $sql
        );
    }
}
