<?php

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;

use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;

/** @group amazonpay */
final class AmazonPaySocialLoginDeactivateCest extends BaseCest
{
    protected $amazonSocialLoginDeactivated = '#amazonSocialLoginDeactivated';
    protected $amazonSocialLoginUserMenu = '#AmazonPayWidgetCheckoutUser';
    protected $amazonSocialLoginCheckoutPage = '#AmazonPayButtonCheckoutUser';


    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPaySocialLoginDeactivate
     * @throws \Exception
     */
    public function checkAmazonSocialLoginDeactivateWorks(AcceptanceTester $I)
    {
        $I->wantToTest('AmazonPay Social Login Deactivate');
        $this->initializeTest();
        $this->openAccountMenu();
        $I->seeElement($this->amazonSocialLoginUserMenu);

        $this->addProductToBasket();
        $this->_openCheckout();
        $I->seeElement($this->amazonSocialLoginCheckoutPage);

        // deactivate option
        $I->openNewTab();
        $this->loginAdmin();
        $this->openAdminAmazonPayConfig();
        $I->scrollTo($this->amazonSocialLoginDeactivated);
        $I->checkOption($this->amazonSocialLoginDeactivated);
        $I->submitForm('.amazonpay-config form', []);
        $I->waitForDocumentReadyState();
        $error = $this->grabTextFromElementWhenPresent('.alert-danger');
        if ('' !== $error) {
            $I->fail('Error on saving amazon module config: ' . $error);
        }
        $I->waitForElement('.alert-success', 60);
        $I->switchToPreviousTab();

        $I->reloadPage();
        $this->openAccountMenu();
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
        $this->openAdminAmazonPayConfig();
        $I->scrollTo($this->amazonSocialLoginDeactivated);
        $I->uncheckOption($this->amazonSocialLoginDeactivated);
        $I->submitForm('.amazonpay-config form', []);
        $I->waitForDocumentReadyState();
        $error = $this->grabTextFromElementWhenPresent('.alert-danger');
        if ('' !== $error) {
            $I->fail('Error on saving amazon module config: ' . $error);
        }
        $I->waitForElement('.alert-success', 60);
        $I->closeTab();
    }
}
