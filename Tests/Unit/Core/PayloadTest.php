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

namespace OxidSolutionCatalysts\AmazonPay\Tests\Unit\Core;

use OxidSolutionCatalysts\AmazonPay\Core\Payload;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidEsales\Eshop\Core\Registry;

class PayloadTest extends UnitTestCase
{
    /** @var Payload */
    private $payload;

    protected function setUp(): void
    {
        $this->payload = new Payload();
    }

    private function createPayloadData(): Payload
    {
        $payload = new Payload();
        $payload->setCaptureAmount('1234.56');
        $payload->setSoftDescriptor('softDescriptor');
        $payload->setCanHandlePendingAuthorization(true);
        $payload->setCurrencyCode('EUR');
        $payload->setNoteToBuyer('Thank you for your order!');
        $payload->setPaymentIntent('capture');
        $payload->setMerchantStoreName('Oxid Store Name');
        $payload->setNoteToBuyer('capture');
        $payload->setCheckoutChargeAmount('2345.67');
        $payload->setPaymentDetailsChargeAmount('3456.78');
        $payload->setMerchantReferenceId('12345');

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
        $payload->setCurrencyCode('EUR');
        $data = $payload->getData();
        $this->assertSame('3456.78', $data['paymentDetails']['chargeAmount']['amount']);
        $this->assertSame('EUR', $data['paymentDetails']['chargeAmount']['currencyCode']);
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
        $payload->setCurrencyCode('EUR');
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

    public function testSetCheckoutReviewReturnUrl(): void
    {
        $payload = new Payload();
        $payload->setCheckoutReviewReturnUrl();

        $data = $payload->getData();
        $this->assertSame(
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->checkoutReviewUrl(),
            $data['webCheckoutDetails']['checkoutReviewReturnUrl']
        );
    }

    public function testSetCheckoutResultReturnUrl(): void
    {
        $payload = new Payload();
        $payload->setCheckoutResultReturnUrl();

        $data = $payload->getData();
        $this->assertStringContainsString(
            $this->checkoutResultReturnUrl = Registry::getConfig()->getCurrentShopUrl(false),
            $data['webCheckoutDetails']['checkoutResultReturnUrl']
        );

        $this->assertStringContainsString(
            "index.php?cl=order&fnc=execute&action=result&stoken=",
            $data['webCheckoutDetails']['checkoutResultReturnUrl']
        );
    }

    public function testSetCheckoutResultReturnUrlExpress(): void
    {
        $payload = new Payload();
        $payload->setCheckoutResultReturnUrlExpress();

        $data = $payload->getData();
        $this->assertSame(
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->checkoutResultUrl(),
            $data['webCheckoutDetails']['checkoutResultReturnUrl']
        );
    }

    public function testSetSignInReturnUrl(): void
    {
        $payload = new Payload();
        $payload->setSignInReturnUrl();

        $data = $payload->getData();
        $this->assertSame(
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->signInReturnUrl(),
            $data['signInReturnUrl']
        );
    }

    public function testSetSignInCancelUrl(): void
    {
        $payload = new Payload();
        $payload->setSignInCancelUrl();

        $data = $payload->getData();
        $this->assertSame(
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->signInCancelUrl(),
            $data['signInCancelUrl']
        );
    }

    public function testSetStoreId(): void
    {
        $payload = new Payload();
        $payload->setStoreId();

        $data = $payload->getData();
        $this->assertSame(
            $this->storeId = OxidServiceProvider::getAmazonClient()->getModuleConfig()->getStoreId(),
            $data['storeId']
        );
    }
}
