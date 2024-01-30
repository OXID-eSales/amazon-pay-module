<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * Class for validating input
 */
class AmazonInputValidator extends AmazonInputValidator_parent
{
    /**
     * Checks if username does not break logic:
     *  - if user wants to UPDATE his login name, performing check if
     *    user entered correct password
     *  - additionally checking for username duplicates. This is usually
     *    needed when creating new users.
     * On any error exception is thrown.
     *
     * @param $user
     * @param $login
     * @param $invAddress
     * @return string login name
     */
    public function checkLogin($user, $login, $invAddress): string
    {
        $login = $invAddress['oxuser__oxusername'] ?? $login;

        $service = OxidServiceProvider::getAmazonService();

        if ($service->isAmazonSessionActive() && $user->checkIfEmailExists($login)) {
            //if exists then we do not allow to do that

            /** @var string $userExistsMessage */
            $userExistsMessage = Registry::getLang()->translateString('AMAZON_PAY_USEREXISTS');
            $oEx = oxNew(
                UserException::class,
                sprintf(
                    $userExistsMessage,
                    $login,
                    $login
                )
            );

            return $this->addValidationError("oxuser__oxusername", $oEx);
        }

        return parent::checkLogin($user, $login, $invAddress);
    }

    /**
     * Disabling validation for Amazon addresses when Amazon Pay is active
     *
     * @param User $user Active user.
     * @param array $billingAddress  Billing address.
     * @param array $deliveryAddress Delivery address.
     * TODO: check if typehint can be used in Oxid 7
     */
    public function checkRequiredFields($user, $billingAddress, $deliveryAddress)
    {
        $service = OxidServiceProvider::getAmazonService();

        if (!$service->isAmazonSessionActive()) {
            parent::checkRequiredFields($user, $billingAddress, $deliveryAddress);
        }
    }
}
