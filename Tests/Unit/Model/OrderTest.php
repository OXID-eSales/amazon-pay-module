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

namespace OxidProfessionalServices\AmazonPay\Tests\Unit\Model;

use Mockery;
use OxidEsales\Eshop\Application\Model\Order as EshopOrderModel;
use OxidProfessionalServices\AmazonPay\Core\AmazonService;
use OxidProfessionalServices\AmazonPay\Tests\Unit\Core\AmazonTestCase;

class OrderTest extends AmazonTestCase
{
    /** @var EshopOrderModel */
    private $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = oxNew(EshopOrderModel::class);
    }

    public function testGetDelAddressInfo(): void
    {
        $amazonServiceMock = Mockery::mock(AmazonService::class);
        $amazonServiceMock->shouldReceive('isAmazonSessionActive')->andReturn(true);
        $amazonServiceMock->shouldReceive('getDeliveryAddress')->andReturn($this->getAddressArray());
        $this->order->setAmazonService($amazonServiceMock);

        $delAddressInfo = $this->order->getDelAddressInfo();

        $this->assertNotEmpty($delAddressInfo);
        $this->assertSame('oxaddress', $delAddressInfo->_sClassName);
        $this->assertSame('Some Name', $delAddressInfo->oxaddress__name->rawValue);
        $this->assertSame('DE', $delAddressInfo->oxaddress__countrycode->rawValue);
        $this->assertSame('Some street 521', $delAddressInfo->oxaddress__addressline1->rawValue);
        $this->assertSame('Some street', $delAddressInfo->oxaddress__addressline2->rawValue);
        $this->assertSame('Some city', $delAddressInfo->oxaddress__addressline3->rawValue);
        $this->assertSame('12345', $delAddressInfo->oxaddress__postalcode->rawValue);
        $this->assertSame('Freiburg', $delAddressInfo->oxaddress__city->rawValue);
        $this->assertSame('+44989383728', $delAddressInfo->oxaddress__phonenumber->rawValue);
        $this->assertSame('Some street 521, Some city', $delAddressInfo->oxaddress__company->rawValue);
        $this->assertSame('BW', $delAddressInfo->oxaddress__stateorregion->rawValue);
    }

    public function testValidateDeliveryAddress(): void
    {
        $amazonServiceMock =  $this->mockLogger = Mockery::mock(AmazonService::class);
        $amazonServiceMock->shouldReceive('isAmazonSessionActive')->andReturn(true);
        $this->order->setAmazonService($amazonServiceMock);

        $this->assertSame(0, $this->order->validateDeliveryAddress(null));
    }

    public function testUpdateAmazonPayOrderStatus(): void
    {
        $this->order->updateAmazonPayOrderStatus('AMZ_PAYMENT_PENDING');
        $this->assertSame('NOT_FINISHED', $this->order->getFieldData('oxtransstatus'));
        $this->order->updateAmazonPayOrderStatus('AMZ_AUTH_AND_CAPT_OK');
        $this->assertSame('OK', $this->order->getFieldData('oxtransstatus'));
    }
}
