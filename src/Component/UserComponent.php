<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Component;

use Exception;
use OxidEsales\Eshop\Application\Model\DeliverySetList;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Core\Registry;
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
     * @throws Exception
     */
    public function createGuestUser(array $amazonSession)
    {
        $session = Registry::getSession();
        $config = new Config();

        $this->setParent(oxNew(RegisterController::class));

        $this->setRequestParameterString('userLoginName', $this->_getNameFromAmazonResponse($amazonSession));
        $this->setRequestParameterString('lgn_usr', $this->_getEMailFromAmazonResponse($amazonSession));

        // Guest users have a blank password
        $password = '';
        $this->setRequestParameterString('lgn_pwd', $password);
        $this->setRequestParameterString('lgn_pwd2', $password);
        $this->setRequestParameterString('lgn_pwd2', $password);

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
        $this->setRequestParameterArray('invadr', $billingAddress);

        // handle shipping address (if provided by amazon)
        if ($amazonShippingAddress) {
            $deliveryAddress = Address::mapAddressToDb($amazonShippingAddress, 'oxaddress__');
            $session->setVariable(Constants::SESSION_DELIVERY_ADDR, $deliveryAddress);
        }

        $userCreated = $this->createUser();
        if ($userCreated) {
            $basket = $session->getBasket();
            $user = $this->getUser();
            $countryOxId = $user->getActiveCountry();

            $deliverySetList = Registry::get(DeliverySetList::class)
                ->getDeliverySetList($user, $countryOxId);
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
            return;
        }

        OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        Registry::getUtils()->redirect(
            Registry::getConfig()->getShopHomeUrl() . 'cl=user',
            false
        );
    }

    /**
     * @param string $paramName
     * @param mixed $paramValue
     */
    public function setRequestParameterString(string $paramName, string $paramValue)
    {
        $_POST[$paramName] = $paramValue;
    }

    public function setRequestParameterArray(string $paramName, array $paramValue)
    {
        $_POST[$paramName] = $paramValue;
    }

    /**
     * @inheritdoc
     */
    public function logout(): string
    {
        // destroy Amazon Session
        OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        return parent::logout();
    }

    /**
     * Returns delivery address from request. Before returning array is checked if
     * all needed data is there
     *
     * @return array
     * @deprecated underscore prefix violates PSR12, will be renamed to "getDelAddressData" in next major
     */
    protected function _getDelAddressData(): array // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $session = Registry::getSession();
        if (
            $session->getVariable('paymentid') !== Constants::PAYMENT_ID ||
            !$session->getVariable(Constants::SESSION_DELIVERY_ADDR)
        ) {
            return parent::_getDelAddressData();
        }
        $aDelAddress = [];
        $aSessionDelAddress = (array)$session->getVariable(Constants::SESSION_DELIVERY_ADDR);
        if (count($aSessionDelAddress)) {
            $aDelAddress = $aSessionDelAddress;
        }
        return $aDelAddress;
    }

    protected function _getNameFromAmazonResponse(array $amazonSession): string
    {
        if (array_key_exists('buyer', $amazonSession['response'])) {
            return $amazonSession['response']['buyer']['name'];
        }

        return $amazonSession['response']['name'];
    }

    protected function _getEMailFromAmazonResponse(array $amazonSession): string
    {
        if (array_key_exists('buyer', $amazonSession['response'])) {
            return $amazonSession['response']['buyer']['email'];
        }

        return $amazonSession['response']['email'];
    }
}
