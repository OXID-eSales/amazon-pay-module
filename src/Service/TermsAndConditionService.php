<?php

namespace OxidSolutionCatalysts\AmazonPay\Service;

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
    const SESSION_VAR_NAME_CONFIRM_DPA = 'payamazon_confirm_dpa';
    const SESSION_VAR_NAME_CONFIRM_SPA = 'payamazon_confirm_spa';
    /** @var object|Session */
    private $session;
    /** @var object|Session */
    private $request;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct()
    {
        $this->request = Registry::getRequest();
        $this->session = Registry::getSession();
    }
    public function setAGBConfirmFromRequestToSession()
    {
        $this->session->setVariable(
            self::SESSION_VAR_NAME_CONFIRM_AGB,
            (bool) $this->request->getRequestParameter('confirm')
        );
    }
    public function getAGBConfirmFromSession(): bool
    {
        return (bool) $this->session->getVariable(self::SESSION_VAR_NAME_CONFIRM_AGB);
    }

    public function setDPAConfirmFromRequestToSession()
    {
        $this->session->setVariable(
            self::SESSION_VAR_NAME_CONFIRM_DPA,
            (bool) $this->request->getRequestParameter('confirm')
        );
    }
    public function getDPAConfirmFromSession(): bool
    {
        return (bool) $this->session->getVariable(self::SESSION_VAR_NAME_CONFIRM_DPA);
    }
    public function setSPAConfirmFromRequestToSession()
    {
        $this->session->setVariable(
            self::SESSION_VAR_NAME_CONFIRM_SPA,
            (bool) $this->request->getRequestParameter('confirm')
        );
    }
    public function getSPAConfirmFromSession(): bool
    {
        return (bool) $this->session->getVariable(self::SESSION_VAR_NAME_CONFIRM_SPA);
    }
    public function resetConfirmOnGet()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->session->deleteVariable(self::SESSION_VAR_NAME_CONFIRM_AGB);
            $this->session->deleteVariable(self::SESSION_VAR_NAME_CONFIRM_DPA);
            $this->session->deleteVariable(self::SESSION_VAR_NAME_CONFIRM_SPA);
        }
    }
}
