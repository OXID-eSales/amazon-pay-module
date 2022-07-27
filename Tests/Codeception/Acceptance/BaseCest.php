<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidProfessionalServices\AmazonPay\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\AcceptanceTester;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\Page\AcceptSSLCertificate;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\Page\AmazonPayInformation;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\Page\AmazonPayLogin;

abstract class BaseCest
{
    private int $amount = 1;
    private AcceptanceTester $I;

    public function _before(AcceptanceTester $I): void
    {
        $I->haveInDatabase(
            'oxobject2payment',
            ['OXID' => 'testAmazonPay',
                'OXOBJECTID' => 'a7c40f631fc920687.20179984',
                'OXPAYMENTID' => 'oxidamazon',
                'OXTYPE' => 'oxcountry'
            ]
        );

        $this->I = $I;
    }

    public function _after(AcceptanceTester $I): void
    {
        $I->clearShopCache();
    }

    /**
     * @return void
     */
    protected function _initializeTest()
    {
        $this->I->openShop();

        $acceptCertificatePage = new AcceptSSLCertificate($this->I);
        $acceptCertificatePage->acceptCertificate();

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
    }

    /**
     * @return void
     */
    protected function _loginOxid()
    {
        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);
    }

    /**
     * @return void
     */
    protected function _openCheckout()
    {
        $homePage = $this->I->openShop();
        $homePage->openMiniBasket()->openCheckout();
    }

    /**
     * @return void
     */
    protected function _openBasketDisplay()
    {
        $homePage = $this->I->openShop();
        $homePage->openMiniBasket()->openBasketDisplay();
    }

    /**
     * @return void
     */
    protected function _openAmazonPayPage()
    {
        $amazonpayDiv = "//div[contains(@id, 'AmazonPayButton')]";

        $this->I->waitForElement($amazonpayDiv);
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
     */
    protected function _submitPaymentMethod()
    {
        $amazonpayInformationPage = new AmazonPayInformation($this->I);
        $amazonpayInformationPage->submitPayment();
    }

    /**
     * @return void
     */
    protected function _cancelPeyment()
    {
        $amazonpayInformationPage = new AmazonPayInformation($this->I);
        $amazonpayInformationPage->cancelPayment();
    }

    /**
     * @return void
     */
    protected function _submitOrder()
    {
        $this->I->waitForText(Translator::translate('SUBMIT_ORDER'));
        $this->I->click(Translator::translate('SUBMIT_ORDER'));
    }

    /**
     * @return void
     */
    protected function _checkSuccessfulPayment()
    {
        $this->I->waitForText(Translator::translate('THANK_YOU'));
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    protected function _setAcceptance(AcceptanceTester $I)
    {
        $this->I = $I;
    }

    /**
     * @return AcceptanceTester
     */
    protected function _getAcceptance(): AcceptanceTester
    {
        return $this->I;
    }

    /**
     * @return string price of order
     */
    protected function _getPrice(): string
    {
        $basketItem = Fixtures::get('product');
        return Registry::getLang()->formatCurrency(
            $basketItem['bruttoprice_single'] * $this->amount + $basketItem['shipping_cost']
        );
    }

    /**
     * @return string currency
     */
    protected function _getCurrency(): string
    {
        $basketItem = Fixtures::get('product');
        return $basketItem['currency'];
    }
}
