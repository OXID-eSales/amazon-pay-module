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

    public function _before(AcceptanceTester $I)
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

    public function _after(AcceptanceTester $I)
    {
        OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        $I->clearShopCache();
        $I->cleanUp();
    }

    /**
     * @return void
     */
    protected function initializeTest(): void
    {
        $this->openShop();
        $this->I->waitForDocumentReadyState();
        $this->I->waitForPageLoad();
    }

    /**
     * @param string $element
     * @return string
     */
    protected function grabTextFromElementWhenPresent(string $element): string
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
     * @return void
     */
    protected function addProductToBasket()
    {
        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
    }

    protected function openDetailPage()
    {
        $this->I->waitForText(Translator::translate('MORE_INFO'), 60);
        $this->I->click(Translator::translate('MORE_INFO'));
    }

    /**
     * @return void
     */
    protected function loginOxid()
    {
        $this->openShop();
        $this->I->waitForDocumentReadyState();
        $this->I->wait(5);
        $this->makeScreenshot('beforeLogin');
        $this->homePage->loginUser($_ENV['OXID_CLIENT_USERNAME'], $_ENV['OXID_CLIENT_PASSWORD']);
        $this->I->wait(5);
    }

    /**
     * @throws \Exception
     */
    protected function loginOxidWithAmazonCredentials()
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
    protected function _openCheckout()
    {
        $this->homePage->openMiniBasket()->openCheckout();
    }

    /**
     * @return void
     */
    protected function openBasketDisplay()
    {
        $this->homePage->openMiniBasket()->openBasketDisplay();
    }

    protected function openAccountMenu()
    {
        $this->homePage->openAccountMenu();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function openAmazonPayPage()
    {
        $amazonpayDiv = "//div[contains(@id, 'AmazonPayButton')]";

        $this->I->waitForElement($amazonpayDiv, 60);
        $this->I->click($amazonpayDiv);
    }

    /**
     * @return void
     */
    protected function loginAmazonPayment()
    {
        $amazonpayLoginPage = new AmazonPayLogin($this->I);
        $amazonpayLoginPage->login();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function checkAccountExist()
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

        $error = $this->grabTextFromElementWhenPresent('.alert .alert-danger');
        if ($error !== '' && !in_array($error, $acceptableErrors)) {
            $this->I->fail('Login issue: ' . $error);
        }
    }
    /**
     * @return void
     */
    protected function submitPaymentMethod()
    {
        $amazonpayInformationPage = new AmazonPayInformation($this->I);
        $amazonpayInformationPage->submitPayment();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function changePaymentMethod()
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
    protected function cancelPayment()
    {
        $amazonpayInformationPage = new AmazonPayInformation($this->I);
        $amazonpayInformationPage->cancelPayment();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function submitOrder()
    {
        $this->I->executeJS('window.scrollTo(0,1600);');
        $this->makeScreenshot('submitOrder');
        $this->I->waitForText(Translator::translate('SUBMIT_ORDER'), 60);
        $this->I->click(Translator::translate('SUBMIT_ORDER'));
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function checkSuccessfulPayment(): string
    {
        $this->I->wait(10);
        $thankYouPage = new ThankYou($this->I);
        $this->makeScreenshot('thankYouPage');
        $orderNumber = $thankYouPage->grabOrderNumber();
        return $orderNumber;
    }

    /**
     * @throws \Exception
     */
    protected function loginAdmin()
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

    protected function openOrder(string $orderNumber)
    {
        $this->loginAdmin();
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

    protected function openAdminAmazonPayConfig()
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

    protected function makeScreenshot($suffix)
    {
        $class = get_class($this);
        $arr = explode('\\', $class);
        $className = array_pop($arr);
        $filename = sprintf('%s_%s_%s', $this->timestampForScreenshot, $className, $suffix);
        $this->I->makeScreenshot($filename);
    }

    protected function openShop(): void
    {
        $this->I->amOnPage($this->homePage->URL);
    }
}
