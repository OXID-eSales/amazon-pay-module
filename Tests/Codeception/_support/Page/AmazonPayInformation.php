<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Page;

use OxidEsales\Codeception\Page\Page;

class AmazonPayInformation extends Page
{
    private $continueToCheckout = "//input[@class='a-button-input']";
    private $cancelCheckout = "//a[@id='return_back_to_merchant_link']";
    private $changePaymentMethod = "//a[@id='change-payment-button']";
    private $changePaymentConfirm = "//span[@class='a-button a-spacing-top-medium a-button-primary buyer-action-button']";
    private $buyerCanceledOption = "//li[@data-trail_number='3064']";

    /**
     * @return void
     */
    public function submitPayment()
    {
        $I = $this->user;

        $I->waitForElement($this->continueToCheckout);
        $I->wait(1);
        $I->click($this->continueToCheckout);
    }

    public function cancelPayment()
    {
        $I = $this->user;

        $I->waitForElement($this->cancelCheckout);
        $I->wait(1);
        $I->click($this->cancelCheckout);
    }

    public function changePaymentToBuyerCanceledOption()
    {
        $I = $this->user;

        $I->waitForElement($this->changePaymentMethod);
        $I->wait(1);
        $I->click($this->changePaymentMethod);

        $I->waitForDocumentReadyState();
        $I->wait(1);

        $I->waitForElementClickable($this->buyerCanceledOption);
        $I->wait(1);
        $I->click($this->buyerCanceledOption);

        $I->waitForElement($this->changePaymentConfirm);
        $I->wait(1);
        $I->click($this->changePaymentConfirm);
    }
}
