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

use Exception;
use OxidEsales\Eshop\Application\Model\DeliverySet;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Application\Model\Basket;
use OxidProfessionalServices\AmazonPay\Core\Helper\Address;
use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidProfessionalServices\AmazonPay\Core\Repository\LogRepository;
use Psr\Log\LoggerInterface;

class AmazonService
{
    /**
     * @var AmazonClient
     */
    private $client;
    /**
     * @var array
     */
    private $checkoutSession;

    /**
     * AmazonService constructor.
     *
     * @param AmazonClient|null $client
     * @throws Exception
     */
    public function __construct(AmazonClient $client = null)
    {
        $this->client = $client;

        if (!$client) {
            $factory = oxNew(ServiceFactory::class);
            $this->client = $factory->getClient();
        }
    }

    /**
     * @param $checkoutSessionId
     */
    public function storeAmazonSession($checkoutSessionId): void
    {
        Registry::getSession()->setVariable(
            Constants::SESSION_CHECKOUT_ID,
            $checkoutSessionId
        );
    }

    /**
     * Checks if Amazon Pay is selected and active
     *
     * @return bool
     */
    public function isAmazonSessionActive(): bool
    {
        if (!$this->getCheckoutSessionId()) {
            return false;
        }

        $checkoutSession = $this->getCheckoutSession();

        return $checkoutSession['response']['statusDetails']['state'] === Constants::CHECKOUT_OPEN;
    }

    /**
     * Amazon checkout session id getter
     *
     * @return mixed
     */
    public function getCheckoutSessionId()
    {
        return Registry::getSession()->getVariable(Constants::SESSION_CHECKOUT_ID);
    }

    /**
     * Returns Amazon checkout session array
     *
     * @return array
     */
    public function getCheckoutSession(): array
    {
        if ($this->checkoutSession !== null) {
            return $this->checkoutSession;
        }

        $checkoutSessionId = $this->getCheckoutSessionId();
        if (!$checkoutSessionId) {
            return $this->checkoutSession = [];
        }

        $this->checkoutSession = $this->client->getCheckoutSession($checkoutSessionId);

        return $this->checkoutSession;
    }

    /**
     * Oxid formatted delivery address from Amazon
     *
     * @return array
     */
    public function getDeliveryAddress(): array
    {
        $checkoutSession = $this->getCheckoutSession();
        $address = $checkoutSession['response']['shippingAddress'] ?? [];

        return Address::mapAddressToView($address);
    }

    /**
     * Oxid formatted billing address from Amazon
     *
     * @return array
     */
    public function getBillingAddress(): array
    {
        $checkoutSession = $this->getCheckoutSession();
        $address = $checkoutSession['response']['billingAddress'] ?? [];
        $buyer = $checkoutSession['response']['buyer'];
        $bill = ['oxusername' => $buyer['email']];

        return array_merge($bill, Address::mapAddressToView($address));
    }

    public function unsetPaymentMethod(): void
    {
        Registry::getSession()->deleteVariable(Constants::SESSION_CHECKOUT_ID);
        $payment = oxNew(Payment::class);

        $payment->load('oxidamazon');
        $payment->oxpayments__oxchecked = 0;
        $payment->save();

        $paymentCC = oxNew(Payment::class);

        $paymentCC->load('oxidcreditcard');
        $paymentCC->oxpayments__oxchecked = 1;
        $paymentCC->save();
    }

