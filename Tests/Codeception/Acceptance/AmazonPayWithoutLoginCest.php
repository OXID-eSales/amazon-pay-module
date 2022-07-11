<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidProfessionalServices\AmazonPay\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Module\Translation\Translator;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\AcceptanceTester;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\Page\AmazonPayInformation;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\Page\AmazonPayLogin;

require __DIR__ . '/BaseCest.php';

class AmazonPayWithoutLoginCest extends BaseCest
{
    private $amazonpayDiv = "//div[contains(@id, 'AmazonPayButton')]";

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithoutLoginPaymentTest
     */
    public function checkPaymentFromBasketWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay payment works');

        $this->_setAcceptance($I);
        $this->_initializeTest();
        $this->_openBasketDisplay();

        $I->waitForElement($this->amazonpayDiv);
        $I->click($this->amazonpayDiv);
        $amazonpayLoginPage = new AmazonPayLogin($I);
        $amazonpayLoginPage->login('dmitrii.volkhin@oxid-esales.com', 'a8Z8dRnYfxEciXx');

        $amazonpayInformationPage = new AmazonPayInformation($I);
        $amazonpayInformationPage->submitPayment();

        $I->waitForText(Translator::translate('HOME'));
        //$I->waitForText(Translator::translate('START_WEEKSPECIAL'));
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group AmazonPayWithoutLoginPaymentTest
     */
    public function checkPaymentFromAddressPageWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test AmazonPay payment works');

        $this->_setAcceptance($I);
        $this->_initializeTest();
        $this->_openCheckout();

        $I->waitForElement($this->amazonpayDiv);
        $I->click($this->amazonpayDiv);
        $amazonpayLoginPage = new AmazonPayLogin($I);
        $amazonpayLoginPage->login('dmitrii.volkhin@oxid-esales.com', 'a8Z8dRnYfxEciXx');

        $amazonpayInformationPage = new AmazonPayInformation($I);
        $amazonpayInformationPage->submitPayment();

        $I->waitForText(Translator::translate('HOME'));
        $I->waitForText(Translator::translate('START_WEEKSPECIAL'));
    }
}
