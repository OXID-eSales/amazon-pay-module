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
    private string $continueToCheckout = "//input[@class='a-button-input']";
    private string $cancelCheckout = "//a[@id='return_back_to_merchant_link']";

    /**
     * @return void
     */
    public function submitPayment(): void
    {
        $I = $this->user;

        $I->waitForDocumentReadyState();
        $I->waitForElement($this->continueToCheckout);
        $I->waitForElementClickable($this->continueToCheckout, 30);
        $I->click($this->continueToCheckout);
        $I->wait(5);
    }

    public function cancelPayment()
    {
        $I = $this->user;

        $I->waitForElementClickable($this->cancelCheckout, 30);
        $I->click($this->cancelCheckout);
        $I->wait(5);
    }
}
