<?php

namespace OxidEsales\EshopCommunity\modules\osc\amazonpay\src\Service;

use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Registry;

/**
 * PSAPC-176 if
 *
 * - the customer is on the checkout page
 * - and pays with amazon pay
 * - and click the checkbox to confirm terms and conditions
 *
 * we need to remember this decision
 */
class TermsAndConditionService
{
    const SESSION_VAR_NAME_CONFIRM_AGB = 'payamazon_confirm_agb';
    /** @var object|Session */
    private $session;
    /** @var object|Session */
    private $request;

    public function __construct()
    {
        $this->request = Registry::getRequest();
        $this->session = Registry::getSession();
    }
    public function setConfirmFromRequestToSession()
    {
        $this->session->setVariable(
            self::SESSION_VAR_NAME_CONFIRM_AGB,
            (bool) $this->request->getRequestParameter('confirm')
        );
    }
    public function getConfirmFromSession(): bool
    {
        return (bool) $this->session->getVariable(self::SESSION_VAR_NAME_CONFIRM_AGB);
    }
}
