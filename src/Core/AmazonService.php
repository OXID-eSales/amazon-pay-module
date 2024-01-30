<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\DeliverySet;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Field as FieldAlias;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\Address;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Core\Repository\LogRepository;
use OxidSolutionCatalysts\AmazonPay\Model\Order;
use Psr\Log\LoggerInterface;
use stdClass;

class AmazonService
{
    /**
     * @var AmazonClient
     */
    private $client;
    /**
     * @var array
     */
    private $checkoutSession = [];

    /**
     * Delivery address
     *
     * @var ?stdClass
     */
    protected $deliveryAddress = null;

    /**
     * Billing address
     *
     * @var stdClass
     */
    protected $billingAddress;

    protected $isTwoStep = false;

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
     * @var User|null
     */
    protected $actUser = null;

    /**
     * AmazonService constructor.
     */
    public function __construct(AmazonClient $client = null)
    {
        if (!$client) {
            $factory = oxNew(ServiceFactory::class);
            $this->client = $factory->getClient();
            return;
        }
        $this->client = $client;
    }

    /**
     * @param string $checkoutSessionId
     */
    public function storeAmazonSession(string $checkoutSessionId)
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
        $checkoutSessionId = $this->getCheckoutSessionId();
        if (!$checkoutSessionId) {
            $session = Registry::getSession();
            /** @var string $paymentId */
            $paymentId = $session->getVariable('paymentid') ?? '';
            $isAmazonPayment = Constants::isAmazonPayment($paymentId);
            if ($isAmazonPayment) {
                //self::unsetPaymentMethod();
            }
            return false;
        }

