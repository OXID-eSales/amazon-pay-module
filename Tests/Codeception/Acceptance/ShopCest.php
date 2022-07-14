<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidProfessionalServices\AmazonPay\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Page\Home;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\AcceptanceTester;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\Page\AcceptSSLCertificate;

final class ShopCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     * @group BaseTest
     */
    public function shopStartPageLoads(AcceptanceTester $I)
    {
        $homePage = new Home($I);
        $I->amOnPage($homePage->URL);

        $acceptCertificatePage = new AcceptSSLCertificate($I);
        $acceptCertificatePage->acceptCertificate();

        $I->waitForText("Home");
        $I->waitForText("Week's Special");
    }
}
