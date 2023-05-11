<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\AmazonPay\Service\TermsAndConditionService;

/**
 * handles amazon checkout ajax calls
 */
class AmazonCheckoutAjaxController extends FrontendController
{
    /** @var TermsAndConditionService */
    private $conditionsService;

    public function __construct()
    {
        parent::__construct();
        $this->conditionsService = ContainerFactory::getInstance()->getContainer()->get(TermsAndConditionService::class);
    }
    public function confirmAGB()
    {
        $this->conditionsService->setAGBConfirmFromRequestToSession();
        $this->_aViewData['jsonResponse'] = json_encode(['success' => true]);
    }

    public function confirmDPA()
    {
        $this->conditionsService->setDPAConfirmFromRequestToSession();
        $this->_aViewData['jsonResponse'] = json_encode(['success' => true]);
    }

    public function confirmSPA()
    {
        $this->conditionsService->setSPAConfirmFromRequestToSession();
        $this->_aViewData['jsonResponse'] = json_encode(['success' => true]);
    }


    public function render()
    {
        return 'amazonpay/json.tpl';
    }
}
