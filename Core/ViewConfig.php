<?php

/**
 * This file is part of OXID eSales AmazonPay module.
 *
 * OXID eSales AmazonPay module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales AmazonPay module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales AmazonPay module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2020
 */

namespace OxidProfessionalServices\AmazonPay\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * Amazon Pay getters for templates
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    /**
     * @return object|Config
     */
    public function getAmazonConfig()
    {
        return Registry::get(\OxidProfessionalServices\AmazonPay\Core\Config::class);
    }

    /**
     * @return bool
     */
    public function isAmazonActive(): bool
    {
        $config = $this->getAmazonConfig();
        $blIsActive = true;
        try {
            $config->checkHealth();
        } catch (StandardException $e) {
            $blIsActive = false;
        }
        return $blIsActive;
    }

    /**
     * @return bool
     */
    public function displayInPDP(): bool
    {
        return $this->getAmazonConfig()->displayInPDP();
    }

    /**
     * @return bool
     */
    public function useExclusion(): bool
    {
        return $this->getAmazonConfig()->useExclusion();
    }

    /**
     * @return bool
     */
    public function displayInMiniCart(): bool
    {
        return $this->getAmazonConfig()->displayInMiniCart();
    }

    /**
     * @return string
     */
    public function getAmazonSessionId(): string
    {
        return oxNew(AmazonService::class)->getCheckoutSessionId();
    }

    /**
     * @return bool
     */
    public function isAmazonSessionActive(): bool
    {
        return oxNew(AmazonService::class)->isAmazonSessionActive();
    }

    /**
     * Get webhook controller url
     *
     * @return string
     */
    public function getCancelAmazonPaymentUrl(): string
    {
        return $this->getSelfLink() . 'cl=amazoncheckout&fnc=cancelAmazonPayment';
    }

    /**
     * @param null $oxid
     * @return bool
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function isAmazonExclude($oxid = null): bool
    {
        if (!$this->useExclusion()) {
            return false;
        }

        $session = Registry::getSession();

        $basket = $session->getBasket();

        $productIds = [];

        foreach ($basket->getContents() as $product) {
            $productIds[] = $product->getProductId();
        }

        if (!empty($oxid)) {
            $productIds[] = $oxid;
        }

        $productIds = array_unique($productIds);

        if (count(array_filter($productIds)) < 1) {
            return false;
        }

        $parameters = '?';
        for ($i = 1, $iMax = count($productIds); $i < $iMax; $i++) {
            $parameters .= ',?';
        }

        $sql = <<<SQL
        SELECT oa.OXPS_AMAZON_EXCLUDE as excludeArticle,
               oc.OXPS_AMAZON_EXCLUDE as excludeCategory
          FROM oxarticles oa
          JOIN oxobject2category o2c
            ON (o2c.OXOBJECTID = oa.OXID)
          JOIN oxcategories oc
            ON (oc.OXID = o2c.OXCATNID)
         WHERE oa.OXID in ($parameters)
SQL;

        $results = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql, $productIds);

        foreach ($results as $result) {
            if ($result['excludeArticle'] === '1' || $result['excludeCategory'] === '1') {
                OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
                return true;
            }
        }

        return false;
    }
}
