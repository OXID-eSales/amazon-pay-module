<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Model\User;

class Payload
{
    /**
     * @var string
     */
    private string $paymentIntent;

    /**
     * @var bool
     */
    private ?bool $canHandlePendingAuthorization = null;

    /**
     * @var string
     */
    private string $merchantStoreName = '';

    /**
     * @var string
     */
    private string $noteToBuyer = '';

    /**
     * @var string
     */
    private string $paymentDetailsChargeAmount = '';

    /**
     * @var string
     */
    private string $checkoutChargeAmount = '';

    /**
     * @var string
     */
    private string $captureAmount = '';

    /**
     * @var string
     */
    private string $currencyCode = '';

    /**
     * @var string
     */
    private string $softDescriptor = '';

    /**
     * @var string
     */
    private string $merchantReferenceId = '';

    /**
     * @var string
     */
    private string $checkoutReviewReturnUrl = '';

    /**
     * @var string
     */
    private string $checkoutResultReturnUrl = '';

    /**
     * @var string
     */
    private string $signInReturnUrl = '';

    /**
     * @var string
     */
    private string $signInCancelUrl = '';

    /**
     * @var string
     */
    private string $storeId = '';

    /**
     * @var array
     */
    private array $scopes = [];

    /**
     * @var array
     */
    private array $signInScopes = [];

    /**
     * @var array
     */
    private array $addressDetails = [];
    /**
     * @var string
     */
    private string $platformId = '';
    private array $addressRestrictions = [];

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = [];

        if (!empty($this->checkoutReviewReturnUrl)) {
            $data['webCheckoutDetails']['checkoutReviewReturnUrl'] = $this->checkoutReviewReturnUrl;
        }

        if (!empty($this->checkoutResultReturnUrl)) {
            $data['webCheckoutDetails']['checkoutResultReturnUrl'] = $this->checkoutResultReturnUrl;
        }

        if (!empty($this->signInReturnUrl)) {
            $data['signInReturnUrl'] = $this->signInReturnUrl;
        }

        if (!empty($this->signInCancelUrl)) {
            $data['signInCancelUrl'] = $this->signInCancelUrl;
        }

        if (!empty($this->storeId)) {
            $data['storeId'] = $this->storeId;
        }

        if (!empty($this->scopes)) {
            $data['scopes'] = $this->scopes;
        }

        if (!empty($this->signInScopes)) {
            $data['signInScopes'] = $this->signInScopes;
        }

        if ($this->canHandlePendingAuthorization === false || $this->canHandlePendingAuthorization === true) {
            $data['paymentDetails']['canHandlePendingAuthorization'] = $this->canHandlePendingAuthorization;
        }

        if (!empty($this->paymentIntent)) {
            $data['paymentDetails']['paymentIntent'] = $this->paymentIntent;
        }

        if (!empty($this->paymentDetailsChargeAmount)) {
            $data['paymentDetails']['chargeAmount'] = [];
            $data['paymentDetails']['chargeAmount']['amount'] = $this->paymentDetailsChargeAmount;
            $data['paymentDetails']['chargeAmount']['currencyCode'] = $this->currencyCode;
        }

        if (!empty($this->captureAmount)) {
            $data['captureAmount'] = [];
            $data['captureAmount']['amount'] = $this->captureAmount;
            $data['captureAmount']['currencyCode'] = $this->currencyCode;
        }


        $data['softDescriptor'] = $this->softDescriptor ?: '';
        if (empty($data['softDescriptor'])) {
            $data = $this->addMerchantMetaData($data);
        }

        if (!empty($this->checkoutChargeAmount)) {
            $data['chargeAmount'] = [];
            $data['chargeAmount']['amount'] = $this->checkoutChargeAmount;
            $data['chargeAmount']['currencyCode'] = $this->currencyCode;
        }

        if (!empty($this->addressDetails)) {
            $data['addressDetails'] = $this->addressDetails;
            $data['webCheckoutDetails']['checkoutMode'] = 'ProcessOrder';
        }

        if (!empty($this->platformId)) {
            $data['platformId'] = $this->platformId;
        }

        if (!empty($this->addressRestrictions)) {
            $data['deliverySpecifications']['addressRestrictions']['type'] = 'Allowed';
            $data['deliverySpecifications']['addressRestrictions']['restrictions'] = $this->addressRestrictions;
        }

