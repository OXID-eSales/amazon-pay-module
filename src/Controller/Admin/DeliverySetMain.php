<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Helper\AmazonCarrier;
use OxidEsales\Eshop\Application\Model\DeliverySet;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\DeliverySetMain
 */
class DeliverySetMain extends DeliverySetMain_parent
{
    public function render(): string
    {
        $amazonCarriers = AmazonCarrier::getAllCarriers();

        $this->addTplParam('amazonCarriers', $amazonCarriers);

        $oxId = $this->getEditObjectId();

        if ($oxId !== -1) {
            $deliverySet = oxNew(DeliverySet::class);
            $deliverySet->load($oxId);
            $amazonCarrier = $deliverySet->getRawFieldData('osc_amazon_carrier');

            $selectedAmazonCarrier = $amazonCarrier != null ? $amazonCarrier : 'NULL';
            $this->addTplParam('selectedAmazonCarrier', $selectedAmazonCarrier);
        }

        return parent::render();
    }

    /**
     * @inheritdoc
     * @throws \Exception
     * @returns mixed
     */
    public function save()
    {
        $result = parent::save();

        $objectId = $this->getEditObjectId();
        $aParams = [];
        $aParams['oxdeliveryset__osc_amazon_carrier'] = Registry::getRequest()
            ->getRequestParameter('editAmazonCarrier');
        $oDelSet = oxNew(DeliverySet::class);
        if ($oDelSet->load($objectId)) {
            $oDelSet->assign($aParams);
            $oDelSet->save();
        }

        return $result;
    }
}
