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
     * @return void
     */
    public function setShopId(string $shopId)
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
     * @return void
     */
    public function setUserId(string $userId)
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
     * @return void
     */
    public function setOrderId(string $orderId)
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
     * @param string $responseMessage
     * @return void
     */
    public function setResponseMessage(string $responseMessage)
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
     * @return void
     */
    public function setStatusCode(string $statusCode)
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
     * @return void
     */
    public function setRequestType(string $requestType)
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
     * @return void
     */
    public function setIdentifier(string $identifier)
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
     * @return void
     */
    public function setChargeId(string $chargeId)
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
     * @return void
     */
    public function setChargePermissionId(string $chargePermissionId)
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
     * @return void
     */
    public function setObjectId(string $objectId)
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
     * @return void
     */
    public function setObjectType(string $objectType)
    {
        $this->objectType = $objectType;
    }
}
