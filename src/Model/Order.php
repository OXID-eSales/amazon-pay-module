<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Model;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Model\BaseModel;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonService;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\PhpHelper;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;
use OxidSolutionCatalysts\AmazonPay\Core\Repository\LogRepository;

/**
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order extends Order_parent
{
    private AmazonService $amazonService;

    /**
     * Security and Cleanup before finalize order
     *
     * @param Basket $oBasket Basket object
     * @return int|null
     *
     */
    protected function prepareFinalizeOrder(Basket $oBasket): ?int
    {
        $paymentId = $oBasket->getPaymentId() ?: '';
        // if payment is 'oxidamazon' but we do not have an Amazon Pay Session
        // stop finalize order
        if (
            Constants::isAmazonPayment($paymentId) &&
            !OxidServiceProvider::getAmazonService()->isAmazonSessionActive()
        ) {
            return self::ORDER_STATE_PAYMENTERROR; // means no authentication
        }
        return 0;
    }

    /**
     * Order checking, processing and saving method.
     *
     * @param Basket $oBasket Basket object
     * @param object $oUser Current User object
     * @param bool $blRecalculatingOrder Order recalculation
     *
     * @return integer
     */
    public function finalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false): ?int
    {
        $ret = $this->prepareFinalizeOrder($oBasket);

        if ($ret !== self::ORDER_STATE_PAYMENTERROR) {
            $ret = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
        }

        // Authorize and Capture via Amazon Pay will be done after finalizeOrder in OXID
        // therefore we reset status to "not finished yet"
        $paymentId = $oBasket->getPaymentId() ?: '';
        $isAmazonPayment = Constants::isAmazonPayment($paymentId);
        if (
            $ret < 2 &&
            !$blRecalculatingOrder &&
            $isAmazonPayment
        ) {
            $this->updateAmazonPayOrderStatus('AMZ_PAYMENT_PENDING');
        }
        return $ret;
    }

    /**
     * If Amazon Pay is active, it will return an address from Amazon
     *
     * @return \OxidEsales\Eshop\Application\Model\Address|null
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     *
     */
    public function getDelAddressInfo()
    {
        $amazonService = $this->getAmazonService();
        $amazonDelAddress = $amazonService->getDeliveryAddress();
        if (
            !$amazonService->isAmazonSessionActive() ||
            !$amazonDelAddress
        ) {
            return parent::getDelAddressInfo();
        }

        $address = oxNew(Address::class);
        $address->assign($amazonDelAddress);

        return $address;
    }

    /**
     * Disabling validation for Amazon addresses when Amazon Pay is active
     *
     * @param $oUser
     *
     * @return int
     */
    public function validateDeliveryAddress($oUser)
    {
        if (!$this->getAmazonService()->isAmazonSessionActive()) {
            return parent::validateDeliveryAddress($oUser);
        }

        return 0; // disable validation
    }

    public function updateAmazonPayOrderStatus(string $amazonPayStatus, array $data = []): void
    {
        if (!empty($data) && $data['chargeId']) {
            $this->_setFieldData('oxtransid', $data['chargeId']);
        }

        switch ($amazonPayStatus) {
            case "AMZ_PAYMENT_PENDING":
                $this->_setFieldData('oxtransstatus', 'NOT_FINISHED');
                $this->_setFieldData('oxfolder', 'ORDERFOLDER_PROBLEMS');
                $this->_setFieldData('osc_amazon_remark', 'AmazonPay Authorisation pending');
                $this->save();
                break;

            case "AMZ_AUTH_STILL_PENDING":
                if (is_array($data)) {
                    $this->_setFieldData(
                        'osc_amazon_remark',
                        'AmazonPay Authorisation still pending: '
                        . $data['chargeAmount']
                    );
                }
                $this->save();
                break;

            case "AMZ_AUTH_AND_CAPT_FAILED":
                $remark = 'AmazonPay: ERROR';
                if (!empty($data['result']['response'])) {
                    $response = PhpHelper::jsonToArray($data['result']['response']);
                    $remark = 'AmazonPay ERROR: ' . $response['reasonCode'];
                }

                $this->_setFieldData('osc_amazon_remark', $remark);
                $this->save();
                break;

            case "AMZ_AUTH_AND_CAPT_OK":
                // we move the order only if the oxtransstatus not OK before
                if ($this->getFieldData('oxpaid') == '0000-00-00 00:00:00') {
                    $this->_setFieldData('oxfolder', 'ORDERFOLDER_NEW');
                }
                $this->_setFieldData('oxpaid', \date('Y-m-d H:i:s'));
                $this->_setFieldData('oxtransstatus', 'OK');
                if (is_array($data)) {
                    $this->_setFieldData('osc_amazon_remark', 'AmazonPay Captured: ' . $data['chargeAmount']);
                }
                $this->save();
                break;

            case "AMZ_2STEP_AUTH_OK":
                if (!empty($data['chargeAmount'])) {
                    $this->_setFieldData(
                        'osc_amazon_remark',
                        'AmazonPay Authorized (not Captured):' . $data['chargeAmount']
                    );
                }
                $this->_setFieldData('oxfolder', 'ORDERFOLDER_NEW');
                $this->save();
                break;
        }
    }

    /**
     * Just a helper to allow mock injection for testing
     * @return AmazonService
     */
    public function getAmazonService(): AmazonService
    {

        if (empty($this->amazonService)) {
            $this->setAmazonService(OxidServiceProvider::getAmazonService());
            return $this->amazonService;
        }
        return $this->amazonService;
    }

    /**
     * @param AmazonService $amazonService
     */
    public function setAmazonService(AmazonService $amazonService): void
    {
        $this->amazonService = $amazonService;
    }

    /**
     * @inheritdoc
     */
    public function delete($sOxId = null): bool
    {
        $sOxId = $sOxId ?: $this->getId();
        if (!$sOxId) {
            return false;
        }

        if (!$this->canDelete($sOxId)) {
            return false;
        }

        OxidServiceProvider::getAmazonService()->processCancel($sOxId);

        $repository = oxNew(LogRepository::class);
        $repository->deleteLogMessageByOrderId($sOxId);

        return parent::delete($sOxId);
    }

    /** @inheritdoc  */
    public function canDelete($oxid = null): bool
    {
        $oxid = $oxid ?: $this->getId();
        if (!$oxid) {
            return false;
        }

        $repository = oxNew(LogRepository::class);
        $logMessage = $repository->findLogMessageForOrderId($oxid);

        if (
            isset($logMessage[0]['OSC_AMAZON_RESPONSE_MSG']) &&
            in_array(
                $logMessage[0]['OSC_AMAZON_RESPONSE_MSG'],
                [
                    'Captured',
                    'Completed & Captured',
                    'Refunded'
                ]
            )
        ) {
            /** @var string $deleteError */
            $deleteError = Registry::getLang()->translateString('OSC_AMAZONPAY_DELETE_ERROR');
            Registry::getUtilsView()->addErrorToDisplay($deleteError);
            return false;
        }

        if (method_exists(Order_parent::class, 'canDelete')) {
            return parent::canDelete($oxid);
        }
        return true;
    }
}
