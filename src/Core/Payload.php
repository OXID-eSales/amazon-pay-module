<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use OxidEsales\Eshop\Application\Model\Address;
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
     * @var ?bool
     */
    private $canHandlePendingAuthorization = null;

    /**
     * @var string
     */
    private $merchantStoreName = '';

    /**
     * @var string
     */
    private $noteToBuyer = '';

    /**
     * @var string
     */
    private $paymentDetailsChargeAmount = '';

    /**
     * @var string
     */
    private $checkoutChargeAmount = '';

    /**
     * @var string
     */
    private $captureAmount = '';

    /**
     * @var string
     */
    private $currencyCode = '';

    /**
     * @var string
     */
    private $softDescriptor = '';

    /**
     * @var string
     */
    private $merchantReferenceId = '';

    /**
     * @var string
     */
    private $checkoutReviewReturnUrl = '';

    /**
     * @var string
     */
    private $checkoutResultReturnUrl = '';

    /**
     * @var string
     */
    private $signInReturnUrl = '';

    /**
     * @var string
     */
    private $signInCancelUrl = '';

    /**
     * @var string
     */
    private $storeId = '';

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
    private $addressDetails = [];
    /**
     * @var string
     */
    private $platformId = '';
    private $addressRestrictions = [];

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

        // why this condition? the official docs https://developer.amazon.com/docs/amazon-pay-api-v2/checkout-session.html#OLS9CAGyE7P
        // says Description shown on the buyer payment instrument statement. You can only use this parameter if
        // paymentIntent is set to AuthorizeWithCapture
        //
        // so why adding addMerchantMetaData is bound to the condition that softDescriptor is not empty???
        // to the reviewer: please have a look at this commit: 8f009cbe, in my eyes it seems that the condition was
        // changed accidentally in intention to only fix code style issues?
        // this change reverts the logic before the commit 8f009cbe, so if you think this is correct just remove this
        // long comment and merge to 6.1 branch, this fix is for normal payment express payment still need a fix for
        // sending merchantReferenceId
        $data['softDescriptor'] = $this->softDescriptor;
        if (empty($data['softDescriptor'])) {
            $data['softDescriptor'] = '';
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

    public function setAddressRestrictions(array $allowedCountries)
    {
        $this->addressRestrictions = $allowedCountries;
    }

    /**
     * @param string $platformId
     * @return void
     */
    public function setPlatformId(string $platformId)
    {
        $this->platformId = $platformId;
    }

    /**
     * @param string $paymentIntent
     */
    public function setPaymentIntent(string $paymentIntent)
    {
        $this->paymentIntent = $paymentIntent;
    }

    /**
     * @param string $merchantStoreName
     */
    public function setMerchantStoreName(string $merchantStoreName)
    {
        $this->merchantStoreName = $merchantStoreName;
    }

    /**
     * @param string $noteToBuyer
     */
    public function setNoteToBuyer(string $noteToBuyer)
    {
        $this->noteToBuyer = $noteToBuyer;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode(string $currencyCode)
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @param bool $canHandlePendingAuthorization
     */
    public function setCanHandlePendingAuthorization(bool $canHandlePendingAuthorization)
    {
        $this->canHandlePendingAuthorization = $canHandlePendingAuthorization;
    }

    /**
     * @param string $paymentDetailsChargeAmount
     */
    public function setPaymentDetailsChargeAmount(string $paymentDetailsChargeAmount)
    {
        $this->paymentDetailsChargeAmount = PhpHelper::getMoneyValue((float)$paymentDetailsChargeAmount);
    }

    /**
     * @param string $merchantReferenceId
     */
    public function setMerchantReferenceId(string $merchantReferenceId)
    {
        $this->merchantReferenceId = $merchantReferenceId;
    }

    /**
     * @param string $softDescriptor
     */
    public function setSoftDescriptor(string $softDescriptor)
    {
        $this->softDescriptor = $softDescriptor;
    }

    /**
     * @param string $captureAmount
     */
    public function setCaptureAmount(string $captureAmount)
    {
        $this->captureAmount = PhpHelper::getMoneyValue((float)$captureAmount);
    }

    /**
     * @param string $checkoutChargeAmount
     */
    public function setCheckoutChargeAmount(string $checkoutChargeAmount)
    {
        $this->checkoutChargeAmount = $checkoutChargeAmount;
    }

    /**
     * @param string $articlesId
     * @return void
     */
    public function setCheckoutReviewReturnUrl(string $articlesId = '')
    {
        $this->checkoutReviewReturnUrl =
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->checkoutReviewUrl() .
            ($articlesId !== '' ? '&anid=' . $articlesId : '');
    }

    /**
     * @return void
     */
    public function setSignInReturnUrl()
    {
        $this->signInReturnUrl =
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->signInReturnUrl();
    }

    /**
     * @return void
     */
    public function setSignInCancelUrl()
    {
        $this->signInCancelUrl =
            OxidServiceProvider::getAmazonClient()->getModuleConfig()->signInCancelUrl();
    }

    /**
     * @return void
     */
    public function setCheckoutResultReturnUrl()
    {
        $this->checkoutResultReturnUrl = Registry::getConfig()->getCurrentShopUrl(false)
            . 'index.php?cl=order&fnc=execute&action=result&stoken='
            . Registry::getSession()->getSessionChallengeToken();
    }

    /**
     * @return void
     */
    public function setCheckoutResultReturnUrlExpress()
    {
        $this->checkoutResultReturnUrl = OxidServiceProvider::getAmazonClient()->getModuleConfig()->checkoutResultUrl();
    }

    /**
     * @return void
     */
    public function setStoreId()
    {
        $this->storeId = OxidServiceProvider::getAmazonClient()->getModuleConfig()->getStoreId();
    }

    /**
     * @param array $scopes
     * @return void
     */
    public function addScopes(array $scopes)
    {
        $this->scopes = array_merge($this->scopes, $scopes);
    }

    public function addSignInScopes(array $scopes)
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
        $data['merchantMetadata']['merchantReferenceId'] = $this->merchantReferenceId;
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
            $oxstreetnr
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

    /**
     * @param Address $address
     * @return Payload
     */
    public function setAddressDetailsFromDeliveryAddress(Address $address)
    {
        /** @var string $oxstreet */
        $oxstreet = $address->getFieldData('oxstreet');
        /** @var string $oxstreetnr */
        $oxstreetnr = $address->getFieldData('oxstreetnr');
        /** @var string $oxfname */
        $oxfname = $address->getFieldData('oxfname');
        /** @var string $oxlname */
        $oxlname = $address->getFieldData('oxlname');
        /** @var string $oxzip */
        $oxzip = $address->getFieldData('oxzip');
        /** @var string $oxcity */
        $oxcity = $address->getFieldData('oxcity');
        /** @var string $oxfon */
        $oxfon = $address->getFieldData('oxfon');
        /** @var string $oxprivfon */
        $oxprivfon = $address->getFieldData('oxprivfon');
        /** @var string $oxmobfon */
        $oxmobfon = $address->getFieldData('oxmobfon');

        $addressLine1 = sprintf(
            '%s %s',
            $oxstreet,
            $oxstreetnr
        );

        /** @var string $oxcountryid */
        $oxcountryid = $address->getFieldData('oxcountryid');
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