        $checkoutSession = $this->getCheckoutSession();
        $sessionActive = (
            ($checkoutSession['response']['statusDetails']['state'] === Constants::CHECKOUT_OPEN) &&
            !is_null($checkoutSession['response']['buyer'])
        );
        if (!$sessionActive) {
            self::unsetPaymentMethod();
        }
        return $sessionActive;
    }

    /**
     * Checks if Amazon Pay is selected, active and an address was found
     *
     * @return bool
     */

    /**
     * Amazon checkout session id getter
     *
     * @return string
     */
    public function getCheckoutSessionId(): string
    {
        /** @var string $sessionId */
        $sessionId = Registry::getSession()->getVariable(Constants::SESSION_CHECKOUT_ID);
        return $sessionId ?: '';
    }

    /**
     * Returns Amazon checkout session array
     *
     * @return array
     */
    public function getCheckoutSession(): array
    {
        if ($this->checkoutSession != null) {
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
        /** @var array $address */
        $address = $checkoutSession['response']['shippingAddress'] ?? [];

        // map address fields only if amazon response have a shippingAddress
        return !empty($address) ? Address::mapAddressToView($address) : $address;
    }

    /**
     * Oxid formatted delivery address from Amazon
     *
     * @return stdClass
     */
    public function getDeliveryAddressAsObj(): stdClass
    {
        if (is_null($this->deliveryAddress)) {
            $this->deliveryAddress = new stdClass();
            $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $deliveryAddress = $oOrder->getDelAddressInfo();

            if ($deliveryAddress) {
                foreach ($deliveryAddress as $key => $value) {
                    $this->deliveryAddress->{$key} = new Field($value, FieldAlias::T_RAW);
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

        return array_merge($bill, Address::mapAddressToView($address));
    }

    /**
     * Oxid formatted billing address from Amazon
     *
     * @return stdClass
     */
    public function getBillingAddressAsObj(): stdClass
    {
        if (empty($this->billingAddress)) {
            $oUser = $this->getUser();
            $this->billingAddress = new stdClass();
            foreach ($this->billingAddressFields as $key) {
                $this->billingAddress->{$key} = new Field($oUser->{$key}->rawValue, FieldAlias::T_RAW);
            }
        }
        return $this->billingAddress;
    }

    /**
     * Maximal amount to refund
     *
     * @param string $orderId
     * @return float
     */
    public function getMaximalRefundAmount(string $orderId): float
    {
        $order = new Order();
        $order->load($orderId);

        $orderAmount = (float)$order->getTotalOrderSum();
        $compensation = min(75, 0.15 * $orderAmount);
        /**
         * There is no trustful method to round down numbers with precision,
         * so we cut off everything after the second decimal
         */
        $decimal = strpos($compensation, '.');
        if ($decimal !== false) {
            $decimal += 3;
            $compensation = (float)substr($compensation, 0, $decimal);
        }

        return min(150000, $orderAmount + $compensation);
    }

    public function unsetPaymentMethod()
    {
        $session = Registry::getSession();
        $session->deleteVariable(Constants::SESSION_CHECKOUT_ID);
        Registry::getSession()->deleteVariable('paymentid');
    }

    /**
     * Processing Amazon Pay
     *
     * @param string $amazonSessionId
     * @param Basket $basket Basket object
     * @param LoggerInterface $logger Logger
     */
    protected function processPayment(
        string $amazonSessionId,
        Basket $basket,
        LoggerInterface $logger
    ) {
        $amazonConfig = oxNew(Config::class);

        $payload = new Payload();
        $payload->setCheckoutChargeAmount(PhpHelper::getMoneyValue($basket->getPrice()->getBruttoPrice()));

        $payload->setCurrencyCode($amazonConfig->getPresentmentCurrency());

        $data = $payload->removeMerchantMetadata($payload->getData());

        // call Amazon Pay
        $result = OxidServiceProvider::getAmazonClient()->completeCheckoutSession(
            $amazonSessionId,
            $data
        );
        $response = $this->checkAmazonResult($result, $basket, $logger);
        // fiddle in the merchant meta data update
        $this->updateMerchantReferenceId($response['chargePermissionId'], $basket, $logger);

        if ($response['statusDetails']['state'] === 'Completed' && !$this->isTwoStep) {
            $response['statusDetails']['state'] = 'Completed & Captured';
        }

        $logger->info($response['statusDetails']['state'], $result);

        Registry::getSession()->deleteVariable(Constants::SESSION_CHECKOUT_ID);
        $request = PhpHelper::jsonToArray($result['request']);

        /** @var Order $order */
        $order = oxNew(Order::class);
        if ($order->load(Registry::getSession()->getVariable('sess_challenge'))) {
            if ($result['status'] === 200) {
                $data = [
                    "chargeAmount" => $request['chargeAmount']['amount'],
                    "chargeId" => $response['chargeId']
                ];
                $status = $this->isTwoStep ? 'AMZ_2STEP_AUTH_OK' : 'AMZ_AUTH_AND_CAPT_OK';
                $order->updateAmazonPayOrderStatus($status, $data);
                Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', false);
                return;
            } elseif ($result['status'] === 202) {
                $data = [
                    "chargeAmount" => $request['chargeAmount']['amount'],
                    "chargeId" => $response['chargeId']
                ];
                $order->updateAmazonPayOrderStatus('AMZ_AUTH_STILL_PENDING', $data);
                Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', false);
                return;
            }

            $data = [
                "result" => $result,
                "chargeId" => $response['chargeId']
            ];
            $order->updateAmazonPayOrderStatus('AMZ_AUTH_AND_CAPT_FAILED', $data);
            $this->showErrorOnRedirect($logger, $result, $basket->getOrderId());
        }
        $this->showErrorOnRedirect($logger, $result, $basket->getOrderId());
    }

    protected function checkAmazonResult(array $result, Basket $basket, LoggerInterface $logger): array
    {
        $response = PhpHelper::jsonToArray($result['response']);

        // in case of error, the resulting structure is different...
        if (!isset($result['response'], $result['status']) || $result['status'] !== 200) {
            $this->showErrorOnRedirect($logger, $result, (string)$basket->getOrderId());
        }

        return $response;
    }

    protected function updateMerchantReferenceId(string $chargePermissionId, Basket $basket, LoggerInterface $logger)
    {
        /** @var string $orderOxId */
        $orderOxId = Registry::getSession()->getVariable('sess_challenge');
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        if ($oOrder->load($orderOxId) && !empty($chargePermissionId)) {
            $activeShop = Registry::getConfig()->getActiveShop();
            /** @var string $oxCompany */
            $oxCompany = $activeShop->getFieldData('oxcompany');
            /** @var string $oxOrderSubject */
            $oxOrderSubject = $activeShop->getFieldData('oxordersubject');
            /** @var string $oxOrderNr */
            $oxOrderNr = $oOrder->getFieldData('oxordernr');

            $result = OxidServiceProvider::getAmazonClient()->updateChargePermission(
                $chargePermissionId,
                [
                    'merchantMetadata' => [
                        'merchantStoreName' => $oxCompany,
                        'merchantReferenceId' => $oxOrderNr,
                        'noteToBuyer' => $oxOrderSubject
                    ]
                ]
            );
            $this->checkAmazonResult($result, $basket, $logger);
        }
    }

    /**
     * Processing Amazon Pay Auth and Capture
     *
     * @param string $amazonSessionId
     * @param Basket $basket
     * @param LoggerInterface $logger Logger
     */
    public function processOneStepPayment(string $amazonSessionId, Basket $basket, LoggerInterface $logger)
    {
        $this->processPayment($amazonSessionId, $basket, $logger);
    }

    /**
     * Processing Amazon Pay Auth
     *
     * @param string $amazonSessionId
     * @param Basket $basket
     * @param LoggerInterface $logger Logger
     */
    public function processTwoStepPayment(string $amazonSessionId, Basket $basket, LoggerInterface $logger)
    {
        $this->isTwoStep = true;
        $this->processPayment($amazonSessionId, $basket, $logger);
    }

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @psalm-suppress UndefinedDocblockClass
     */
    public function createRefund(string $orderId, float $refundAmount, LoggerInterface $logger)
    {
        $repository = oxNew(LogRepository::class);
        $order = new Order();
        $order->load($orderId);
        /** @var string $orderCurrencyName */
        $orderCurrencyName = $order->getOrderCurrency()->name;

        if ($refundAmount < 0 || $refundAmount > $this->getMaximalRefundAmount($orderId)) {
            Registry::getUtilsView()->addErrorToDisplay(
                Registry::getLang()->translateString(
                    "OSC_AMAZONPAY_REFUND_ANNOTATION"
                ) .
                PhpHelper::getMoneyValue($this->getMaximalRefundAmount($orderId)) . " " . $orderCurrencyName
            );
            return;
        }

        $amazonConfig = oxNew(Config::class);

        $body = [
            'chargeId' => $repository->findLogMessageForOrderId($orderId)[0]['OSC_AMAZON_CHARGE_ID'],
            'refundAmount' => [
                'amount' => $refundAmount,
                'currencyCode' => $orderCurrencyName
            ],
            'softDescriptor' => 'AMZ*OXID'
        ];


        $result = OxidServiceProvider::getAmazonClient()->createRefund(
            $body,
            [
                'x-amz-pay-Idempotency-Key' => $amazonConfig->getUuid(),
                'platformId' => $amazonConfig->getPlatformId()
            ]
        );

        $response = PhpHelper::jsonToArray($result['response']);

        $responseService = oxNew(AmazonResponseService::class);
        if ($responseService->isRequestError($result)) {
            return $responseService->getRequestErrorMessage($result);
        }

        if ($result['status'] !== 201) {
            return;
        }

        $refundedAmount = $response['refundAmount']['amount'];
        $currency = $response['refundAmount']['currencyCode'];

        if ($response['statusDetails']['state'] === 'Refunded') {
            $response['statusDetails']['state'] = 'Refunded: ' . $refundedAmount . ' ' . $currency;
        }

        $repository->markOrderPaid(
            $orderId,
            'AmazonPay REFUND: ' . $refundedAmount,
            'REFUNDED',
            $response['chargeId']
        );

        $result['identifier'] = $response['refundId'];
        $result['orderId'] = $orderId;
        $logger->info($response['statusDetails']['state'], $result);
    }

    /**
     * @param string $refundId
     * @param LoggerInterface $logger
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function processRefund(string $refundId, LoggerInterface $logger)
    {
        $logger->info("Start processRefund");
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
        $currency = $response['refundAmount']['currencyCode'];

        if ($response['statusDetails']['state'] === 'Refunded') {
            $response['statusDetails']['state'] = 'Refunded: ' . $refundedAmount . ' ' . $currency;
        }

        $repository = oxNew(LogRepository::class);
        /** @var string $chargeId */
        $chargeId = $response['chargeId'];
        $orderId = $repository->findOrderIdByChargeId($chargeId);

        if ($orderId === '') {
            return;
        }

        $chargeId = (string)$response['chargeId'];
        $repository->markOrderPaid(
            $orderId,
            'AmazonPay REFUND: ' . $refundedAmount,
            'REFUNDED',
            $chargeId
        );

        $result['identifier'] = $refundId;
        $result['orderId'] = $orderId;
        $logger->info($response['statusDetails']['state'], $result);
    }

    /**
     * @param string $chargeId
     * @param LoggerInterface $logger
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function processCharge(string $chargeId, LoggerInterface $logger)
    {
        $amazonConfig = oxNew(Config::class);

        $client = OxidServiceProvider::getAmazonClient();
        $result = $client->getCharge(
            $chargeId,
            [
                'x-amz-pay-Idempotency-Key' => $amazonConfig->getUuid(),
                'platformId' => $amazonConfig->getPlatformId()
            ]
        );

        $response = $result['response'];

        if ($result['status'] !== 200) {
            return;
        }

        $repository = oxNew(LogRepository::class);
        /** @var string $chargeId */
        $chargeId = $response['chargeId'];
        $orderId = $repository->findOrderIdByChargeId($chargeId);

        if ($orderId == null) {
            return;
        }

        $order = oxNew(Order::class);
        if ($order->load($orderId)) {
            switch ($response['statusDetails']['state']) {
                case "Declined":
                    $order->updateAmazonPayOrderStatus('AMZ_AUTH_OR_CAPT_DECLINED', $result);
                    break;
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
     * @param string $orderId
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * TODO: refactor
     */
    public function checkOrderState(string $orderId)
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
            if ($log['OSC_AMAZON_RESPONSE_MSG'] === 'Canceled') {
                $isCancelled = true;
            }
            if ($log['OSC_AMAZON_RESPONSE_MSG'] === 'Refunded') {
                $isRefunded = true;
            }
            if (
                $log['OSC_AMAZON_RESPONSE_MSG'] === 'Captured'
                || $log['OSC_AMAZON_RESPONSE_MSG'] === 'Completed & Captured'
            ) {
                $isCaptured = true;
            }
            if (!empty($log['OSC_AMAZON_CHARGE_ID']) && $log['OSC_AMAZON_CHARGE_ID'] !== 'null') {
                $chargeId = $log['OSC_AMAZON_CHARGE_ID'];
            }
            if (
                !empty($log['OSC_AMAZON_CHARGE_PERMISSION_ID'])
                && $log['OSC_AMAZON_CHARGE_PERMISSION_ID'] !== 'null'
            ) {
                $chargePermissionId = $log['OSC_AMAZON_CHARGE_PERMISSION_ID'];
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
     * @param string $orderId
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function processCancel(string $orderId)
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
            if (!empty($log['OSC_AMAZON_CHARGE_ID']) && $log['OSC_AMAZON_CHARGE_ID'] !== 'null') {
                $chargeId = $log['OSC_AMAZON_CHARGE_ID'];
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
     * @param string $orderId
     */
    protected function showErrorOnRedirect(LoggerInterface $logger, array $result, string $orderId = '')
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
            /** @var string $orderNr */
            $orderNr = $order->getFieldData('oxordernr');
        }

        /** @var string $oxowneremail */
        $oxowneremail = $shop->getFieldData('oxowneremail');
        /** @var string $subject */
        $subject = $lang->translateString("AMAZON_PAY_COMPLETECHECKOUTSESSION_ERROR_SUBJECT");
        /** @var string $errorMessage */
        $errorMessage = $lang->translateString("AMAZON_PAY_COMPLETECHECKOUTSESSION_ERROR_MESSAGE");
        $mailer->sendEmail(
            $oxowneremail,
            $subject,
            sprintf(
                $errorMessage,
                $orderNr
            )
        );

        $exception = oxNew(InputException::class, $response['message']);
        Registry::getUtilsView()->addErrorToDisplay($exception, false, false, '', 'payment');
        Registry::getUtils()->redirect($config->getShopHomeUrl() . 'cl=payment');
    }

    /**
     * @param string $chargeId
     * @param string $amount
     * @param string $currencyCode
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function capturePaymentForOrder(string $chargeId, string $amount, string $currencyCode)
    {
        $amazonConfig = oxNew(Config::class);
        $logger = new Logger();

        $payload = new Payload();
        $payload->setSoftDescriptor('CC Account');
        $payload->setCaptureAmount(PhpHelper::getMoneyValue((float)$amount));

        $activeShop = Registry::getConfig()->getActiveShop();

        /** @var string $oxcompany */
        $oxcompany = $activeShop->getFieldData('oxcompany');
        $payload->setMerchantStoreName($oxcompany);
        /** @var string $oxordersubject */
        $oxordersubject = $activeShop->getFieldData('oxordersubject');
        $payload->setNoteToBuyer($oxordersubject);
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
            Registry::getUtilsView()->addErrorToDisplay(
                $exception,
                false,
                false,
                '',
                'order_overview'
            );

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

    public function sendAlexaNotification(
        string $chargePermissionId,
        string $trackingCode = '',
        string $deliveryType = ''
    ) {
        $amazonConfig = oxNew(Config::class);

        $payload = [];
        $payload['amazonOrderReferenceId'] = $chargePermissionId;

        $deliveryDetails = [];
        if (empty($trackingCode) === false) {
            $deliveryDetails[0]['trackingNumber'] = $trackingCode;
        }

        $delivery = OxNew(DeliverySet::class);

        if ($delivery->load($deliveryType)) {
            /** @var string $osc_amazon_carrier */
            $osc_amazon_carrier = $delivery->getRawFieldData('osc_amazon_carrier');
            $deliveryDetails[0]['carrierCode'] = $osc_amazon_carrier;
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

        $logMessage = 'Alexa Delivery Notification sent';
        if ($result['status'] !== 200) {
            $logMessage = 'Alexa Delivery Notification failed';
        }
        $logger->info($logMessage, $result);
    }

    /**
     * @param Order $order
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getOrderLogs(Order $order): array
    {
        $orderLogs = [];

        $repository = oxNew(LogRepository::class);
        $logMessages = $repository->findLogMessageForOrderId($order->getId());

        if (empty($logMessages)) {
            return [];
        }

        $amazonObjIds = [];
        foreach ($logMessages as $logMessage) {
            $logsWithChargePermission =
                $repository->findLogMessageForOrderId($logMessage['OSC_AMAZON_OXORDERID']);
            $error = strpos($logMessage['OSC_AMAZON_REQUEST_TYPE'], 'Error');
            $chargeId = $logMessage['OSC_AMAZON_CHARGE_ID'];
            if (!empty($chargeId) && $chargeId !== 'null') {
                $amazonObjIds[$chargeId] = 1;
            }
            if ($error !== false) {
                $logsWithChargePermission =
                    $repository->findLogMessageForChargePermissionId($logMessage['OSC_AMAZON_CHARGE_PERMISSION_ID']);
                if ($logMessage['OSC_AMAZON_CHARGE_PERMISSION_ID'] === 'null') {
                    $logsWithChargePermission =
                        $repository->findLogMessageForOrderId($logMessage['OSC_AMAZON_OXORDERID']);
                }
            }

            foreach ($logsWithChargePermission as $logsWithPermission) {
                $orderLogs[] = $logsWithPermission;
            }
        }

        // IPN logs refer the "chargeId" in OSC_AMAZON_OBJECT_ID field
        foreach (array_keys($amazonObjIds) as $objId) {
            $logsIPN = $repository->findLogMessageForAmazonObjectId($objId);
            if (!empty($logsIPN)) {
                $orderLogs = array_merge($orderLogs, $logsIPN);
            }
        }

        return $orderLogs;
    }

    /**
     * Active user getter
     */
    private function getUser(): User
    {
        if ($this->actUser === null) {
            $this->actUser = oxNew(User::class);
            $this->actUser->loadActiveUser();
        }
        return $this->actUser;
    }
}
