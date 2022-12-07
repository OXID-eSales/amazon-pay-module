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

    /**
     * @return void
     */
    public function submitPayment()
    {
        $I = $this->user;

        $I->waitForDocumentReadyState();
        $I->makeScreenshot();
        $I->waitForElement($this->continueToCheckout);
        $I->makeScreenshot();
        $I->waitForElementClickable($this->continueToCheckout, 30);
        $I->click($this->continueToCheckout);
    }

    public function cancelPayment()
    {
        $I = $this->user;

        $I->waitForElement($this->cancelCheckout, 30);
        $I->click($this->cancelCheckout);
    }
}
