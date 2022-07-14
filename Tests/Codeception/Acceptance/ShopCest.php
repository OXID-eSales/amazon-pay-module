<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidProfessionalServices\AmazonPay\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Page\Home;
use OxidProfessionalServices\AmazonPay\Tests\Codeception\Acceptancetester;

final class ShopCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function shopStartPageLoads(AcceptanceTester $I)
    {
        $homePage = new Home($I);
        $I->amOnPage($homePage->URL);

        $I->waitForText("Home");
        $I->waitForText("Week's Special");
    }
}
