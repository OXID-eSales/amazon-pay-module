<?php

use OxidSolutionCatalysts\AmazonPay\Core\Logger;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance\BaseCest;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;
use \OxidEsales\Codeception\Module\Translation\Translator;

final class RefundCest extends BaseCest {
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

        $orderStatus = $I->grabFromDatabase('oxorder', 'oxtransstatus', ['OXORDERNR' => $orderNumber]);
        $orderRemark = $I->grabFromDatabase('oxorder', 'osc_amazon_remark', ['OXORDERNR' => $orderNumber]);

        $this->_openOrderPayPal($orderNumber);
        $I->switchToFrame("edit");
        $I->see(Translator::translate("GENERAL_ORDERNUM") . ': ' . $orderNumber);
        $I->see(Translator::translate("ORDER_OVERVIEW_INTSTATUS") . ': ' . $orderStatus);
        $I->see(Translator::translate("OSC_AMAZONPAY_REMARK") . ': ' . $orderRemark);

        $I->waitForElement("//input[@name='refundButton']");
        $I->click("//input[@name='refundButton']");

        $orderStatus = $I->grabFromDatabase('oxorder', 'oxtransstatus', ['OXORDERNR' => $orderNumber]);
        $orderRemark = $I->grabFromDatabase('oxorder', 'osc_amazon_remark', ['OXORDERNR' => $orderNumber]);

        $I->see(Translator::translate("GENERAL_ORDERNUM") . ': ' . $orderNumber);
        $I->see(Translator::translate("ORDER_OVERVIEW_INTSTATUS") . ': ' . $orderStatus);
        $I->see(Translator::translate("OSC_AMAZONPAY_REMARK") . ': ' . $orderRemark);
    }
}