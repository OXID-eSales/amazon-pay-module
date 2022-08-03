<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidProfessionalServices\AmazonPay\Tests\Codeception\Acceptance;

use OxidProfessionalServices\AmazonPay\Tests\Codeception\AcceptanceTester;

final class AmazonPayWithLoginCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromBasketWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Basket with login payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxid();
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
     */
    public function checkPaymentFromAddressPageWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Address Page with login payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxid();
        $this->_openCheckout();
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
     */
    public function checkPaymentFromDetailWorks(AcceptanceTester $I)
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
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromBasketWithReturnWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Basket with login and return payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxid();
        $this->_openBasketDisplay();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_cancelPeyment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_submitOrder();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromAddressPageWithReturnWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Address Page with login and return payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxid();
        $this->_openCheckout();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_cancelPeyment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_submitOrder();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithLoginPaymentTest
     */
    public function checkPaymentFromDetailWithReturnWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Details Page with login and return payment works');

        $this->_initializeTest();
        $this->_loginOxid();
        $this->_openDetailPage();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_cancelPeyment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_submitOrder();
        $this->_checkSuccessfulPayment();
    }
}
