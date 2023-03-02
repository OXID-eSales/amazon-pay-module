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
use OxidEsales\Codeception\Page\Home;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Page\AmazonPayInformation;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Page\AmazonPayLogin;

abstract class BaseCest
{
    private int $timestampForScreenshot;
    private int $amount = 1;
    private AcceptanceTester $I;
    private Home $homePage;

    public function _before(AcceptanceTester $I): void
    {
        $this->timestampForScreenshot = time();
        $this->I = $I;
        $this->I->haveInDatabase(
            'oxobject2payment',
            [
                'OXID' => 'testAmazonPay',
                'OXOBJECTID' => 'oxidstandard',
                'OXPAYMENTID' => 'oxidamazon',
                'OXTYPE' => 'oxcountry'
            ]
        );

        // make sure the payments are active
        $this->I->updateInDatabase(
            'oxpayments',
            ['OXACTIVE' => 1],
            ['OXID' => 'oxidamazon']
        );
        $this->I->updateInDatabase(
            'oxpayments',
            ['OXACTIVE' => 1],
            ['OXID' => 'oxidamazonexpress']
        );
        $this->homePage = new Home($this->I);
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
    protected function _initializeTest(): void
    {
        $this->_openShop();
        $this->I->waitForDocumentReadyState();
        $this->I->waitForPageLoad();
    }

    /**
     * @param string $element
     * @return string
     */
    protected function _grabTextFromElementWhenPresent(string $element): string
    {
        try {
            $this->I->seeElement($element);
            $isFound = $this->I->grabTextFrom($element);
        } catch (\Exception $e) {
            $isFound = '';
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
        $errorMsg = $errorMsg ?: 'Text not found: ' . $text;
        try {
            $this->I->see($text);
        } catch (\Exception $e) {
            $this->I->fail($errorMsg);
        }
    }

    /**
     * @return void
     */
    protected function _addProductToBasket(): void
    {
        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
    }

    protected function _openDetailPage(): void
    {
        $this->I->waitForText(Translator::translate('MORE_INFO'), 60);
        $this->I->click(Translator::translate('MORE_INFO'));
    }

    /**
     * @return void
     */
    protected function _loginOxid(): void
    {
        $this->_openShop();
        $this->I->waitForDocumentReadyState();
        $this->I->wait(5);
        $this->_makeScreenshot('beforeLogin');
        $this->homePage->loginUser($_ENV['OXID_CLIENT_USERNAME'], $_ENV['OXID_CLIENT_PASSWORD']);
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
        $this->I->fillField($loginInput, $_ENV['AMAZONPAY_CLIENT_USERNAME']);
        $this->I->fillField($passwordInput, $_ENV['AMAZONPAY_CLIENT_PASSWORD']);
        $this->I->click($loginButton);

        $this->I->waitForPageLoad();
        $this->I->waitForElement($continueButton, 60);
        $this->I->clickWithLeftButton($continueButton);
    }

    /**
     * @return void
     */
    protected function _openCheckout(): void
    {
        $this->homePage->openMiniBasket()->openCheckout();
    }

    /**
     * @return void
     */
    protected function _openBasketDisplay(): void
    {
        $this->homePage->openMiniBasket()->openBasketDisplay();
    }

    protected function _openAccountMenu(): void
    {
        $this->homePage->openAccountMenu();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function _openAmazonPayPage(): void
    {
        $amazonpayDiv = "//div[contains(@id, 'AmazonPayButton')]";

        $this->I->waitForElement($amazonpayDiv, 60);
        $this->I->click($amazonpayDiv);
    }

    /**
     * @return void
     */
    protected function _loginAmazonPayment(): void
    {
        $amazonpayLoginPage = new AmazonPayLogin($this->I);
        $amazonpayLoginPage->login();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function _checkAccountExist(): void
    {
        $this->I->waitForDocumentReadyState();
        $this->I->makeScreenshot(time() . 'Account already exists');
        $acceptableErrors = [
            strip_tags(sprintf(
                Translator::translate('AMAZON_PAY_USEREXISTS'),
                $_ENV['AMAZONPAY_CLIENT_USERNAME'],
                $_ENV['AMAZONPAY_CLIENT_USERNAME']
            )),
            strip_tags(sprintf(
                Translator::translate('ERROR_MESSAGE_USER_USEREXISTS'),
                $_ENV['AMAZONPAY_CLIENT_USERNAME']
            ))
        ];

        $error = $this->_grabTextFromElementWhenPresent('.alert .alert-danger');
        if ($error !== '' && !in_array($error, $acceptableErrors)) {
            $this->I->fail('Login issue: ' . $error);
        }
    }
    /**
     * @return void
     */
    protected function _submitPaymentMethod(): void
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
    protected function _cancelPayment(): void
    {
        $amazonpayInformationPage = new AmazonPayInformation($this->I);
        $amazonpayInformationPage->cancelPayment();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function _submitOrder(): void
    {
        $this->I->executeJS('window.scrollTo(0,1600);');
        $this->_makeScreenshot('submitOrder');
        $this->I->waitForText(Translator::translate('SUBMIT_ORDER'), 60);
        $this->I->click(Translator::translate('SUBMIT_ORDER'));
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function _checkSuccessfulPayment(): string
    {
        $this->I->wait(10);
        $thankYouPage = new ThankYou($this->I);
        $this->_makeScreenshot('thankYouPage');
        $orderNumber = $thankYouPage->grabOrderNumber();
        return $orderNumber;
    }

    /**
     * @throws \Exception
     */
    protected function _loginAdmin(): void
    {
        $userAccountLoginName = '#usr';
        $userAccountLoginPassword = '#pwd';
        $userAccountLoginButton = '.btn';

        $adminLoginPage = new AdminLoginPage($this->I);
        $this->I->amOnPage($adminLoginPage->URL);

        $this->I->fillField($userAccountLoginName, $_ENV['OXID_ADMIN_USERNAME']);
        $this->I->fillField($userAccountLoginPassword, $_ENV['OXID_ADMIN_PASSWORD']);
        $this->I->click($userAccountLoginButton);
        $this->I->wait(5);

        $this->I->switchToFrame(null);
        $this->I->switchToFrame("basefrm");
        $this->I->waitForText(Translator::translate('NAVIGATION_HOME'), 60);
    }

    protected function _openOrder(string $orderNumber): void
    {
        $this->_loginAdmin();
        $this->I->wait(5);
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
        $this->I->waitForElementVisible('[name="nav_amazonconfig"]', 60);
        $this->I->see(Translator::translate("OSC_AMAZONPAY_CONFIG"));
        $this->I->click(Translator::translate("OSC_AMAZONPAY_CONFIG"));
        $this->I->waitForDocumentReadyState();

        $this->I->switchToFrame(null);
        $this->I->switchToFrame("basefrm");
    }

    protected function _makeScreenshot($suffix)
    {
        $class = get_class($this);
        $arr = explode('\\', $class);
        $className = array_pop($arr);
        $filename = sprintf('%s_%s_%s', $this->timestampForScreenshot, $className, $suffix);
        $this->I->makeScreenshot($filename);
    }

    protected function _openShop()
    {
        $this->I->amOnPage($this->homePage->URL);
    }
}
