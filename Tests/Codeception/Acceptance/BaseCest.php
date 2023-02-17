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

        // make sure the payments are active
        $I->updateInDatabase(
            'oxpayments',
            ['OXACTIVE' => 1],
            ['OXID' => 'oxidamazon']
        );
        $I->updateInDatabase(
            'oxpayments',
            ['OXACTIVE' => 1],
            ['OXID' => 'oxidamazonexpress']
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
        $this->I->wait(2);
    }

    protected function _openDetailPage()
    {
        $this->I->waitForText(Translator::translate('MORE_INFO'), 60);
        $this->I->click(Translator::translate('MORE_INFO'));
        $this->I->wait(2);
    }

    /**
     * @return void
     */
    protected function _loginOxid()
    {
        $homePage = $this->I->openShop();
        $this->I->waitForDocumentReadyState();
        $this->I->wait(2);
        $this->I->makeScreenshot(time() . 'beforeLogin.png');
        $homePage->loginUser($_ENV['OXID_CLIENT_USERNAME'], $_ENV['OXID_CLIENT_PASSWORD']);
        $this->I->wait(2);
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
        $this->I->fillField($loginInput, $_ENV['AMAZONPAY_CLIENT_USERNAME']);
        $this->I->fillField($passwordInput, $_ENV['AMAZONPAY_CLIENT_PASSWORD']);
        $this->I->click($loginButton);
        $this->I->wait(2);

        $this->I->waitForPageLoad();
        $this->I->waitForElement($continueButton, 60);
        $this->I->clickWithLeftButton($continueButton);
        $this->I->wait(2);
    }

    /**
     * @return void
     */
    protected function _openCheckout()
    {
        if (!$this->homePage) {
            $this->homePage = $this->I->openShop();
            $this->I->wait(2);
        }
        $this->homePage->openMiniBasket()->openCheckout();
        $this->I->wait(2);
    }

    /**
     * @return void
     */
    protected function _openBasketDisplay()
    {
        if (!$this->homePage) {
            $this->homePage = $this->I->openShop();
            $this->I->wait(2);
        }

        $this->homePage->openMiniBasket()->openBasketDisplay();
        $this->I->wait(2);
    }

    protected function _openAccountMenu()
    {
        if (!$this->homePage) {
            $this->homePage = $this->I->openShop();
            $this->I->wait(2);
        }

        $this->homePage->openAccountMenu();
        $this->I->wait(2);
    }

    /**
     * @return void
     */
    protected function _openAmazonPayPage()
    {
        $amazonpayDiv = "//div[contains(@id, 'AmazonPayButton')]";

        $this->I->waitForElement($amazonpayDiv, 60);
        $this->I->click($amazonpayDiv);
        $this->I->wait(2);
    }

    /**
     * @return void
     */
    protected function _loginAmazonPayment()
    {
        $amazonpayLoginPage = new AmazonPayLogin($this->I);
        $amazonpayLoginPage->login();
        $this->I->wait(2);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function _checkAccountExist()
    {
        $this->I->waitForDocumentReadyState();
        $this->I->makeScreenshot(time() . 'Account already exists');
        $foundModuleMessage = false;
        try {
            $this->I->waitForText(strip_tags(sprintf(
                Translator::translate('AMAZON_PAY_USEREXISTS'),
                $_ENV['AMAZONPAY_CLIENT_USERNAME'],
                $_ENV['AMAZONPAY_CLIENT_USERNAME']
            )), 5);
            $foundModuleMessage = true;
        } catch (\Throwable $e) {
        }

        if (!$foundModuleMessage) {
            $coreMessage = strip_tags(sprintf(
                Translator::translate('ERROR_MESSAGE_USER_USEREXISTS'),
                $_ENV['AMAZONPAY_CLIENT_USERNAME']
            ));

            $this->I->waitForText($coreMessage, 5);
        }
        $this->I->wait(2);
    }

    /**
     * @return void
     */
    protected function _submitPaymentMethod()
    {
        $amazonpayInformationPage = new AmazonPayInformation($this->I);
        $amazonpayInformationPage->submitPayment();
        $this->I->wait(2);
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
        $this->I->wait(2);
    }

    /**
     * @return void
     */
    protected function _cancelPayment()
    {
        $amazonpayInformationPage = new AmazonPayInformation($this->I);
        $amazonpayInformationPage->cancelPayment();
        $this->I->wait(2);
    }

    /**
     * @return void
     */
    protected function _submitOrder()
    {
        $this->I->waitForText(Translator::translate('SUBMIT_ORDER'), 60);
        $this->I->click(Translator::translate('SUBMIT_ORDER'));
        $this->I->wait(2);
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

        $this->I->fillField($userAccountLoginName, $_ENV['OXID_ADMIN_USERNAME']);
        $this->I->fillField($userAccountLoginPassword, $_ENV['OXID_ADMIN_PASSWORD']);
        $this->I->click($userAccountLoginButton);
        $this->I->wait(2);

        $this->I->switchToFrame(null);
        $this->I->switchToFrame("basefrm");
        $this->I->waitForText(Translator::translate('NAVIGATION_HOME'), 60);
        $this->I->wait(2);
    }

    protected function _openOrder(string $orderNumber): void
    {
        $this->_loginAdmin();
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
        $this->I->wait(2);
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
        $this->I->waitForElementVisible('[name="nav_amazonconfig"]', 60);
        $this->I->see(Translator::translate("OSC_AMAZONPAY_CONFIG"));
        $this->I->click(Translator::translate("OSC_AMAZONPAY_CONFIG"));
        $this->I->waitForDocumentReadyState();

        $this->I->switchToFrame(null);
        $this->I->switchToFrame("basefrm");
        $this->I->wait(2);
    }
}
