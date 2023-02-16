<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Model;

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
        return (bool)$this->getFieldData('osc_amazon_exclude');
    }
}
