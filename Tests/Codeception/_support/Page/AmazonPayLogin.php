<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Page;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Page\Page;

class AmazonPayLogin extends Page
{
    private $amazonpayEmailInput = "//input[@id='ap_email']";
    private $amazonpayPasswordInput = "//input[@id='ap_password']";
    private $signInSubmitInput = "//input[@id='signInSubmit']";

    /**
     * @return void
     */
    public function login()
    {
        $I = $this->user;

        $I->waitForDocumentReadyState();
        $I->waitForElement($this->amazonpayEmailInput, 30);
        $I->fillField($this->amazonpayEmailInput, Fixtures::get('amazonClientUsername'));
        $I->fillField($this->amazonpayPasswordInput, Fixtures::get('amazonClientPassword'));
        $I->click($this->signInSubmitInput);
    }
}
