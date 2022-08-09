<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core\Logger;

class LogMessage
{
    /**
     * @var string
     */
    public $shopId;

    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $orderId;

    /**
     * @var string
     */
    public $responseMessage;

    /**
     * @var string
     */
    public $statusCode;

    /**
     * @var string
     */
    public $requestType;

    /**
     * @var string
     */
    public $identifier;

    /**
     * @var string
     */
    public $chargeId;

    /**
     * @var string
     */
    public $chargePermissionId;

    /**
     * @var string
     */
    public $objectId;

    /**
     * @var string
     */
    public $objectType;

    /**
     * @return string
     */
    public function getShopId(): string
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     */
    public function setShopId(string $shopId): void
    {
        $this->shopId = $shopId;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getResponseMessage(): string
    {
        return $this->responseMessage;
    }

    /**
     * @param string|null $responseMessage
     */
    public function setResponseMessage(?string $responseMessage): void
    {
        if (!$responseMessage) {
            $responseMessage = '';
        }
        $this->responseMessage = $responseMessage;
    }

    /**
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    /**
     * @param string $statusCode
     */
    public function setStatusCode(string $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getRequestType(): string
    {
        return $this->requestType;
    }

    /**
     * @param string $requestType
     */
    public function setRequestType(string $requestType): void
    {
        $this->requestType = $requestType;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getChargeId(): string
    {
        return $this->chargeId;
    }

    /**
     * @param string $chargeId
     */
    public function setChargeId(string $chargeId): void
    {
        $this->chargeId = $chargeId;
    }

    /**
     * @return string
     */
    public function getChargePermissionId(): string
    {
        return $this->chargePermissionId;
    }

    /**
     * @param string $chargePermissionId
     */
    public function setChargePermissionId(string $chargePermissionId): void
    {
        $this->chargePermissionId = $chargePermissionId;
    }

    /**
     * @return string
     */
    public function getObjectId(): string
    {
        return $this->objectId;
    }

    /**
     * @param string $objectId
     */
    public function setObjectId(string $objectId): void
    {
        $this->objectId = $objectId;
    }

    /**
     * @return string
     */
    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType(string $objectType): void
    {
        $this->objectType = $objectType;
    }
}
