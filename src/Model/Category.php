<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Model;

use OxidEsales\Eshop\Core\Registry;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Category
 */
class Category extends Category_parent
{
    /**
     * @inheritDoc
     * @return string|bool
     */
    public function save()
    {
        /** @var array $editVal */
        $editVal = Registry::getRequest()->getRequestParameter('editval');
        $this->setFieldData('osc_amazon_exclude', $editVal['oxcategories__osc_amazon_exclude']);
        return parent::save();
    }
}
