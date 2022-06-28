<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Core;

use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\DeliverySet;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Field;
use OxidProfessionalServices\AmazonPay\Core\Config;
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
     * Delivery address
     *
     * @var oxAddress|null
     */
    protected $deliveryAddress = null;

    /**
     * Billing address
     *
     * @var oxAddress|null
     */
    protected $billingAddress = null;

    /**
     * Billing address fields
     *
     * @var array
     */
    protected $billingAddressFields = [
        'oxuser__oxcompany',
        'oxuser__oxusername',
        'oxuser__oxsal',
        'oxuser__oxfname',
        'oxuser__oxlname',
        'oxuser__oxstreet',
        'oxuser__oxstreetnr',
        'oxuser__oxaddinfo',
        'oxuser__oxustid',
        'oxuser__oxcity',
        'oxuser__oxcountryid',
        'oxuser__oxcountry',
        'oxuser__oxstateid',
        'oxuser__oxzip',
        'oxuser__oxfon',
        'oxuser__oxfax'
    ];

    /**
     * oxuser object
     *
     * @var \OxidEsales\Eshop\Application\Model\User
     */
    protected $actUser = null;

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

        return ($checkoutSession['response']['statusDetails']['state'] === Constants::CHECKOUT_OPEN);
    }

    /**
     * Checks if Amazon Pay is selected, active and a Address would found
     *
     * @return bool
     */

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

        // map address fields only if amazon response have a shippingAddress
        if (!empty($address)) {
            return Address::mapAddressToView($address, 'oxaddress__');
        } else {
            return [];
        }
    }

    /**
     * Oxid formatted delivery address from Amazon
     *
     * @return object
     */
    public function getDeliveryAddressAsObj()
    {
        if (is_null($this->deliveryAddress)) {
            $this->deliveryAddress = new \stdClass();
            $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            if ($deliveryAddress = $oOrder->getDelAddressInfo()) {
                foreach ($deliveryAddress as $key => $value) {
                    $this->deliveryAddress->{$key} = new Field($value, Field::T_RAW);
                }
            }
        }
        return $this->deliveryAddress;
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

        return array_merge($bill, Address::mapAddressToView($address, 'oxuser__'));
    }

    /**
     * Oxid formatted billing address from Amazon
     *
     * @return object
     */
    public function getBillingAddressAsObj()
    {
        if (is_null($this->billingAddress)) {
            $oUser = $this->getUser();
            $this->billingAddress = new \stdClass();
            foreach ($this->billingAddressFields as $key) {
                $this->billingAddress->{$key} = new Field($oUser->{$key}->rawValue, Field::T_RAW);
            }
        }
        return $this->billingAddress;
    }

    public function unsetPaymentMethod(): void
    {
        Registry::getSession()->deleteVariable(Constants::SESSION_CHECKOUT_ID);
    }

    /**
     * Processing Amazon Pay
     *
     * @param $amazonSessionId
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket Basket object
     * @param \Psr\Log\LoggerInterface $logger Logger
     * @param bool $bl2Step
     *
     */
    protected function processPayment($amazonSessionId, Basket $basket, LoggerInterface $logger, $bl2Step = false): void
    {
        $amazonConfig = oxNew(Config::class);

        $payload = new Payload();
        $payload->setCheckoutChargeAmount(PhpHelper::getMoneyValue($basket->getPrice()->getBruttoPrice()));

        $activeShop = Registry::getConfig()->getActiveShop();

        $payload->setMerchantStoreName($activeShop->oxshops__oxcompany->value);
        $payload->setNoteToBuyer($activeShop->oxshops__oxordersubject->value);
        $payload->setCurrencyCode($amazonConfig->getPresentmentCurrency());

        $data = $payload->removeMerchantMetadata($payload->getData());

        // call Amazon Pay
        $result = OxidServiceProvider::getAmazonClient()->completeCheckoutSession(
            $amazonSessionId,
            $data
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if ($response['statusDetails']['state'] === 'Completed' && !$bl2Step) {
            $response['statusDetails']['state'] = 'Completed & Captured';
        }

        $logger->info($response['statusDetails']['state'], $result);

        Registry::getSession()->deleteVariable(Constants::SESSION_CHECKOUT_ID);
        $request = PhpHelper::jsonToArray($result['request']);

        $order = oxNew(Order::class);
        if ($order->load($basket->getOrderId())) {
            if ($result['status'] === 200) {
                $data = [
                    "chargeAmount" => $request['chargeAmount']['amount'],
                    "chargeId" => $response['chargeId']
                ];
                if (!$bl2Step) {
                    $order->updateAmazonPayOrderStatus('AMZ_AUTH_AND_CAPT_OK', $data);
                } else {
                    $order->updateAmazonPayOrderStatus('AMZ_2STEP_AUTH_OK', $data);
                }
                Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', true, 302);
            } elseif ($result['status'] === 202) {
                $data = [
                    "chargeAmount" => $request['chargeAmount']['amount'],
                    "chargeId" => $response['chargeId']
                ];
                $order->updateAmazonPayOrderStatus('AMZ_AUTH_STILL_PENDING', $data);
                Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', true, 302);
            } else {
                $data = [
                    "result" => $result,
                    "chargeId" => $response['chargeId']
                ];
                $order->updateAmazonPayOrderStatus('AMZ_AUTH_AND_CAPT_FAILED', $data);
                $this->showErrorOnRedirect($logger, $result, $basket->getOrderId());
            }
        }
        $this->showErrorOnRedirect($logger, $result, $basket->getOrderId());
    }

    /**
     * Processing Amazon Pay Auth an Capt
     *
     * @param $amazonSessionId
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket Basket object
     * @param \Psr\Log\LoggerInterface $logger Logger
     *
     */
    public function processOneStepPayment($amazonSessionId, Basket $basket, LoggerInterface $logger): void
    {
        $this->processPayment($amazonSessionId, $basket, $logger, false);
    }

    /**
     * Processing Amazon Pay Auth
     *
     * @param $amazonSessionId
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket Basket object
     * @param \Psr\Log\LoggerInterface $logger Logger
     *
     */
    public function processTwoStepPayment($amazonSessionId, Basket $basket, LoggerInterface $logger): void
    {
        $this->processPayment($amazonSessionId, $basket, $logger, true);
    }

    /**
     * @param $refundId
     * @param $logger
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function processRefund($refundId, LoggerInterface $logger): void
    {
        $amazonConfig = oxNew(Config::class);

        $result = OxidServiceProvider::getAmazonClient()->getRefund(
            $refundId,
            [
                'x-amz-pay-Idempotency-Key' => $amazonConfig->getUuid(),
                'platformId' => $amazonConfig->getPlatformId()
            ]
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

        $repository = oxNew(LogRepository::class);
        $orderId = $repository->findOrderIdByChargeId($response['chargeId']);

        if ($orderId === null) {
            return;
        }

        $repository->markOrderPaid(
            $orderId,
            'AmazonPay REFUND: ' . $refundedAmount,
            'REFUNDED',
            $response['chargeId']
        );

        $result['identifier'] = $refundId;
        $result['orderId'] = $orderId;
        $logger->info($response['statusDetails']['state'], $result);
    }

    /**
     * @param $chargeId
     * @param $logger
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function processCharge($chargeId, LoggerInterface $logger): void
    {
        $amazonConfig = oxNew(Config::class);

        $result = OxidServiceProvider::getAmazonClient()->getCharge(
            $chargeId,
            [
                'x-amz-pay-Idempotency-Key' => $amazonConfig->getUuid(),
                'platformId' => $amazonConfig->getPlatformId()
            ]
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if ($result['status'] !== 200) {
            return;
        }

        $repository = oxNew(LogRepository::class);
        $orderId = $repository->findOrderIdByChargeId($response['chargeId']);

        if ($orderId === null) {
            return;
        }

        $order = oxNew(Order::class);
        if ($order->load($orderId)) {
            switch ($response['statusDetails']['state']) {
                case "Pending":
                    $order->updateAmazonPayOrderStatus('AMZ_PAYMENT_PENDING', $result);
                    break;
                case "Captured":
                    $order->updateAmazonPayOrderStatus('AMZ_AUTH_AND_CAPT_OK', $result);
                    break;
            }
        }

        $result['identifier'] = $chargeId;
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
        $amazonConfig = oxNew(Config::class);
        $repository = oxNew(LogRepository::class);
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
            [
                'x-amz-pay-Idempotency-Key' => $amazonConfig->getUuid(),
                'platformId' => $amazonConfig->getPlatformId()
            ]
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if ($response['statusDetails']['state'] === 'Canceled' && $isCancelled === false) {
            $this->processCancel($orderId);
        } elseif ($response['statusDetails']['state'] === 'Captured' && $isCaptured === false) {
            $repository->markOrderPaid(
                $orderId,
                'AmazonPay: ' . $response['statusDetails']['amount'],
                'OK',
                $response['chargeId']
            );
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
                'REFUNDED',
                $response['chargeId']
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
        $amazonConfig = oxNew(Config::class);
        $repository = oxNew(LogRepository::class);
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
            [
                'x-amz-pay-Idempotency-Key' => $amazonConfig->getUuid(),
                'platformId' => $amazonConfig->getPlatformId()
            ]
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

        $repository->updateOrderStatus(
            $orderId,
            $response['statusDetails']['reasonCode'],
            $response['chargeId']
        );

        $result['identifier'] = $response['chargePermissionId'];
        $result['orderId'] = $orderId;
        $logger->info($response['statusDetails']['state'], $result);
    }

    /**
     * @param LoggerInterface $logger
     * @param array $result
     */
    protected function showErrorOnRedirect(LoggerInterface $logger, array $result, $orderId = ''): void
    {
        $response = PhpHelper::jsonToArray($result['response']);

        $logger->info(
            $response['reasonCode'],
            $result
        );

        // Inform the ShopOwner about broken order
        $config = Registry::getConfig();
        $lang = Registry::getLang();
        $shop = $config->getActiveShop();
        $mailer = oxNew(Email::class);
        $order = oxNew(Order::class);
        $orderNr = '';
        if ($orderId && $order->load($orderId)) {
            $orderNr = $order->oxorder__oxordernr->value;
        }

        $mailer->sendEmail(
            $shop->oxshops__oxowneremail->value,
            $lang->translateString("AMAZON_PAY_COMPLETECHECKOUTSESSION_ERROR_SUBJECT"),
            sprintf(
                $lang->translateString("AMAZON_PAY_COMPLETECHECKOUTSESSION_ERROR_MESSAGE"),
                $orderNr
            )
        );

        $exception = oxNew(InputException::class, $response['message']);
        Registry::getUtilsView()->addErrorToDisplay($exception, false, false, '', 'payment');
        Registry::getUtils()->redirect($config->getShopHomeUrl() . 'cl=payment', true, 302);
    }

    /**
     * @param $chargeId
     * @param $amount
     * @param $currencyCode
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function capturePaymentForOrder($chargeId, $amount, $currencyCode): void
    {
        $amazonConfig = oxNew(Config::class);
        $logger = new Logger();

        $payload = new Payload();
        $payload->setSoftDescriptor('CC Account');
        $payload->setCaptureAmount(PhpHelper::getMoneyValue($amount));

        $activeShop = Registry::getConfig()->getActiveShop();

        $payload->setMerchantStoreName($activeShop->oxshops__oxcompany->value);
        $payload->setNoteToBuyer($activeShop->oxshops__oxordersubject->value);
        $payload->setCurrencyCode($currencyCode);

        $result = OxidServiceProvider::getAmazonClient()->captureCharge(
            $chargeId,
            $payload->getData(),
            [
                'x-amz-pay-Idempotency-Key' => $amazonConfig->getUuid(),
                'platformId' => $amazonConfig->getPlatformId()
            ]
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

        $repository = oxNew(LogRepository::class);
        $orderId = $repository->findOrderIdByChargeId($chargeId);
        $repository->markOrderPaid(
            $orderId,
            'AmazonPay: ' . $amount,
            'OK',
            $response['chargeId']
        );

        $logger->info($response['statusDetails']['state'], $result);
    }

    public function sendAlexaNotification($chargePermissionId, $trackingCode = null, $deliveryType = null): void
    {
        $amazonConfig = oxNew(Config::class);

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
            [
                'x-amz-pay-Idempotency-Key' => $amazonConfig->getUuid(),
                'platformId' => $amazonConfig->getPlatformId()
            ]
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

        $repository = oxNew(LogRepository::class);
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

    /**
     * Active user getter
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     */
    private function getUser()
    {
        if ($this->actUser === null) {
            $this->actUser = false;
            $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            if ($user->loadActiveUser()) {
                $this->actUser = $user;
            }
        }

        return $this->actUser;
    }
}
