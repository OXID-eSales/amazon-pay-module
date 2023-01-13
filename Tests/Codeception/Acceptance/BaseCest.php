<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Admin\AdminLoginPage;
use OxidEsales\Codeception\Admin\Orders;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\Checkout\ThankYou;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Page\AmazonPayInformation;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Page\AmazonPayLogin;

abstract class BaseCest
{
    private int $amount = 1;
    private AcceptanceTester $I;
    private $homePage;

    public function _before(AcceptanceTester $I): void
    {
        $I->haveInDatabase(
            'oxobject2payment',
            [
                'OXID' => 'testAmazonPay',
                'OXOBJECTID' => 'oxidstandard',
                'OXPAYMENTID' => 'oxidamazon',
                'OXTYPE' => 'oxcountry'
            ]
        );

        $this->I = $I;
    }

    public function _after(AcceptanceTester $I): void
    {
        OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        $I->clearShopCache();
        $I->cleanUp();
    }

    /**
     * @return void
     */
    protected function _initializeTest()
    {
        $this->I->openShop();
        $this->I->waitForDocumentReadyState();
        $this->I->waitForPageLoad();
    }

    /**
     * @param string $element
     * @return false|string
     */
    protected function _grabTextFromElementWhenPresent(string $element)
    {
        try {
            $this->I->seeElement($element);
            $isFound = $this->I->grabTextFrom($element);
        } catch (\Exception $e) {
            $isFound = false;
        }
        return $isFound;
    }

    /**
     * @param string $text
     * @param string $errorMsg
     * @return void
     */
    protected function _failIfTextNotSeen(string $text, string $errorMsg = ''): void
    {
        $errorMsg = $errorMsg ?? 'Text not found: ' . $text;
        try {
            $this->I->see($text);
        } catch (\Exception $e) {
            $this->I->fail($errorMsg);
        }
    }

    /**
     * @return void
     */
    protected function _addProductToBasket()
    {
        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
    }

    protected function _openDetailPage()
    {
        $this->I->waitForText(Translator::translate('MORE_INFO'), 60);
        $this->I->click(Translator::translate('MORE_INFO'));
    }

    /**
     * @return void
     */
    protected function _loginOxid()
    {
        $homePage = $this->I->openShop();
        $this->I->waitForDocumentReadyState();
        $this->I->wait(5);
        $clientData = Fixtures::get('client');
        $this->I->makeScreenshot(time() . 'beforeLogin.png');
        $homePage->loginUser($clientData['username'], $clientData['password']);
        $this->I->wait(5);
    }

    /**
     * @throws \Exception
     */
    protected function _loginOxidWithAmazonCredentials(): void
    {
        $loginInput = "//input[@name='lgn_usr' and " .
            "@class='form-control textbox js-oxValidate js-oxValidate_notEmpty']";
        $passwordInput = "//input[@name='lgn_pwd' and " .
            "@class='form-control js-oxValidate js-oxValidate_notEmpty textbox stepsPasswordbox']";
        $loginButton = "//button[@class='btn btn-primary submitButton']";
        $continueButton = "//button[@id='userNextStepTop']";

        $this->I->waitForPageLoad();
        $this->I->waitForElement($loginInput, 60);
        $this->I->fillField($loginInput, Fixtures::get('amazonClientUsername'));
        $this->I->fillField($passwordInput, Fixtures::get('amazonClientPassword'));
        $this->I->click($loginButton);

        $this->I->waitForPageLoad();
        $this->I->waitForElement($continueButton, 60);
        $this->I->clickWithLeftButton($continueButton);
    }

    /**
     * @return void
     */
    protected function _openCheckout()
    {
        if (!$this->homePage) {
            $this->homePage = $this->I->openShop();
        }
        $this->homePage->openMiniBasket()->openCheckout();
    }

    /**
     * @return void
     */
    protected function _openBasketDisplay()
    {
        if (!$this->homePage) {
            $this->homePage = $this->I->openShop();
        }

        $this->homePage->openMiniBasket()->openBasketDisplay();
    }

    protected function _openAccountMenu()
    {
        if (!$this->homePage) {
            $this->homePage = $this->I->openShop();
        }

        $this->homePage->openAccountMenu();
    }

