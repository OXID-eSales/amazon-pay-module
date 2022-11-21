<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Psr\Log\LogLevel;

/**
 * Class DispatchController
 *
 */
class DispatchController extends FrontendController
{
    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        $logger = new Logger();
        $action = Registry::getRequest()->getRequestParameter('action');

        switch ($action) {
            case 'review':
                $amazonSessionId = $this->setRequestAmazonSessionId();
                if (!$amazonSessionId) {
                    return;
                }

                Registry::getUtils()->redirect(
                    Registry::getConfig()->getShopHomeUrl() . 'cl=order&stoken='
                        . Registry::getSession()->getSessionChallengeToken(),
                    true,
                    302
                );
                break;
            case 'result':
                $amazonSessionId = $this->getRequestAmazonSessionId();
                if (!$amazonSessionId) {
                    return;
                }

                if (OxidServiceProvider::getAmazonClient()->getModuleConfig()->isOneStepCapture()) {
                    OxidServiceProvider::getAmazonService()->processOneStepPayment(
                        $amazonSessionId,
                        Registry::getSession()->getBasket(),
                        $logger
                    );
                } else {
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
                $orderId = Registry::getRequest()->getRequestParameter('orderId');

                OxidServiceProvider::getAmazonService()->checkOrderState($orderId);

                break;
        }
    }

    /**
     * get Amazon Session ID and validate it
     *
     * @return mixed
     */
    protected function getRequestAmazonSessionId()
    {
        $amazonSessionId = Registry::getRequest()
            ->getRequestParameter(Constants::CHECKOUT_REQUEST_PARAMETER_ID);
        return ($amazonSessionId === OxidServiceProvider::getAmazonService()->getCheckoutSessionId()) ?
            $amazonSessionId :
            false;
    }

    /**
     * set Amazon Session ID and validate it
     *
     * @return mixed
     */
    protected function setRequestAmazonSessionId()
    {

        if ($sProductId = Registry::getRequest()->getRequestParameter('anid')) {
            $database = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
            $database->startTransaction();
            try {
                $basket = Registry::getSession()->getBasket();
                $basket->addToBasket(
                    $sProductId,
                    1
                );
                // Remove flag of "new item added" to not show "Item added" popup when returning to checkout
                $basket->isNewItemAdded();
                $basket->calculateBasket(true);
            } catch (\Exception $exception) {
                $database->rollbackTransaction();
                throw $exception;
            }
        }

        $amazonSessionId = Registry::getRequest()
            ->getRequestParameter(Constants::CHECKOUT_REQUEST_PARAMETER_ID);

        if (is_null(OxidServiceProvider::getAmazonService()->getCheckoutSessionId()))
        {
            OxidServiceProvider::getAmazonService()->storeAmazonSession($amazonSessionId);
            return $amazonSessionId;
        }

        return $this->getRequestAmazonSessionId();
    }
}
