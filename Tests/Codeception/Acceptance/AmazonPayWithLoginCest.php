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
        $this->_loginOxid();
        $this->_openBasketDisplay();
        $this->_loginAmazonPayment();
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
        $this->_loginOxid();
        $this->_openCheckout();
        $this->_loginAmazonPayment();
        $this->_submitOrder();
        $this->_checkSuccessfulPayment();
    }
}
