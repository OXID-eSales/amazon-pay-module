<?php

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;
namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;

use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;

final class RefundCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     * @return void
     * @group RefundTest
     */
    public function checkRefundPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test refund payment');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxid();
        $this->_openBasketDisplay();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_submitPaymentMethod();
        $this->_submitOrder();
        $orderNumber = $this->_checkSuccessfulPayment();

        $data = $this->_checkDatabase($orderNumber);
        $this->_openOrder($orderNumber);
        $this->_checkDataOnAdminPage($data);

        $I->waitForElement("//input[@name='refundButton']", 60);
        $I->click("//input[@name='refundButton']");

        $data = $this->_checkDatabase($orderNumber);
        $this->_checkDataOnAdminPage($data);
    }
}
