<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;

use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;

final class AmazonPayWithLoginCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     * @return void
     * @throws \Exception
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromBasketWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test AmazonPay via Basket with login payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxid();
        $I->makeScreenshot(time() . 'afterlogin.png');
        $I->wait(5);
        $this->_openBasketDisplay();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_submitPaymentMethod();
        $this->_submitOrder();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithLoginPaymentTest
     * @throws \Exception
     */
    public function checkPaymentFromAddressPageWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test AmazonPay via Address Page with login payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxid();
        $this->_openCheckout();
        $this->_changePaymentMethod();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_submitPaymentMethod();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @throws \Exception
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromDetailWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test AmazonPay via Details Page with login payment works');

        $this->_initializeTest();
        $this->_loginOxid();
        $this->_openDetailPage();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_submitPaymentMethod();
        $this->_submitOrder();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @throws \Exception
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromBasketWithReturnWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test AmazonPay via Basket with login and return payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxid();
        $this->_openBasketDisplay();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_cancelPayment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_submitOrder();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithLoginPaymentTest
     * @throws \Exception
     * @group b
     */
    public function checkPaymentFromAddressPageWithReturnWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test AmazonPay via Address Page with login and return payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxid();
        $this->_openCheckout();
        $this->_changePaymentMethod();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_cancelPayment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @throws \Exception
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromDetailWithReturnWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test AmazonPay via Details Page with login and return payment works');

        $this->_initializeTest();
        $this->_loginOxid();
        $this->_openDetailPage();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_cancelPayment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_submitOrder();
        $this->_checkSuccessfulPayment();
    }
}
