<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\Helper\AmazonCarrier;
use OxidEsales\Eshop\Application\Model\DeliverySet;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\DeliverySetMain
 */
class DeliverySetMain extends DeliverySetMain_parent
{
    public function render()
    {
        $amazonCarriers = AmazonCarrier::getAllCarriers();

        $this->addTplParam('amazonCarriers', $amazonCarriers);

        $oxId = $this->getEditObjectId();

        if ($oxId !== -1) {
            $deliverySet = oxNew(DeliverySet::class);
            $deliverySet->load($oxId);

            if ($deliverySet->oxdeliveryset__oxps_amazon_carrier->rawValue !== null) {
                $this->addTplParam('selectedAmazonCarrier', $deliverySet->oxdeliveryset__oxps_amazon_carrier);
            } else {
                // Default
                $this->addTplParam('selectedAmazonCarrier', 'NULL');
            }
        }

        return parent::render();
    }

    /**
     * Saves deliveryset information changes.
     *
     * @return mixed
     */
    public function save()
    {
        $result = parent::save();

        $aParams = Registry::getRequest()->getRequestParameter('editval');
        $aParams['oxdeliveryset__oxps_amazon_carrier'] = Registry::getRequest()
            ->getRequestParameter('editAmazonCarrier');
        $oDelSet = oxNew(DeliverySet::class);
        $oDelSet->assign($aParams);
        $oDelSet->save();

        // set oxid if inserted
        $this->setEditObjectId($oDelSet->getId());

        return $result;
    }
}