    public function processOneStepPayment($amazonSessionId, Basket $basket, LoggerInterface $logger): void
    {
        $payload = new Payload();
        $payload->setCheckoutChargeAmount(PhpHelper::getMoneyValue($basket->getBruttoSum()));
        $data = $payload->removeMerchantMetadata($payload->getData());

        $result = OxidServiceProvider::getAmazonClient()->completeCheckoutSession(
            $amazonSessionId,
            $data
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if ($result['status'] !== 200) {
            $this->showErrorOnRedirect($logger, $result);
        } else {
            if ($response['statusDetails']['state'] === 'Completed') {
                $response['statusDetails']['state'] = 'Completed & Captured';
            }

            $request = PhpHelper::jsonToArray($result['request']);

            $repository = new LogRepository();
            $repository->markOrderPaid(
                $basket->getOrderId(),
                'AmazonPay: ' . $request['chargeAmount']['amount'],
                'PAID'
            );
            $logger->info($response['statusDetails']['state'], $result);
            Registry::getSession()->deleteVariable(Constants::SESSION_CHECKOUT_ID);
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', true, 302);
        }
    }

    public function processTwoStepPayment($amazonSessionId, Basket $basket, LoggerInterface $logger): void
    {
        $amount = PhpHelper::getMoneyValue($basket->getBruttoSum());
        $payload = new Payload();
        $payload->setCheckoutChargeAmount($amount);
        $data = $payload->removeMerchantMetadata($payload->getData());

        $result = OxidServiceProvider::getAmazonClient()->completeCheckoutSession(
            $amazonSessionId,
            $data
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if ($result['status'] !== 200) {
            Registry::getSession()->deleteVariable(Constants::SESSION_CHECKOUT_ID);
            $result['message'] = 'Auth error - please select a different payment method';
            $this->showErrorOnRedirect($logger, $result);
        } else {
            $repository = new LogRepository();
            $repository->updateOrderStatus($basket->getOrderId(), 'PENDING');
            $logger->info($response['statusDetails']['state'], $result);
            Registry::getSession()->deleteVariable(Constants::SESSION_CHECKOUT_ID);
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', true, 302);
        }
    }

    /**
     * @param $refundId
     * @param $logger
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function processRefund($refundId, LoggerInterface $logger): void
    {
        $result = OxidServiceProvider::getAmazonClient()->getRefund(
            $refundId,
            ['x-amz-pay-Idempotency-Key' => uniqid()]
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if ($result['status'] !== 200) {
            return;
        }

        $refundedAmount = $response['refundAmount']['amount'];
        $currency = $response['refundAmount']['currency'];

        if ($response['statusDetails']['state'] === 'Refunded') {
            $response['statusDetails']['state'] = 'Refunded: ' . $refundedAmount . ' ' . $currency;
        }

        $repository = new LogRepository();
        $orderId = $repository->findOrderIdByChargeId($response['chargeId']);

        if ($orderId === null) {
            return;
        }

        $repository->markOrderPaid($orderId, 'AmazonPay REFUND: ' . $refundedAmount, 'REFUNDED');

        $result['identifier'] = $refundId;
        $result['orderId'] = $orderId;
        $logger->info($response['statusDetails']['state'], $result);
    }

    /**
     * @param $orderId
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function checkOrderState($orderId): void
    {
        $repository = new LogRepository();
        $logger = new Logger();

        $order = oxNew(Order::class);

        if (!$order->load($orderId)) {
            return;
        }

        $logs = $this->getOrderLogs($order);

        $chargeId = null;
        $chargePermissionId = null;
        $isCancelled = false;
        $isRefunded = false;
        $isCaptured = false;

        foreach ($logs as $log) {
            if ($log['OXPS_AMAZON_RESPONSE_MSG'] === 'Canceled') {
                $isCancelled = true;
            }
            if ($log['OXPS_AMAZON_RESPONSE_MSG'] === 'Refunded') {
                $isRefunded = true;
            }
            if (
                $log['OXPS_AMAZON_RESPONSE_MSG'] === 'Captured'
                || $log['OXPS_AMAZON_RESPONSE_MSG'] === 'Completed & Captured'
            ) {
                $isCaptured = true;
            }
            if (!empty($log['OXPS_AMAZON_CHARGE_ID']) && $log['OXPS_AMAZON_CHARGE_ID'] !== 'null') {
                $chargeId = $log['OXPS_AMAZON_CHARGE_ID'];
            }
            if (
                !empty($log['OXPS_AMAZON_CHARGE_PERMISSION_ID'])
                && $log['OXPS_AMAZON_CHARGE_PERMISSION_ID'] !== 'null'
            ) {
                $chargePermissionId = $log['OXPS_AMAZON_CHARGE_PERMISSION_ID'];
            }
        }

        if ($chargeId === null) {
            return;
        }

        $result = OxidServiceProvider::getAmazonClient()->getCharge(
            $chargeId,
            ['x-amz-pay-Idempotency-Key' => uniqid()]
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if ($response['statusDetails']['state'] === 'Canceled' && $isCancelled === false) {
            $this->processCancel($orderId);
        }

        if (isset($response['statusDetails']['state']) === 'Captured' && $isCaptured === false) {
            $repository->markOrderPaid($orderId, 'AmazonPay: ' . $response['statusDetails']['amount'], 'PAID');
            $result['identifier'] = $chargePermissionId;
            $result['orderId'] = $orderId;
            $logger->info($response['statusDetails']['state'], $result);
        }

        if (
            isset($response['refundedAmount']['amount']) &&
            $response['refundedAmount']['amount'] !== '0.00' &&
            $isRefunded === false
        ) {
            $repository->markOrderPaid(
                $orderId,
                'AmazonPay REFUND: ' . $response['refundedAmount']['amount'],
                'REFUNDED'
            );
            $result['identifier'] = $chargePermissionId;
            $result['orderId'] = $orderId;
            $logger->info($response['statusDetails']['state'], $result);
        }

        if ($response['statusDetails']['state'] === 'Canceled' && $isCancelled === false) {
            $this->processCancel($orderId);
        }
    }

    /**
     * @param $orderId
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function processCancel($orderId): void
    {
        $repository = new LogRepository();
        $logger = new Logger();

        $order = oxNew(Order::class);

        if (!$order->load($orderId)) {
            return;
        }

        $logs = $this->getOrderLogs($order);

        $chargeId = null;

        foreach ($logs as $log) {
            if (!empty($log['OXPS_AMAZON_CHARGE_ID']) && $log['OXPS_AMAZON_CHARGE_ID'] !== 'null') {
                $chargeId = $log['OXPS_AMAZON_CHARGE_ID'];
                continue;
            }
        }

        if ($chargeId === null) {
            return;
        }

        $result = OxidServiceProvider::getAmazonClient()->cancelCharge(
            $chargeId,
            ['cancellationReason' => 'OXID ADMIN'],
            ['x-amz-pay-Idempotency-Key' => uniqid()]
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if ($result['status'] !== 200) {
            $exception = oxNew(InputException::class, 'AmazonPay: ' . $response['message']);
            Registry::getUtilsView()->addErrorToDisplay($exception, false, false, '', 'order_overview');

            return;
        }

        if ($response['statusDetails']['state'] === 'Canceled') {
            $response['statusDetails']['state'] = 'Canceled';
        }

        $repository->updateOrderStatus($orderId, $response['statusDetails']['reasonCode']);

        $result['identifier'] = $response['chargePermissionId'];
        $result['orderId'] = $orderId;
        $logger->info($response['statusDetails']['state'], $result);
    }

    /**
     * @param LoggerInterface $logger
     * @param array $result
     */
    protected function showErrorOnRedirect(LoggerInterface $logger, array $result): void
    {
        $response = PhpHelper::jsonToArray($result['response']);

        $logger->info(
            $response['reasonCode'],
            $result
        );

        $exception = oxNew(InputException::class, $response['message']);
        Registry::getUtilsView()->addErrorToDisplay($exception, false, false, '', 'payment');
        Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=payment', true, 302);
    }

    /**
     * @param $chargeId
     * @param $amount
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function capturePaymentForOrder($chargeId, $amount): void
    {
        $logger = new Logger();

        $payload = new Payload();
        $payload->setSoftDescriptor('CC Account');
        $payload->setCaptureAmount(PhpHelper::getMoneyValue($amount));

        $result = OxidServiceProvider::getAmazonClient()->captureCharge(
            $chargeId,
            $payload->getData(),
            ['x-amz-pay-Idempotency-Key' => uniqid()]
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if (isset($response['reasonCode']) && !empty($response['reasonCode'])) {
            $logger->info(
                'Capture Error',
                $result
            );

            $exception = oxNew(InputException::class, 'AmazonPay: ' . $response['message']);
            Registry::getUtilsView()->addErrorToDisplay($exception, false, false, '', 'order_overview');

            return;
        }

        $repository = new LogRepository();
        $orderId = $repository->findOrderIdByChargeId($chargeId);
        $repository->markOrderPaid($orderId, 'AmazonPay: ' . $amount, 'PAID');

        $logger->info($response['statusDetails']['state'], $result);
    }

    public function sendAlexaNotification($chargePermissionId, $trackingCode = null, $deliveryType = null): void
    {
        $payload = [];
        $payload['amazonOrderReferenceId'] = $chargePermissionId;

        $deliveryDetails = [];
        if (empty($trackingCode) === false) {
            $deliveryDetails[0]['trackingNumber'] = $trackingCode;
        }

        $delivery = OxNew(DeliverySet::class);

        if ($delivery->load($deliveryType)) {
            $deliveryDetails[0]['carrierCode'] = $delivery->oxdeliveryset__oxps_amazon_carrier->rawValue;
        }

        if (!empty($deliveryDetails)) {
            $payload['deliveryDetails'] = $deliveryDetails;
        }

        $result = OxidServiceProvider::getAmazonClient()->deliveryTrackers(
            $payload,
            ['x-amz-pay-Idempotency-Key' => uniqid()]
        );

        $logger = OxidServiceProvider::getLogger();

        if ($result['status'] !== 200) {
            $logger->info('Alexa Delivery Notification failed', $result);
        } else {
            $logger->info('Alexa Delivery Notification sent', $result);
        }
    }

    /**
     * @param Order $order
     * @return array
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function getOrderLogs(Order $order): array
    {
        $orderLogs = [];

        $repository = new LogRepository();
        $logMessages = $repository->findLogMessageForOrderId($order->getId());

        if (empty($logMessages)) {
            return [];
        }

        foreach ($logMessages as $logMessage) {
            if (strpos($logMessage['OXPS_AMAZON_REQUEST_TYPE'], 'Error') !== false) {
                $logsWithChargePermission = $repository->findLogMessageForOrderId(
                    $logMessage['OXPS_AMAZON_OXORDERID']
                );
            } else {
                if ($logMessage['OXPS_AMAZON_CHARGE_PERMISSION_ID'] === 'null') {
                    $logsWithChargePermission = $repository->findLogMessageForOrderId(
                        $logMessage['OXPS_AMAZON_OXORDERID']
                    );
                } else {
                    $logsWithChargePermission = $repository->findLogMessageForChargePermissionId(
                        $logMessage['OXPS_AMAZON_CHARGE_PERMISSION_ID']
                    );
                }
            }

            foreach ($logsWithChargePermission as $logsWithPermission) {
                $orderLogs[] = $logsWithPermission;
            }
        }

        return $orderLogs;
    }
}