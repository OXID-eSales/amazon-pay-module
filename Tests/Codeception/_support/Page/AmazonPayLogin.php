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

    /**
     * @param string $username
     * @param string $password
     * @return void
     */
    public function login(string $username, string $password)
    {
        $I = $this->user;

        $I->waitForElement($this->amazonpayEmailInput);
        $I->fillField($this->amazonpayEmailInput, $username);
        $I->fillField($this->amazonpayPasswordInput, $password);
        $I->click($this->signInSubmitInput);
    }
}
