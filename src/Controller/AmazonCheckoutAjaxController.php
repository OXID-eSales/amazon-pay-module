<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * handles amazon checkout ajax calls
 */
class AmazonCheckoutAjaxController extends FrontendController
{
    public function confirmAGB(): void
    {
        $conditionsService = OxidServiceProvider::getTermsAndConditionService();
        $conditionsService->setAGBConfirmFromRequestToSession();
        $this->_aViewData['jsonResponse'] = json_encode(['success' => true]);
    }
    public function confirmDPA(): void
    {
        $conditionsService = OxidServiceProvider::getTermsAndConditionService();
        $conditionsService->setDPAConfirmFromRequestToSession();
        $this->_aViewData['jsonResponse'] = json_encode(['success' => true]);
    }
    public function confirmSPA(): void
    {
        $conditionsService = OxidServiceProvider::getTermsAndConditionService();
        $conditionsService->setSPAConfirmFromRequestToSession();
        $this->_aViewData['jsonResponse'] = json_encode(['success' => true]);
    }
    public function render()
    {
        return 'amazonpay/json.tpl';
    }
}
