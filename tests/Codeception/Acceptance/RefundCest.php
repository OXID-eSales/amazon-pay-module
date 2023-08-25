<?php

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;
namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Module\Translation\Translator;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;

final class RefundCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     * @return void
     * @throws \Exception
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

        $this->_openOrder($orderNumber);
        $I->switchToFrame("edit");
        $I->see(Translator::translate("GENERAL_ORDERNUM") . ': ' . $orderNumber);
        $I->see(Translator::translate("ORDER_OVERVIEW_INTSTATUS") . ': ' . $orderStatus);
        $I->see(Translator::translate("OSC_AMAZONPAY_REMARK") . ': ' . $orderRemark);

        $I->waitForElement("//input[@name='refundButton']", 60);
        $I->click("//input[@name='refundButton']");

        $orderStatus = $I->grabFromDatabase('oxorder', 'oxtransstatus', ['OXORDERNR' => $orderNumber]);
        $orderRemark = $I->grabFromDatabase('oxorder', 'osc_amazon_remark', ['OXORDERNR' => $orderNumber]);

        $I->see(Translator::translate("GENERAL_ORDERNUM") . ': ' . $orderNumber);
        $I->see(Translator::translate("ORDER_OVERVIEW_INTSTATUS") . ': ' . $orderStatus);
        $I->see(Translator::translate("OSC_AMAZONPAY_REMARK") . ': ' . $orderRemark);
    }
}
