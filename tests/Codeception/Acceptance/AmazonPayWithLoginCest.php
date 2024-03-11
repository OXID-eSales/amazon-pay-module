<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;

use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;

/**
 * @group amazonpay
 * */
final class AmazonPayWithLoginCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     * @return void
     * @throws \Exception
     * @group AmazonPayWithLoginPaymentTest1
     */
    public function checkPaymentFromBasketWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Basket with login payment works');

        $this->initializeTest();
        $this->addProductToBasket();
        $this->loginOxid();
        $I->makeScreenshot(time() . 'afterlogin.png');
        $I->wait(30);
        $this->openBasketDisplay();
        $this->openAmazonPayPage();
        $this->loginAmazonPayment();
        $this->submitPaymentMethod();
        $this->submitOrder();
        $this->checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithLoginPaymentTest
     * @throws \Exception
     */
    public function checkPaymentFromAddressPageWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Address Page with login payment works');

        $this->initializeTest();
        $this->addProductToBasket();
        $this->loginOxid();
        $this->_openCheckout();
        $this->changePaymentMethod();
        $this->openAmazonPayPage();
        $this->loginAmazonPayment();
        $this->submitPaymentMethod();
        $this->checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @throws \Exception
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromDetailWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Details Page with login payment works');

        $this->initializeTest();
        $this->loginOxid();
        $this->openDetailPage();
        $this->openAmazonPayPage();
        $this->loginAmazonPayment();
        $this->submitPaymentMethod();
        $this->submitOrder();
        $this->checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @throws \Exception
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromBasketWithReturnWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Basket with login and return payment works');

        $this->initializeTest();
        $this->addProductToBasket();
        $this->loginOxid();
        $this->openBasketDisplay();
        $this->openAmazonPayPage();
        $this->loginAmazonPayment();
        $this->cancelPayment();
        $this->openAmazonPayPage();
        $this->submitPaymentMethod();
        $this->submitOrder();
        $this->checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithLoginPaymentTest
     * @throws \Exception
     * @group b
     */
    public function checkPaymentFromAddressPageWithReturnWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Address Page with login and return payment works');

        $this->initializeTest();
        $this->addProductToBasket();
        $this->loginOxid();
        $this->_openCheckout();
        $this->changePaymentMethod();
        $this->openAmazonPayPage();
        $this->loginAmazonPayment();
        $this->cancelPayment();
        $this->openAmazonPayPage();
        $this->submitPaymentMethod();
        $this->checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @throws \Exception
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromDetailWithReturnWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Details Page with login and return payment works');

        $this->initializeTest();
        $this->loginOxid();
        $this->openDetailPage();
        $this->openAmazonPayPage();
        $this->loginAmazonPayment();
        $this->cancelPayment();
        $this->openAmazonPayPage();
        $this->submitPaymentMethod();
        $this->submitOrder();
        $this->checkSuccessfulPayment();
    }
}
