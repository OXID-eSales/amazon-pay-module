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
    protected $filteredDeliveryAddress = null;

    /**
     * Billing address
     *
     * @var oxAddress|null
     */
    protected $filteredBillingAddress = null;

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
     * Delivery address
     *
     * @var oxAddress|null
     */
    protected $delAddress = null;

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
        }
        else {
            return [];
        }
    }

    /**
     * Oxid formatted and filtered delivery address from Amazon
     *
     * @return array
     */
    public function getFilteredDeliveryAddress()
    {
        if (is_null($this->filteredDeliveryAddress)) {
            $this->filteredDeliveryAddress = false;
            if ($deliveryAddress = $this->getDelAddress()) {
                $this->filteredDeliveryAddress = $this->filterAddress(
                    $deliveryAddress,
                    $this->getMissingRequiredDeliveryFields()
                );
            }
        }
        return $this->filteredDeliveryAddress;
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
     * Oxid formatted and filtered billing address from Amazon
     *
     * @return object
     */
    public function getFilteredBillingAddress()
    {
        if (is_null($this->filteredBillingAddress)) {
            $this->filteredBillingAddress = false;
            $oUser = $this->getUser();
            $billingAddress = new \stdClass();
            foreach ($this->billingAddressFields as $key) {
                $billingAddress->{$key} = $oUser->{$key}->rawValue;
            }
            $this->filteredBillingAddress = $this->filterAddress(
                $billingAddress,
                $this->getMissingRequiredBillingFields()
            );
        }
        return $this->filteredBillingAddress;
    }

    /**
     * Oxid missed billing address fields
     *
     * @return array
     */
    public function getMissingRequiredBillingFields(): array
    {
        $missingBillingFields = Registry::getSession()->getVariable('amazonMissingBillingFields');
        return is_array($missingBillingFields) ? $missingBillingFields : [];
    }

    /**
     * Oxid missed delivery address fields
     *
     * @return array
     */
    public function getMissingRequiredDeliveryFields(): array
    {
        $missingDeliveryFields = Registry::getSession()->getVariable('amazonMissingDeliveryFields');
        return is_array($missingDeliveryFields) ? $missingDeliveryFields : [];
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
        $amazonConfig = oxNew(Config::class);

        $payload = new Payload();
        $payload->setCheckoutChargeAmount(PhpHelper::getMoneyValue($basket->getPrice()->getBruttoPrice()));

        $activeShop = Registry::getConfig()->getActiveShop();

        $payload->setMerchantStoreName($activeShop->oxshops__oxcompany->value);
        $payload->setNoteToBuyer($activeShop->oxshops__oxordersubject->value);

        $payload->setCurrencyCode($amazonConfig->getPresentmentCurrency());

        $data = $payload->removeMerchantMetadata($payload->getData());

        $result = OxidServiceProvider::getAmazonClient()->completeCheckoutSession(
            $amazonSessionId,
            $data
        );

        $response = PhpHelper::jsonToArray($result['response']);

        if ($response['statusDetails']['state'] === 'Completed') {
            $response['statusDetails']['state'] = 'Completed & Captured';
        }

        $logger->info($response['statusDetails']['state'], $result);

        Registry::getSession()->deleteVariable(Constants::SESSION_CHECKOUT_ID);
        $request = PhpHelper::jsonToArray($result['request']);
        $repository = oxNew(LogRepository::class);

        if ($result['status'] === 200) {
            $repository->markOrderPaid(
                $basket->getOrderId(),
                'AmazonPay: ' . $request['chargeAmount']['amount'],
                'OK',
                $response['chargeId']
            );

            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', true, 302);
        } elseif ($result['status'] === 202) {
            $repository->updateOrderStatus(
                'AmazonPay: ' . $request['chargeAmount']['amount'],
                'NOT_FINISHED',
                $response['chargeId']
            );

            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', true, 302);
        } else {
            $repository->updateOrderStatus(
                $basket->getOrderId(),
                'ERROR',
                $response['chargeId']
            );

            $this->showErrorOnRedirect($logger, $result, $basket->getOrderId());
        }
    }

    public function processTwoStepPayment($amazonSessionId, Basket $basket, LoggerInterface $logger): void
    {
        $amazonConfig = oxNew(Config::class);

        $amount = PhpHelper::getMoneyValue($basket->getPrice()->getBruttoPrice());
        $payload = new Payload();
        $payload->setCheckoutChargeAmount($amount);

        $activeShop = Registry::getConfig()->getActiveShop();

        $payload->setMerchantStoreName($activeShop->oxshops__oxcompany->value);
        $payload->setNoteToBuyer($activeShop->oxshops__oxordersubject->value);

        $payload->setCurrencyCode($amazonConfig->getPresentmentCurrency());

        $data = $payload->removeMerchantMetadata($payload->getData());

        $result = OxidServiceProvider::getAmazonClient()->completeCheckoutSession(
            $amazonSessionId,
            $data
        );

        $response = PhpHelper::jsonToArray($result['response']);
        $repository = oxNew(LogRepository::class);
        $logger->info($response['statusDetails']['state'], $result);
        Registry::getSession()->deleteVariable(Constants::SESSION_CHECKOUT_ID);

        if ($result['status'] === 200) {
            $repository->updateOrderStatus(
                $basket->getOrderId(),
                'AMZ-Authorize-Open',
                $response['chargeId']
            );
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', true, 302);
        } elseif ($result['status'] === 202) {
            $repository->updateOrderStatus(
                $basket->getOrderId(),
                'AMZ-Authorize-Pending',
                $response['chargeId']
            );
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=thankyou', true, 302);
        } else {
            $repository->updateOrderStatus(
                $basket->getOrderId(),
                'ERROR',
                $response['chargeId']
            );

            $result['message'] = 'Auth error - please select a different payment method';
            $this->showErrorOnRedirect($logger, $result, $basket->getOrderId());
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
        }
        elseif ($response['statusDetails']['state'] === 'Captured' && $isCaptured === false) {
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

    /**
     * Returns delivery address
     *
     * @return object
     */
    public function getDelAddress()
    {
        if ($this->delAddress === null) {
            $this->delAddress = false;
            $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $this->delAddress = $oOrder->getDelAddressInfo();
        }

        return $this->delAddress;
    }

    private function filterAddress($address, array $missingFields = [])
    {
        $filteredAddress = new \stdClass();
        foreach ($address as $key => $value) {
            $value = (!isset($missingFields[$key])) ? $value : '';
            $filteredAddress->{$key} = new Field($value, Field::T_RAW);
        }
        return $filteredAddress;
    }
}
