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
}
