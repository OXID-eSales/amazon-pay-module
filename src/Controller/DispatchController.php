<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\Constants;
use OxidProfessionalServices\AmazonPay\Core\Helper\PhpHelper;
use OxidProfessionalServices\AmazonPay\Core\Logger;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

/**
 * Class DispatchController
 *
 */
class DispatchController extends FrontendController
{
    public function init()
    {
        parent::init();

        $logger = new Logger();
        $action = Registry::getRequest()->getRequestParameter('action');

        switch ($action) {
            case 'review':
                $amazonSessionId = $this->getRequestAmazonSessionId();
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
}
