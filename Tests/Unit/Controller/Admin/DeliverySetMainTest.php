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

namespace OxidSolutionCatalysts\AmazonPay\Tests\Unit\Controller\Admin;

use OxidSolutionCatalysts\AmazonPay\Controller\Admin\DeliverySetMain;
use OxidEsales\TestingLibrary\UnitTestCase;

class DeliverySetMainTest extends UnitTestCase
{
    /** @var DeliverySetMain */
    private $deliverySetMain;

    protected function setUp(): void
    {
        $this->deliverySetMain = oxNew(DeliverySetMain::class);
    }

    public function testRender(): void
    {
        $this->assertSame('deliveryset_main.tpl', $this->deliverySetMain->render());
    }

    public function testSave(): void
    {
        $editVal = [
            'oxdeliveryset__oxid' => 'oxidstandard',
            'oxid' => 'oxidstandard'
        ];

        $this->setRequestParameter('editVal', $editVal);
        $this->setRequestParameter('editAmazonCarrier', 'DHL');

        $this->deliverySetMain->save();
        $this->assertNotEmpty($this->deliverySetMain->getEditObjectId());
    }
}
