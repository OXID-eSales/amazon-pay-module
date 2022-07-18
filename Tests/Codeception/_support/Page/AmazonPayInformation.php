<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidProfessionalServices\AmazonPay\Tests\Codeception\Page;

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
}
