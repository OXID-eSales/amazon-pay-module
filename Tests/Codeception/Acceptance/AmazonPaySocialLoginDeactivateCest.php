<?php

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;

use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;

final class AmazonPaySocialLoginDeactivateCest extends BaseCest
{
    protected string $amazonSocialLoginDeactivated = '#amazonSocialLoginDeactivated';
    protected string $amazonSocialLoginUserMenu = '#AmazonPayWidgetCheckoutUser';
    protected string $amazonSocialLoginCheckoutPage = '#AmazonPayButtonCheckoutUser';


    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPaySocialLoginDeactivate
     * @throws \Exception
     */
    public function checkAmazonSocialLoginDeactivateWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('AmazonPay Social Login Deactivate');
        $this->_openAccountMenu();
        $I->seeElement($this->amazonSocialLoginUserMenu);

        $this->_addProductToBasket();
        $this->_openCheckout();
        $I->seeElement($this->amazonSocialLoginCheckoutPage);

        // deactivate option
        $I->openNewTab();
        $this->_loginAdmin();
        $this->_openAdminAmazonPayConfig();
        $I->scrollTo($this->amazonSocialLoginDeactivated);
        $I->checkOption($this->amazonSocialLoginDeactivated);
        $I->submitForm('.amazonpay-config form', []);
        $I->waitForDocumentReadyState();
        $error = $this->_grabTextFromElementWhenPresent('.alert-danger');
        if ($error) {
            $I->fail('Error on saving amazon module config: ' . $error);
        }
        $I->waitForElement('.alert-success', 60);
        $I->switchToPreviousTab();

        $I->reloadPage();
        $this->_openAccountMenu();
        try {
            $I->dontSeeElement($this->amazonSocialLoginUserMenu);
        } catch (\Exception $e) {
            $I->fail('Social login in the account menu should be absent, but is still there');
        }

        try {
            $I->dontSeeElement($this->amazonSocialLoginCheckoutPage);
        } catch (\Exception $e) {
            $I->fail('Social login in the account menu should be absent, but is still there');
        }

        // reactivate option for following tests
        $I->switchToNextTab();
        $I->reloadPage();
        $this->_openAdminAmazonPayConfig();
        $I->scrollTo($this->amazonSocialLoginDeactivated);
        $I->uncheckOption($this->amazonSocialLoginDeactivated);
        $I->submitForm('.amazonpay-config form', []);
        $I->waitForDocumentReadyState();
        $error = $this->_grabTextFromElementWhenPresent('.alert-danger');
        if ($error) {
            $I->fail('Error on saving amazon module config: ' . $error);
        }
        $I->waitForElement('.alert-success', 60);
        $I->wait(30);
        $I->closeTab();
    }
}
