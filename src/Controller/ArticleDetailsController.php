<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidSolutionCatalysts\AmazonPay\Model\Article;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\ArticleDetailsController
 */
class ArticleDetailsController extends ArticleDetailsController_parent
{
    public function render()
    {
        /** @var Article $article */
        $article = $this->getProduct();
        $this->addTplParam('amazonExclude', $article->isAmazonExclude());

        return parent::render();
    }
}
