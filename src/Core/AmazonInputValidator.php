<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidProfessionalServices\AmazonPay\Core\AmazonService;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * Class for validating input
 */
class AmazonInputValidator extends AmazonInputValidator_parent
{
    /**
     * Checks if user name does not break logic:
     *  - if user wants to UPDATE his login name, performing check if
     *    user entered correct password
     *  - additionally checking for user name duplicates. This is usually
     *    needed when creating new users.
     * On any error exception is thrown.
     *
     * @param \OxidEsales\Eshop\Application\Model\User $oUser       active user
     * @param string                                   $sLogin      user preferred login name
     * @param array                                    $aInvAddress user information
     *
     * @return string login name
     */
    public function checkLogin($oUser, $sLogin, $aInvAddress)
    {
        $sLogin = $aInvAddress['oxuser__oxusername'] ?? $sLogin;

        $service = OxidServiceProvider::getAmazonService();

        if ($service->isAmazonSessionActive() && $oUser->checkIfEmailExists($sLogin)) {
            //if exists then we do not allow to do that
            $oEx = oxNew(UserException::class);

            $oEx->setMessage(sprintf(
                Registry::getLang()->translateString('AMAZON_PAY_USEREXISTS'),
                $sLogin,
                $sLogin
            ));

            return $this->_addValidationError("oxuser__oxusername", $oEx);
        }

        return parent::checkLogin($oUser, $sLogin, $aInvAddress);
    }

    /**
     * Disabling validation for Amazon addresses when Amazon Pay is active
     *
     * @param User  $user            Active user.
     * @param array $billingAddress  Billing address.
     * @param array $deliveryAddress Delivery address.
     */
    public function checkRequiredFields($user, $billingAddress, $deliveryAddress)
    {
        $service = OxidServiceProvider::getAmazonService();

        if (!$service->isAmazonSessionActive()) {
            return parent::checkRequiredFields($user, $billingAddress, $deliveryAddress);
        }
    }
}