    /**
     * @return void
     */
    protected function _openAmazonPayPage()
    {
        $amazonpayDiv = "//div[contains(@id, 'AmazonPayButton')]";

        $this->I->waitForElement($amazonpayDiv, 60);
        $this->I->click($amazonpayDiv);
    }

    /**
     * @return void
     */
    protected function _loginAmazonPayment()
    {
        $amazonpayLoginPage = new AmazonPayLogin($this->I);
        $amazonpayLoginPage->login();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function _checkAccountExist()
    {
        $this->I->waitForDocumentReadyState();
        $this->I->makeScreenshot(time() . 'Account already exists');
        $this->I->waitForText(strip_tags(sprintf(
            Translator::translate('AMAZON_PAY_USEREXISTS'),
            Fixtures::get('amazonClientUsername'),
            Fixtures::get('amazonClientUsername')
        )), 60);
    }

    /**
     * @return void
     */
    protected function _submitPaymentMethod()
    {
        $amazonpayInformationPage = new AmazonPayInformation($this->I);
        $amazonpayInformationPage->submitPayment();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function _changePaymentMethod(): void
    {
        $amazonPayment = '#payment_oxidamazon';
        $paymentNextStep = '#paymentNextStepBottom';
        $this->I->waitForElement($amazonPayment, 60);
        $this->I->click($amazonPayment);
        $this->I->click($paymentNextStep);
    }

    /**
     * @return void
     */
    protected function _cancelPayment()
    {
        $amazonpayInformationPage = new AmazonPayInformation($this->I);
        $amazonpayInformationPage->cancelPayment();
    }

    /**
     * @return void
     */
    protected function _submitOrder()
    {
        $this->I->waitForText(Translator::translate('SUBMIT_ORDER'), 60);
        $this->I->click(Translator::translate('SUBMIT_ORDER'));
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function _checkSuccessfulPayment()
    {
        $this->I->wait(10);
        $thankYouPage = new ThankYou($this->I);
        $orderNumber = $thankYouPage->grabOrderNumber();
        return $orderNumber;
    }

    /**
     * @throws \Exception
     */
    protected function _loginAdmin()
    {
        $userAccountLoginName = '#usr';
        $userAccountLoginPassword = '#pwd';
        $userAccountLoginButton = '.btn';

        $adminLoginPage = new AdminLoginPage($this->I);
        $this->I->amOnPage($adminLoginPage->URL);

        $admin = Fixtures::get('adminUser');
        $this->I->fillField($userAccountLoginName, $admin['userLoginName']);
        $this->I->fillField($userAccountLoginPassword, $admin['userPassword']);
        $this->I->click($userAccountLoginButton);
        $this->I->wait(5);

        $this->I->switchToFrame(null);
        $this->I->switchToFrame("basefrm");
        $this->I->waitForText(Translator::translate('NAVIGATION_HOME'), 60);
    }

    protected function _openOrder(string $orderNumber): void
    {
        $this->_loginAdmin();
        $this->I->wait(15);
        $this->I->switchToFrame(null);
        $this->I->switchToFrame("navigation");
        $this->I->switchToFrame("adminnav");
        $this->I->see(Translator::translate("mxorders"));
        $this->I->click(Translator::translate("mxorders"));
        $this->I->see(Translator::translate("mxdisplayorders"));
        $this->I->click(Translator::translate("mxdisplayorders"));
        $this->I->waitForDocumentReadyState();

        $orders = new Orders($this->I);
        $orders->find($orders->orderNumberInput, $orderNumber);

        $this->I->switchToFrame(null);
        $this->I->switchToFrame("basefrm");
    }

    protected function _openAdminAmazonPayConfig(): void
    {
        $this->I->switchToFrame(null);
        $this->I->switchToFrame("navigation");
        $this->I->switchToFrame("adminnav");
        try {
            $this->I->see(Translator::translate("amazonpay"));
        } catch (\Exception $e) {
            $this->I->fail('Amazon Pay menu item not found. Is the module active?');
        }
        $this->I->click(Translator::translate("amazonpay"));
        $this->I->waitForElementVisible('[name="nav_amazonconfig"]',60);
        $this->I->see(Translator::translate("OSC_AMAZONPAY_CONFIG"));
        $this->I->click(Translator::translate("OSC_AMAZONPAY_CONFIG"));
        $this->I->waitForDocumentReadyState();

        $this->I->switchToFrame(null);
        $this->I->switchToFrame("basefrm");
    }
}
