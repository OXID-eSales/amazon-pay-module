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

namespace OxidProfessionalServices\AmazonPay\Core\Repository;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidProfessionalServices\AmazonPay\Core\Logger\LogMessage;

class LogRepository
{
    public const TABLE_NAME = 'amazonpaylog';

    /**
     * @param LogMessage $logMessage
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function saveLogMessage(LogMessage $logMessage): void
    {
        $id = UtilsObject::getInstance()->generateUId();

        $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (
                `OXPS_AMAZON_PAYLOGID`,
                `OXPS_AMAZON_OXSHOPID`,
                `OXPS_AMAZON_OXUSERID`,
                `OXPS_AMAZON_OXORDERID`,
                `OXPS_AMAZON_RESPONSE_MSG`,
                `OXPS_AMAZON_STATUS_CODE`,
                `OXPS_AMAZON_REQUEST_TYPE`,
                `OXPS_AMAZON_IDENTIFIER`,
                `OXPS_AMAZON_CHARGE_ID`,
                `OXPS_AMAZON_CHARGE_PERMISSION_ID`,
                `OXPS_AMAZON_OBJECT_ID`,
                `OXPS_AMAZON_OBJECT_TYPE`
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';

        DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute($sql, [
            $id,
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
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OXPS_AMAZON_OXUSERID = ? ORDER BY OXTIMESTAMP',
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
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OXPS_AMAZON_IDENTIFIER = ? ORDER BY OXTIMESTAMP',
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
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OXPS_AMAZON_CHARGE_PERMISSION_ID = ? ORDER BY ' . $orderBy,
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
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OXPS_AMAZON_OXORDERID = ? ORDER BY OXTIMESTAMP',
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
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE OXPS_AMAZON_CHARGE_ID = ? ORDER BY OXTIMESTAMP',
            [$chargeId]
        );
    }

    /**
     * @param string $chargeId
     * @return mixed|null
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function findOrderIdByChargeId($chargeId)
    {
        $orderId = null;

        $logMessages = $this->findLogMessageForChargeId($chargeId);

        foreach ($logMessages as $logMessage) {
            if (isset($logMessage['OXPS_AMAZON_OXORDERID']) && $logMessage['OXPS_AMAZON_OXORDERID'] !== 'no basket') {
                $orderId = $logMessage['OXPS_AMAZON_OXORDERID'];
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
    public function markOrderPaid($orderId, $remark, $transStatus = 'OK', $chargeId = ''): void
    {
        $sql = 'UPDATE oxorder SET OXPAID = ?, OXTRANSSTATUS = ?, OXPS_AMAZON_REMARK = ?, OXTRANSID= ? WHERE OXID=?';
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
     * @param $orderId
     * @param string $transStatus
     * @param string $chargeId
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function updateOrderStatus($orderId, $transStatus = 'OK', $chargeId = ''): void
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
}