        return $data;
    }

    public function setAddressRestrictions(array $allowedCountries): void
    {
        $this->addressRestrictions = $allowedCountries;
    }

    /**
     * @param string $platformId
     * @return void
     */
    public function setPlatformId(string $platformId): void
    {
        $this->platformId = $platformId;
    }

    /**
     * @param string $paymentIntent
     */
    public function setPaymentIntent(string $paymentIntent): void
    {
        $this->paymentIntent = $paymentIntent;
    }

    /**
     * @param string $merchantStoreName
     */
    public function setMerchantStoreName(string $merchantStoreName): void
    {
        $this->merchantStoreName = $merchantStoreName;
    }

    /**
     * @param string $noteToBuyer
     */
    public function setNoteToBuyer(string $noteToBuyer): void
    {
        $this->noteToBuyer = $noteToBuyer;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode(string $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @param bool $canHandlePendingAuthorization
     */
    public function setCanHandlePendingAuthorization(bool $canHandlePendingAuthorization): void
    {
        $this->canHandlePendingAuthorization = $canHandlePendingAuthorization;
    }

    public function getCanHandlePendingAuthorization()
    {
        return $this->canHandlePendingAuthorization;
    }

    /**
     * @param string $paymentDetailsChargeAmount
     */
    public function setPaymentDetailsChargeAmount(string $paymentDetailsChargeAmount): void
    {
        $this->paymentDetailsChargeAmount = PhpHelper::getMoneyValue((float)$paymentDetailsChargeAmount);
    }

    /**
     * @param string $merchantReferenceId
     */
    public function setMerchantReferenceId(string $merchantReferenceId): void
    {
        $this->merchantReferenceId = $merchantReferenceId;
    }

    /**
     * @param string $softDescriptor
     */
    public function setSoftDescriptor(string $softDescriptor): void
    {
        $this->softDescriptor = $softDescriptor;
    }

    /**
     * @param string $captureAmount
     */
    public function setCaptureAmount(string $captureAmount): void
    {
        $this->captureAmount = PhpHelper::getMoneyValue((float)$captureAmount);
    }

    /**
     * @param string $checkoutChargeAmount
     */
    public function setCheckoutChargeAmount(string $checkoutChargeAmount): void
    {
        $this->checkoutChargeAmount = $checkoutChargeAmount;
    }

    /**
     * @param string $articlesId
     * @return void
     */
    public function setCheckoutReviewReturnUrl(string $articlesId = ''): void
    {
        $this->checkoutReviewReturnUrl =
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->checkoutReviewUrl() .
            ($articlesId !== '' ? '&anid=' . $articlesId : '');
    }

    /**
     * @return void
     */
    public function setSignInReturnUrl(): void
    {
        $this->signInReturnUrl =
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->signInReturnUrl();
    }

    /**
     * @return void
     */
    public function setSignInCancelUrl(): void
    {
        $this->signInCancelUrl =
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->signInCancelUrl();
    }

    /**
     * @return void
     */
    public function setCheckoutResultReturnUrl(): void
    {
        $this->checkoutResultReturnUrl = Registry::getConfig()->getCurrentShopUrl(false)
            . 'index.php?cl=order&fnc=execute&action=result&stoken='
            . Registry::getSession()->getSessionChallengeToken();
    }

    /**
     * @return void
     */
    public function setCheckoutResultReturnUrlExpress(): void
    {
        $this->checkoutResultReturnUrl = OxidServiceProvider::getAmazonClient()->getModuleConfig()->checkoutResultUrl();
    }

    /**
     * @return void
     */
    public function setStoreId(): void
    {
        $this->storeId = OxidServiceProvider::getAmazonClient()->getModuleConfig()->getStoreId();
    }

    /**
     * @param array $scopes
     * @return void
     */
    public function addScopes(array $scopes): void
    {
        $this->scopes = array_merge($this->scopes, $scopes);
    }

    public function addSignInScopes(array $scopes): void
    {
        $this->signInScopes = array_merge($this->scopes, $scopes);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function addMerchantMetaData(array $data): array
    {
        $data['merchantMetadata'] = [];
        //$data['merchantMetadata']['merchantReferenceId'] = $this->merchantReferenceId;
        $data['merchantMetadata']['merchantStoreName'] = $this->merchantStoreName;
        $data['merchantMetadata']['noteToBuyer'] = $this->noteToBuyer;
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function removeMerchantMetadata(array $data): array
    {
        unset($data['merchantMetadata']);
        return $data;
    }

    /**
     * @param User $user
     * @return Payload
     */
    public function setAddressDetails(User $user): Payload
    {
        /** @var string $oxstreet */
        $oxstreet = $user->getFieldData('oxstreet');
        /** @var string $oxstreetnr */
        $oxstreetnr = $user->getFieldData('oxstreetnr');
        /** @var string $oxfname */
        $oxfname = $user->getFieldData('oxfname');
        /** @var string $oxlname */
        $oxlname = $user->getFieldData('oxlname');
        /** @var string $oxzip */
        $oxzip = $user->getFieldData('oxzip');
        /** @var string $oxcity */
        $oxcity = $user->getFieldData('oxcity');
        /** @var string $oxfon */
        $oxfon = $user->getFieldData('oxfon');
        /** @var string $oxprivfon */
        $oxprivfon = $user->getFieldData('oxprivfon');
        /** @var string $oxmobfon */
        $oxmobfon = $user->getFieldData('oxmobfon');

        $addressLine1 = sprintf(
            '%s %s',
            $oxstreet,
            $oxstreetnr,
        );

        /** @var string $oxcountryid */
        $oxcountryid = $user->getFieldData('oxcountryid');
        $oCountry = oxNew(Country::class);
        $oCountry->load($oxcountryid);
        $oxisoalpha2 = $oCountry->getFieldData('oxisoalpha2');
        $sCountryCode = $oxisoalpha2;


        // set mandatory standard fields
        $this->addressDetails = [
            'name' => $oxfname . ' ' . $oxlname,
            'addressLine1' => $addressLine1,
            'postalCode' => $oxzip,
            'city' => $oxcity,
            'countryCode' => $sCountryCode
        ];

        // check for additional fields
        // check for phone number
        $phoneNumber = null;
        if (!empty($oxfon)) { // phone number
            $phoneNumber = $oxfon;
        } elseif (!empty($oxprivfon)) { // phone number (private)
            $phoneNumber = $oxprivfon;
        } elseif (!empty($oxmobfon)) { // phone number (private)
            $phoneNumber = $oxmobfon;
        }
        /** TODO Change default number to  0 */
        $this->addressDetails['phoneNumber'] = $phoneNumber ?? '0'; // when no number was provided, Amazon accepts '0'
        return $this;
    }
}
