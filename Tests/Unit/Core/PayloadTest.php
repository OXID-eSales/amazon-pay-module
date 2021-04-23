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

namespace OxidProfessionalServices\AmazonPay\Tests\Unit\Core;

use OxidProfessionalServices\AmazonPay\Core\Payload;
use OxidEsales\TestingLibrary\UnitTestCase;

class PayloadTest extends UnitTestCase
{
    /** @var Payload */
    private $payload;

    protected function setUp()
    {
        $this->payload = new Payload();
    }

    private function createPayloadData(): Payload
    {
        $payload = new Payload();
        $payload->setCaptureAmount('1234.56');
        $payload->setSoftDescriptor('softDescriptor');
        $payload->setCanHandlePendingAuthorization(true);
        $payload->setPaymentIntent('Thank you for your order!');
        $payload->setMerchantStoreName('Oxid Store Name');
        $payload->setNoteToBuyer('capture');
        $payload->setCheckoutChargeAmount('2345.67');
        $payload->setPaymentDetailsChargeAmount('3456.78');

        return $payload;
    }

    public function testGetData(): void
    {
        $payload = $this->createPayloadData();
        $data = $payload->getData();

        $this->assertSame('capture', $data['paymentDetails']['paymentIntent']);
        $this->assertTrue($data['paymentDetails']['canHandlePendingAuthorization']);
        $this->assertSame('3456.78', $data['paymentDetails']['chargeAmount']['amount']);
        $this->assertSame('EUR', $data['paymentDetails']['chargeAmount']['currencyCode']);
        $this->assertSame('1234.56', $data['captureAmount']['amount']);
        $this->assertSame('2345.67', $data['chargeAmount']['amount']);
        $this->assertSame('EUR', $data['chargeAmount']['currencyCode']);
        $this->assertSame('softDescriptor', $data['softDescriptor']);
    }

    public function testSetPaymentIntent(): void
    {
        $payload = new Payload();
        $payload->setPaymentIntent('somethingRandom');
        $data = $payload->getData();
        $this->assertSame('somethingRandom', $data['paymentDetails']['paymentIntent']);
    }

    public function testSetCanHandlePendingAuthorization(): void
    {
        $payload = new Payload();
        $payload->setCanHandlePendingAuthorization(false);
        $data = $payload->getData();
        $this->assertFalse($data['paymentDetails']['canHandlePendingAuthorization']);
    }

    public function testSetPaymentDetailsChargeAmount(): void
    {
        $payload = new Payload();
        $payload->setPaymentDetailsChargeAmount('3456.78');
        $data = $payload->getData();
        $this->assertSame('3456.78', $data['paymentDetails']['chargeAmount']['amount']);
    }

    public function testSetSoftDescriptor(): void
    {
        $payload = new Payload();
        $payload->setSoftDescriptor('softDescriptor');
        $data = $payload->getData();
        $this->assertSame('softDescriptor', $data['softDescriptor']);
    }

    public function testSetCaptureAmount(): void
    {
        $payload = new Payload();
        $payload->setCaptureAmount('1234.56');
        $data = $payload->getData();
        $this->assertSame('1234.56', $data['captureAmount']['amount']);
    }

    public function testSetCheckoutChargeAmount(): void
    {
        $payload = new Payload();
        $payload->setCheckoutChargeAmount('2345.67');
        $data = $payload->getData();
        $this->assertSame('2345.67', $data['chargeAmount']['amount']);
        $this->assertSame('EUR', $data['chargeAmount']['currencyCode']);
    }

    public function testRemoveMerchantMetadata(): void
    {
        $payload = $this->createPayloadData();
        $data = $payload->removeMerchantMetadata($payload->getData());

        $this->assertNull($data['merchantMetadata']);
    }
}
