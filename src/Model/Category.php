<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Model;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Category
 */
class Category extends Category_parent
{
    /**
     * @inheritDoc
     */
    public function save()
    {
        $editVal = Registry::getRequest()->getRequestParameter('editval');
        $this->oxcategories__oxps_amazon_exclude = new Field($editVal['oxcategories__oxps_amazon_exclude']);
        return parent::save();
    }
}
