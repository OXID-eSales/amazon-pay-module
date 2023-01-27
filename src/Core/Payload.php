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
    private $paymentIntent;

    /**
     * @var bool
     */
    private $canHandlePendingAuthorization;

    /**
     * @var string
     */
    private $merchantStoreName;

    /**
     * @var string
     */
    private $noteToBuyer;

    /**
     * @var string
     */
    private $paymentDetailsChargeAmount;

    /**
     * @var string
     */
    private $checkoutChargeAmount;

    /**
     * @var string
     */
    private $captureAmount;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var string
     */
    private $softDescriptor;

    /**
     * @var string
     */
    private $merchantReferenceId;

    /**
     * @var string
     */
    private $checkoutReviewReturnUrl;

    /**
     * @var string
     */
    private $checkoutResultReturnUrl;

    /**
     * @var string
     */
    private $signInReturnUrl;

    /**
     * @var string
     */
    private $signInCancelUrl;

    /**
     * @var string
     */
    private $storeId;

    /**
     * @var array
     */
    private $scopes = [];

    /**
     * @var array
     */
    private $signInScopes = [];

    /**
     * @var array
     */
    private array $addressDetails;
    /**
     * @var string
     */
    private string $platformId;
    private array $addressRestrictions;

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

        if (
            is_bool($this->canHandlePendingAuthorization) ||
            !empty($this->paymentIntent) ||
            !empty($this->paymentDetailsChargeAmount)
        ) {
            #$data['paymentDetails'] = [];
            if (is_bool($this->canHandlePendingAuthorization)) {
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
        }

        if (!empty($this->captureAmount)) {
            $data['captureAmount'] = [];
            $data['captureAmount']['amount'] = $this->captureAmount;
            $data['captureAmount']['currencyCode'] = $this->currencyCode;
        }

        if (!empty($this->softDescriptor)) {
            $data['softDescriptor'] = $this->softDescriptor;
        } else {
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
     * @param string $id
     * @return void
     */
    public function setPlatformId(string $id)
    {
        $this->platformId = $id;
    }

    /**
     * @param string $paymentIntent
     */
    public function setPaymentIntent($paymentIntent): void
    {
        $this->paymentIntent = $paymentIntent;
    }

    /**
     * @param string $merchantStoreName
     */
    public function setMerchantStoreName($merchantStoreName): void
    {
        $this->merchantStoreName = $merchantStoreName;
    }

    /**
     * @param string $noteToBuyer
     */
    public function setNoteToBuyer($noteToBuyer): void
    {
        $this->noteToBuyer = $noteToBuyer;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @param bool $canHandlePendingAuthorization
     */
    public function setCanHandlePendingAuthorization($canHandlePendingAuthorization): void
    {
        $this->canHandlePendingAuthorization = $canHandlePendingAuthorization;
    }

    /**
     * @param string $paymentDetailsChargeAmount
     */
    public function setPaymentDetailsChargeAmount($paymentDetailsChargeAmount): void
    {
        $this->paymentDetailsChargeAmount = PhpHelper::getMoneyValue((float)$paymentDetailsChargeAmount);
    }

    /**
     * @param string $merchantReferenceId
     */
    public function setMerchantReferenceId($merchantReferenceId): void
    {
        $this->merchantReferenceId = $merchantReferenceId;
    }

    /**
     * @param string $softDescriptor
     */
    public function setSoftDescriptor($softDescriptor): void
    {
        $this->softDescriptor = $softDescriptor;
    }

    /**
     * @param string $captureAmount
     */
    public function setCaptureAmount($captureAmount): void
    {
        $this->captureAmount = PhpHelper::getMoneyValue((float)$captureAmount);
    }

    /**
     * @param string $checkoutChargeAmount
     */
    public function setCheckoutChargeAmount($checkoutChargeAmount): void
    {
        $this->checkoutChargeAmount = $checkoutChargeAmount;
    }

    /**
     * @return void
     */
    public function setCheckoutReviewReturnUrl($articlesId = null): void
    {
        $this->checkoutReviewReturnUrl =
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->checkoutReviewUrl() .
            (is_null($articlesId) ? '' : ('&anid=' . $articlesId));
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
        $addressLine1 = sprintf(
            '%s %s',
            $user->oxuser__oxstreet,
            $user->oxuser__oxstreetnr,
        );

        $oCountry = oxNew(Country::class);
        $oCountry->load($user->oxuser__oxcountryid);
        $sCountryCode = $oCountry->oxcountry__oxisoalpha2->value;


        // set mandatory standard fields
        $this->addressDetails = [
            'name' => $user->oxuser__oxfname->value . ' ' . $user->oxuser__oxlname->value,
            'addressLine1' => $addressLine1,
            'postalCode' => $user->oxuser__oxzip->value,
            'city' => $user->oxuser__oxcity->value,
            'countryCode' => $sCountryCode
        ];

        // check for additional fields
        // check for phone number
        $phoneNumber = null;
        if (!empty($user->oxuser__oxfon->value)) { // phone number
            $phoneNumber = $user->oxuser__oxfon->value;
        } elseif (!empty($user->oxuser__oxprivfon->value)) { // phone number (private)
            $phoneNumber = $user->oxuser__oxprivfon->value;
        } elseif (!empty($user->oxuser__oxmobfon->value)) { // phone number (private)
            $phoneNumber = $user->oxuser__oxmobfon->value;
        }
        /** TODO Change default number to  0 */
        $this->addressDetails['phoneNumber'] = $phoneNumber ?? '0'; // when no number was provided, Amazon accepts '0'
        return $this;
    }
}
