<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\modules\osc\amazonpay\Tests\Codeception\Acceptance;

use Exception;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Acceptance\BaseCest;
use OxidSolutionCatalysts\AmazonPay\Tests\Codeception\AcceptanceTester;

final class AmazonPayLoginCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayLoginPaymentTest
     */
    public function checkPaymentFromAddressPageWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay via Address Page without login payment works');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_openCheckout();
        $this->_openAmazonPayPage();
        $this->_loginAmazonPayment();
        try {
            $I->waitForDocumentReadyState();
            $I->see("You are securely signed in with Amazon.");
            $this->_submitPaymentMethod();
        } catch (Exception $exception) {
        }

        $this->_confirmAddress();
        $this->_confirmPayment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $orderNumber = $this->_checkSuccessfulPayment();

        $data = $this->_checkDatabase($orderNumber);
        $this->_openOrder($orderNumber);
        $this->_checkDataOnAdminPage($data);
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayLoginPaymentTest
     */
    public function checkPaymentFromBasketWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay login via widget');

        $this->_initializeTest();
        $this->_addProductToBasket();
        $this->_loginOxidViaAmazon();
        $this->_loginAmazonPayment();
        try {
            $I->waitForDocumentReadyState();
            $I->see("You are securely signed in with Amazon.");
            $this->_submitPaymentMethod();
        } catch (Exception $exception) {
        }

        $this->_confirmAddress();
        $this->_confirmPayment();
        $this->_openAmazonPayPage();
        $this->_submitPaymentMethod();
        $orderNumber = $this->_checkSuccessfulPayment();

        $data = $this->_checkDatabase($orderNumber);
        $this->_openOrder($orderNumber);
        $this->_checkDataOnAdminPage($data);
    }
}
