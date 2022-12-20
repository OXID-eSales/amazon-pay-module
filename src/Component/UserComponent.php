<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Component;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Application\Model\DeliverySetList;
use OxidEsales\EshopCommunity\Application\Controller\RegisterController;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\Address;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * Handles Amazon checkout sessions
 * @mixin \OxidEsales\Eshop\Application\Component\UserComponent
 */
class UserComponent extends UserComponent_parent
{
    /**
     * @param array $amazonSession
     */
    public function createGuestUser(array $amazonSession): void
    {
        $session = Registry::getSession();
        $config = new Config();

        $this->setParent(oxNew(RegisterController::class));

        $this->setRequestParameter('userLoginName', $amazonSession['response']['buyer']['name']);
        $this->setRequestParameter('lgn_usr', $amazonSession['response']['buyer']['email']);

        // Guest users have a blank password
        $password = '';
        $this->setRequestParameter('lgn_pwd', $password);
        $this->setRequestParameter('lgn_pwd2', $password);
        $this->setRequestParameter('lgn_pwd2', $password);

        $amazonBillingAddress = $amazonSession['response']['billingAddress'];
        $amazonShippingAddress = $amazonSession['response']['shippingAddress'];

        // Amazon has no way of restricting the country of the billing address to the countries of the OXID shop.
        // This option is only available for the billing address. That's why we double-check the country of the
        // billing address. If this does not fit, we will use the validated delivery address as the billing address
        if (
            !array_key_exists($amazonBillingAddress['countryCode'], $config->getPossibleEUAddresses()) &&
            $amazonShippingAddress
        ) {
            $amazonBillingAddress = $amazonShippingAddress;
            Registry::getUtilsView()->addErrorToDisplay('AMAZON_PAY_BILLINGCOUNTRY_MISMATCH', false, true);
        }

        // handle billing address
        $billingAddress = Address::mapAddressToDb($amazonBillingAddress, 'oxuser__');
        $this->setRequestParameter('invadr', $billingAddress);

        // handle shipping address (if provided by amazon)
        if ($amazonShippingAddress) {
            $deliveryAddress = Address::mapAddressToDb($amazonShippingAddress, 'oxaddress__');
            $session->setVariable(Constants::SESSION_DELIVERY_ADDR, $deliveryAddress);
        }

        if ($this->createUser() !== false) {
            $basket = $session->getBasket();
            $user = $this->getUser();
            $countryOxId = $user->getActiveCountry();

            $deliverySetList = Registry::get(DeliverySetList::class)
            ->getDeliverySetList(
                $user,
                $countryOxId
            );
            $possibleDeliverySets = [];
            foreach ($deliverySetList as $deliverySet) {
                $paymentList = Registry::get(PaymentList::class)->getPaymentList(
                    $deliverySet->getId(),
                    $basket->getPrice()->getBruttoPrice(),
                    $user
                );
                if (array_key_exists(Constants::PAYMENT_ID_EXPRESS, $paymentList)) {
                    $possibleDeliverySets[] = $deliverySet->getId();
                }
            }

            if (count($possibleDeliverySets)) {
                $basket->setPayment(Constants::PAYMENT_ID_EXPRESS);
                $basket->setShipping(reset($possibleDeliverySets));
            }
        } else {
            OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=user', false, 302);
        }
    }

    /**
     * @param string $paramName
     * @param mixed $paramValue
     */
    public function setRequestParameter(string $paramName, $paramValue): void
    {
        $_POST[$paramName] = $paramValue;
    }

    /**
     * Deletes user information from session:<br>
     * "usr", "dynvalue", "paymentid"<br>
     * also deletes cookie, unsets \OxidEsales\Eshop\Core\Config::oUser,
     * oxcmp_user::oUser, forces basket to recalculate.
     * @return void
     */
    public function logout(): void
    {
        // destroy Amazon Session
        OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        parent::logout();
    }

    /**
     * Returns delivery address from request. Before returning array is checked if
     * all needed data is there
     *
     * @return array
     * @deprecated underscore prefix violates PSR12, will be renamed to "getDelAddressData" in next major
     */
    protected function _getDelAddressData() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $session = Registry::getSession();
        if (
            $session->getVariable('paymentid') !== Constants::PAYMENT_ID ||
            !$session->getVariable(Constants::SESSION_DELIVERY_ADDR)
        ) {
            return parent::_getDelAddressData();
        }
        $aDelAdress = [];
        $aDeladr = $session->getVariable(Constants::SESSION_DELIVERY_ADDR);
        if (count($aDeladr)) {
            $aDelAdress = $aDeladr;
        }
        return $aDelAdress;
    }
}
