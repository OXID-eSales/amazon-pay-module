<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance;

use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;

final class AmazonPayWithoutLoginCest extends BaseCest
{
    public function _before(AcceptanceTester $I): void
    {
        parent::_before($I);
        $I->haveInDatabase(
            'oxuser',
            [
                'OXID' => 'testAmazonPay',
                'OXACTIVE' => 1,
                'OXRIGHTS' => 'user',
                'OXSHOPID' => 1,
                'OXUSERNAME' => $_ENV['AMAZONPAY_CLIENT_USERNAME'],
                'OXPASSWORD' => hash('sha512', $_ENV['AMAZONPAY_CLIENT_PASSWORD']),
                'OXPASSSALT' => '',
                'OXFNAME' => 'TestUserName',
                'OXLNAME' => 'TestUserSurname',
                'OXSTREET' => 'Bertoldstraße',
                'OXSTREETNR' => '48',
                'OXCITY' => 'Freiburg',
                'OXZIP' => '79098',
                'OXCOUNTRYID' => 'a7c40f631fc920687.20179984',
                'OXBIRTHDATE' => '1985-02-05 14:42:42',
                'OXCREATE' => '2021-02-05 14:42:42',
                'OXREGISTER' => '2021-02-05 14:42:42'
            ]
        );
    }

    public function _after(AcceptanceTester $I): void
    {
        parent::_after($I);
        $I->deleteFromDatabase('oxuser', ['OXID' => 'testAmazonPay']);
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithoutLoginPaymentTest
     */
    public function checkPaymentFromBasketWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Basket without login payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_openBasketDisplay();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_submitPaymentMethod();
        $this->_checkAccountExist();
        $this->_loginOxidWithAmazonCredentials();
        $this->_changePaymentMethod();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithoutLoginPaymentTest
     */
    public function checkPaymentFromAddressPageWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Address Page without login payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_openCheckout();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_submitPaymentMethod();
        $this->_checkAccountExist();
        $this->_loginOxidWithAmazonCredentials();
        $this->_changePaymentMethod();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithoutLoginPaymentTest
     */
    public function checkPaymentFromDetailWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Details Page without login payment works');

        $this->_initializeTest();
        $this->_openDetailPage();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_submitPaymentMethod();
        $this->_checkAccountExist();
        $this->_loginOxidWithAmazonCredentials();
        $this->_changePaymentMethod();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithoutLoginPaymentTest
     * @group asd
     */
    public function checkPaymentFromBasketWithReturnWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Basket without login with return payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_openBasketDisplay();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_cancelPayment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkAccountExist();
        $this->_loginOxidWithAmazonCredentials();
        $this->_changePaymentMethod();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithoutLoginPaymentTest
     */
    public function checkPaymentFromAddressPageWithReturnWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Address Page without login with return payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_openCheckout();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_cancelPayment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkAccountExist();
        $this->_loginOxidWithAmazonCredentials();
        $this->_changePaymentMethod();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithoutLoginPaymentTest
     */
    public function checkPaymentFromDetailWithReturnWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Details Page without login with return payment works');

        $this->_initializeTest();
        $this->_openDetailPage();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        $this->_cancelPayment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkAccountExist();
        $this->_loginOxidWithAmazonCredentials();
        $this->_changePaymentMethod();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $this->_checkSuccessfulPayment();
    }
}
