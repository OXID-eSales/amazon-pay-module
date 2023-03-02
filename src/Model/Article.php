<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Model;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Article
 */
class Article extends Article_parent
{
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
