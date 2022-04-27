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

                Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=order', true, 302);
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
