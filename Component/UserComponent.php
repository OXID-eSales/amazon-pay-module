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

namespace OxidProfessionalServices\AmazonPay\Component;

use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\Helper\Address;

/**
 * Handles Amazon checkout sessions
 * @mixin \OxidEsales\Eshop\Application\Component\UserComponent
 */
class UserComponent extends UserComponent_Parent
{
    /**
     * @param array $amazonSession
     */
    public function createGuestUser(array $amazonSession): void
    {
        $this->setParent(oxNew('Register'));

        $this->setRequestParameter('userLoginName', $amazonSession['response']['buyer']['name']);
        $this->setRequestParameter('lgn_usr', $amazonSession['response']['buyer']['email']);

        $password = $this->generateRandomPassword();

        $this->setRequestParameter('lgn_pwd', $password);
        $this->setRequestParameter('lgn_pwd2', $password);
        $this->setRequestParameter('passwordLength', $password);
        $this->setRequestParameter('userPasswordConfirm', $password);

        $this->setRequestParameter('invadr', Address::mapAddressToDb($amazonSession['response']['billingAddress']));
        $this->setRequestParameter('deladr', Address::mapAddressToDb($amazonSession['response']['shippingAddress']));

        $registrationResult = $this->registerUser();

        if ($registrationResult) {
            $this->login_noredirect();
            Registry::getSession()->getBasket()->setPayment('oxidamazon');
        } else {
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
     * @return string
     */
    private function generateRandomPassword(): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $pass[] = $alphabet[rand(0, $alphaLength)];
        }
        return implode($pass);
    }
}
