<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Tests\Codeception;

use Codeception\Actor;
use OxidEsales\Codeception\Page\Home;
use OxidEsales\Eshop\Core\Registry;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Define custom actions here
     */

    public function saveShopConfVar($sVarType, $sVarName, $sVarVal, $sShopId = null, $sModule = '')
    {
        $config = Registry::getConfig();
        $config->saveShopConfVar($sVarType, $sVarName, $sVarVal, $sShopId, $sModule);
    }

    /**
     * Open shop first page.
     */
    public function openShop(): Home
    {
        $I = $this;
        $homePage = new Home($I);
        $I->amOnPage($homePage->URL);

        return $homePage;
    }
}
