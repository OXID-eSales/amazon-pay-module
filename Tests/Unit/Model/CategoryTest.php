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

declare(strict_types=1);

namespace OxidSolutionCatalysts\AmazonPay\Tests\Unit\Model;

use OxidEsales\Eshop\Application\Model\Category as EshopCategoryModel;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\TestingLibrary\UnitTestCase;

class CategoryTest extends UnitTestCase
{
    /** @var EshopCategoryModel */
    private EshopCategoryModel $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = oxNew(EshopCategoryModel::class);
    }

    public function testSave(): void
    {
        $this->category->setShopId(1);
        $this->category->setId('testSaveId');
        $this->category->oxcategories__oxtitle = new Field("test title");
        $this->category->oxcategories__oxparentid = new Field("oxrootid");

        $this->setRequestParameter('editval', ['oxcategories__osc_amazon_exclude' => false]);
        $this->category->save();
        $this->assertTrue($this->category->load('testSaveId'));

        $this->setRequestParameter('editval', ['oxcategories__osc_amazon_exclude' => true]);
        $this->category->save();
        $this->assertTrue($this->category->load('testSaveId'));

        $this->assertSame('1', $this->category->oxcategories__osc_amazon_exclude->rawValue);
    }
}
