<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidProfessionalServices\AmazonPay\Tests\Codeception\Page;

use OxidEsales\Codeception\Page\Page;

class AmazonPayLogin extends Page
{
    private $amazonpayEmailInput = "//input[@id='ap_email']";
    private $amazonpayPasswordInput = "//input[@id='ap_password']";
    private $signInSubmitInput = "//input[@id='signInSubmit']";
    private $amazonpayLogin = '';
    private $amazonpayPassword = '';

    /**
     * @return void
     */
    public function login()
    {
        $I = $this->user;

        $I->waitForElement($this->amazonpayEmailInput);
        $I->fillField($this->amazonpayEmailInput, $this->amazonpayLogin);
        $I->fillField($this->amazonpayPasswordInput, $this->amazonpayPassword);
        $I->click($this->signInSubmitInput);
    }
}
