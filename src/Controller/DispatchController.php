<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Component\UserComponent;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\Address;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Model\User;

/**
 * Class DispatchController
 *
 */
class DispatchController extends FrontendController
{
    /**
     * @return void
     * @throws \Exception
     */
    public function init(): void
    {
        parent::init();

        $logger = new Logger();
        $action = Registry::getRequest()->getRequestParameter('action');

        switch ($action) {
            case 'review':
                /** @var string $amazonSessionId */
                $amazonSessionId = $this->setRequestAmazonSessionId();
                if ($amazonSessionId === '') {
                    return;
                }
                $redirectUrl = Registry::getConfig()->getShopHomeUrl() .
                    'cl=order&stoken=' . Registry::getSession()->getSessionChallengeToken();
                Registry::getUtils()->redirect($redirectUrl, true, 302);
                break;
            case 'result':
                /** @var string $amazonSessionId */
                $amazonSessionId = $this->getRequestAmazonSessionId();
                if ($amazonSessionId === '') {
                    $amazonSessionId = $this->setRequestAmazonSessionId();
                }

                if ($amazonSessionId === '') {
                    return;
                }

                $basket = Registry::getSession()->getBasket();
                $paymentId = $basket->getPaymentId();
                if ($paymentId !== Constants::PAYMENT_ID_EXPRESS) {
                    return;
                }

                $isOneStepPayment = OxidServiceProvider::getAmazonClient()->getModuleConfig()->isOneStepCapture();
                if ($isOneStepPayment) {
                    OxidServiceProvider::getAmazonService()->processOneStepPayment(
                        $amazonSessionId,
                        $basket,
                        $logger
                    );
                }

                if (!$isOneStepPayment) {
                    OxidServiceProvider::getAmazonService()->processTwoStepPayment(
                        $amazonSessionId,
                        Registry::getSession()->getBasket(),
                        $logger
                    );
                }

                break;
            case 'ipn':
                $message = Message::fromRawPostData();

                // Validate the message
                $validator = new MessageValidator();
                if ($validator->isValid($message)) {
                    $post = PhpHelper::getPost();
                    $message = PhpHelper::jsonToArray($post['Message']);

                    if ($message['ObjectType'] === 'REFUND') {
                        OxidServiceProvider::getAmazonService()->processRefund(
                            $message['ObjectId'],
                            $logger
                        );
                    } elseif (
                        $message['ObjectType'] === 'CHARGE' &&
                        $message['NotificationType'] === 'STATE_CHANGE'
                    ) {
                        OxidServiceProvider::getAmazonService()->processCharge(
                            $message['ObjectId'],
                            $logger
                        );
                    }

                    $logger->info($message['NotificationType'], $message);
                }

                break;

            case 'poll':
                /** @var string $orderId */
                $orderId = Registry::getRequest()->getRequestParameter('orderId');

                OxidServiceProvider::getAmazonService()->checkOrderState($orderId);

                break;

            case 'signin':
                $buyerToken = Registry::getRequest()
                    ->getRequestParameter(Constants::CHECKOUT_REQUEST_BUYER_TOKEN);

                $result = OxidServiceProvider::getAmazonClient()->getBuyer($buyerToken);

                $response = [];
                $response['response'] = PhpHelper::jsonToArray($result['response']);

                if ($result['status'] !== 200) {
                    return;
                }

                $user = $this->getUser();
                $session = Registry::getSession();

                if (!$user instanceof User) {
                    // Create guest user if not logged in
                    $userComponent = oxNew(UserComponent::class);
                    $userComponent->createGuestUser($response);
                }

                if ($user instanceof User) {
                    // if Amazon provides a shipping address use it
                    if (!empty($response['response']['shippingAddress'])) {
                        $deliveryAddress = Address::mapAddressToDb(
                            $response['response']['shippingAddress'],
                            'oxaddress__'
                        );
                        $session->setVariable(Constants::SESSION_DELIVERY_ADDR, $deliveryAddress);
                    }

                    if (empty($response['response']['shippingAddress'])) {
                        // if amazon does not provide a shipping address, and we already have an oxid user,
                        // use oxid-user-data
                        $session->deleteVariable(Constants::SESSION_DELIVERY_ADDR);
                    }
                }

                Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=user');
                break;
        }
    }

    /**
     * get Amazon Session ID and validate it
     *
     * @return string
     */
    protected function getRequestAmazonSessionId(): string
    {
        /** @var string $amazonSessionIdRequest */
        $amazonSessionIdRequest = Registry::getRequest()->getRequestParameter(
            Constants::CHECKOUT_REQUEST_PARAMETER_ID
        );
        $amazonSessionIdService = OxidServiceProvider::getAmazonService()->getCheckoutSessionId();
        return
            $amazonSessionIdRequest === $amazonSessionIdService ? $amazonSessionIdRequest : '';
    }

    /**
     * set Amazon Session ID and validate it
     *
     * @return string
     * @throws DatabaseErrorException
     * @throws ArticleInputException
     * @throws DatabaseConnectionException
     * @throws NoArticleException
     * @throws OutOfStockException
     */
    protected function setRequestAmazonSessionId(): string
    {
        // add item to basket if an "anid" was provided in the url
        /** @var bool|string $anid */
        $anid = Registry::getRequest()->getRequestParameter('anid');
        if ($anid) {
            $database = DatabaseProvider::getDb();
            $database->startTransaction();
            try {
                $basket = Registry::getSession()->getBasket();
                $basket->addToBasket(
                    $anid,
                    1
                );
                // Remove flag of "new item added" to not show "Item added" popup when returning to the checkout
                $basket->isNewItemAdded();
                $basket->calculateBasket(true);
            } catch (\Exception $exception) {
                $database->rollbackTransaction();
                throw $exception;
            }
        }

        /** @var string $amazonSessionId */
        $amazonSessionId =
            Registry::getRequest()->getRequestParameter(Constants::CHECKOUT_REQUEST_PARAMETER_ID);

        if (OxidServiceProvider::getAmazonService()->getCheckoutSessionId() === '') {
            OxidServiceProvider::getAmazonService()->storeAmazonSession($amazonSessionId);
            return $amazonSessionId;
        }

        return $this->getRequestAmazonSessionId();
    }
}
