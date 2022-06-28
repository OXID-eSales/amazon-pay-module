<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Controller;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\ArticleDetailsController
 */
class ArticleDetailsController extends ArticleDetailsController_parent
{
    public function render()
    {
        $article = $this->getProduct();
        $this->addTplParam('amazonExclude', $article->isAmazonExclude());

        return parent::render();
    }
}
