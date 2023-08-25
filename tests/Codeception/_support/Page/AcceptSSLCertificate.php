<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception\Page;

use OxidEsales\Codeception\Page\Page;

class AcceptSSLCertificate extends Page
{
    private $markerString = "Your connection is not private";
    private $advancedButton = "//button[@id='details-button']";
    private $proceedLink = "//a[@id='proceed-link']";

    /**
     * @return void
     */
    public function acceptCertificate()
    {
        $I = $this->user;

        try {
            $I->waitForText($this->markerString, 60);
        } catch (\Exception $e) {
            return;
        }

        $I->waitForElement($this->advancedButton, 60);
        $I->click($this->advancedButton);
        $I->click($this->proceedLink);
    }
}
