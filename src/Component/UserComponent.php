<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Component;

use Exception;
use OxidEsales\Eshop\Application\Model\DeliverySetList;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Controller\RegisterController;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\Address;
use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use Psr\Log\LogLevel;
use Throwable;

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
    public function createGuestUser(array $amazonSession): void
    {
        $session = Registry::getSession();
        $config = new Config();
        $logger = new Logger();

        $this->setParent(oxNew(RegisterController::class));

        $this->setRequestParameterString('userLoginName', $this->_getNameFromAmazonResponse($amazonSession));
        $this->setRequestParameterString('lgn_usr', $this->getEMailFromAmazonResponse($amazonSession));

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
            !array_key_exists($amazonBillingAddress['countryCode'], $config->getPossibleAddresses()) &&
            $amazonShippingAddress
        ) {
            if ($config->getAmazonPayLogging()) {
                $logger->log(
                    LogLevel::DEBUG,
                    Registry::getLang()->translateString(
                        'AMAZON_PAY_BILLINGCOUNTRY_MISMATCH',
                        1
                    ) . PHP_EOL .
                    'Billing address countryCode was: ' . $amazonBillingAddress['countryCode'] . PHP_EOL .
                    'Shipping address countryCode was: ' . $amazonShippingAddress['countryCode'] . PHP_EOL .
                    'Allowed countries: ' . implode(', ', $config->getCountryList()) . PHP_EOL
                );
            }
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
     * Sign the customer into an existing shop account when the email address
     * Amazon returned for the buyer matches that account. Used by both Amazon
     * entry points (sign-in button and express checkout) before a guest user is
     * created, because a matching account makes the guest creation fail with
     * AMAZON_PAY_USEREXISTS.
     *
     * Feature gate and sign-in effect deliberately live in this single method:
     * splitting the decision "a password check may be skipped now" across a
     * controller flag check and a lower-level session-state check is what caused
     * the authentication bypass in the PayPal module (see its security
     * bulletin). Therefore:
     *
     * - The email address must come from a response the module fetched from the
     *   Amazon API in this request (getBuyer()/getCheckoutSession()), never from
     *   a request parameter.
     * - No session state ("an amazon payment is active") is used as an
     *   authentication criterion, and the shop password path
     *   (User::login()/onLogin()) is not touched at all.
     * - The lookup is restricted to active, non-admin accounts of the current
     *   shop, so admin accounts can never be signed in via a matching email.
     * - The session challenge is required, exactly like in the guest creation
     *   path this method runs in front of (UserComponent::createUser()).
     *
     * @param array $amazonResponse buyer or checkout session response from Amazon
     * @return bool true if a customer was signed in
     * @throws Exception
     */
    public function loginAmazonCustomer(array $amazonResponse): bool
    {
        $config = new Config();
        $mode = $config->getLoginByEMailMode();
        if ($mode === Constants::LOGIN_BY_EMAIL_OFF) {
            return false;
        }

        $session = Registry::getSession();
        if (!$session->checkSessionChallenge()) {
            return false;
        }

        $user = $this->resolveShopUserForAmazonSignIn($amazonResponse, $mode);
        if ($user === null) {
            return false;
        }

        // new session id for the authenticated session, as the shop login does
        // in UserComponent::_afterLogin(); session variables are kept
        if ($session->isSessionStarted()) {
            $session->regenerateSessionId();
        }

        $session->setVariable('usr', $user->getId());
        $this->setUser($user);
        $this->setLoginStatus(USER_LOGIN_SUCCESS);

        // customer group prices and discounts differ from the guest calculation
        $session->getBasket()->onUpdate();

        $this->logSignInByEMail($user, $mode);

        return true;
    }

    /**
     * The account the Amazon-supplied email address belongs to, or null when
     * there is nothing to sign in. Purely reading, the sign-in effect stays in
     * loginAmazonCustomer() together with the feature gate.
     *
     * @param array $amazonResponse buyer or checkout session response from Amazon
     * @param string $mode one of the Constants::LOGIN_BY_EMAIL_* modes
     * @return User|null
     */
    protected function resolveShopUserForAmazonSignIn(array $amazonResponse, string $mode): ?User
    {
        $email = $this->getEMailFromAmazonResponse($amazonResponse);
        if ($email === '') {
            return null;
        }

        $userId = $this->getShopUserIdByEMail($email, $mode);
        if ($userId === '') {
            return null;
        }

        /** @var User $user */
        $user = oxNew(User::class);
        if (!$user->load($userId)) {
            return null;
        }

        // blocked customers must not get a session, they are not allowed to order
        if ($user->inGroup('oxidblocked')) {
            return null;
        }

        return $user;
    }

    /**
     * A sign-in without a password entry is always logged, independently of the
     * module's debug logging setting. The email address is not logged, the user
     * id identifies the account. A failing logger must not break the checkout of
     * an already signed-in customer.
     *
     * @param User $user
     * @param string $mode one of the Constants::LOGIN_BY_EMAIL_* modes
     * @return void
     */
    protected function logSignInByEMail(User $user, string $mode): void
    {
        try {
            $logger = new Logger();
            $logger->log(
                LogLevel::INFO,
                sprintf(
                    'Signed customer in via the Amazon email address (mode: %s)',
                    $mode
                ),
                ['userId' => $user->getId(), 'requestType' => 'amazonpay-login-by-email']
            );
        } catch (Throwable $throwable) {
            // nothing we can do here, the customer is signed in
        }
    }

    /**
     * Look up the shop account for an email address Amazon verified. Only
     * active customer accounts of the current shop are considered; in guest-only
     * mode accounts holding a password are skipped as well, so that no existing
     * password protection can be bypassed.
     *
     * @param string $email
     * @param string $mode one of the Constants::LOGIN_BY_EMAIL_* modes
     * @return string user id, empty string if there is no matching account
     */
    protected function getShopUserIdByEMail(string $email, string $mode): string
    {
        $select = "SELECT OXID FROM oxuser
                   WHERE oxusername = :oxusername
                     AND oxrights   = 'user'
                     AND oxactive   = 1
                     AND oxshopid   = :oxshopid";

        if ($mode !== Constants::LOGIN_BY_EMAIL_ALL) {
            $select .= " AND (oxpassword = '' OR oxpassword IS NULL)";
        }

        return (string)DatabaseProvider::getDb()->getOne(
            $select,
            [
                ':oxusername' => $email,
                ':oxshopid'   => Registry::getConfig()->getShopId(),
            ]
        );
    }

    /**
     * @param string $paramName
     * @param string $paramValue
     *
     * @return void
     */
    public function setRequestParameterString(string $paramName, string $paramValue)
    {
        $_POST[$paramName] = $paramValue;
    }

    public function setRequestParameterArray(string $paramName, array $paramValue): void
    {
        $_POST[$paramName] = $paramValue;
    }

    /**
     * @inheritdoc
     */
    public function logout()
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
    protected function _getDelAddressData() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
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

    /**
     * Email address of the buyer as returned by the Amazon API. Both response
     * shapes are supported: the buyer response of the sign-in flow and the
     * checkout session response of the express flow.
     *
     * @param array $amazonResponse
     * @return string empty string if the response carries no email address
     */
    protected function getEMailFromAmazonResponse(array $amazonResponse): string
    {
        $response = $amazonResponse['response'] ?? [];
        $email = $response['buyer']['email'] ?? $response['email'] ?? '';

        return trim((string)$email);
    }

    /**
     * @param array $amazonSession
     * @return string
     * @deprecated underscore prefix violates PSR12, use getEMailFromAmazonResponse instead
     */
    protected function _getEMailFromAmazonResponse(array $amazonSession): string
    {
        return $this->getEMailFromAmazonResponse($amazonSession);
    }
}
