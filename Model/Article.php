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

namespace OxidProfessionalServices\AmazonPay\Model;

use Doctrine\DBAL\FetchMode;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Article
 */
class Article extends Article_parent
{
    protected $blAmazonExclude = false;

    /**
     * Checks if article is buyable.
     *
     * @return bool
     */
    public function isAmazonExclude(): bool
    {
        return $this->blAmazonExclude;
    }

    /**
     * @inheritDoc
     */
    public function load($sOXID)
    {
        $load = parent::load($sOXID);

        if (!$load) {
            return false;
        }

        $sql = 'SELECT OXPS_AMAZON_EXCLUDE FROM oxarticles WHERE OXID = ?';
        $result = DatabaseProvider::getDb(FetchMode::ASSOCIATIVE)->getOne($sql, [$sOXID]);
        $this->blAmazonExclude = (bool)$result;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        $editVal = Registry::getRequest()->getRequestParameter('editval');
        $this->oxarticles__oxps_amazon_exclude = new Field($editVal['oxarticles__oxps_amazon_exclude']);
        return parent::save();
    }
}
