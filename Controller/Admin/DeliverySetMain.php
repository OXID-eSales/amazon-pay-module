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
     */
    public function save()
    {
        parent::save();

        $aParams = Registry::getRequest()->getRequestParameter('editval');
        $aParams['oxdeliveryset__oxps_amazon_carrier'] = Registry::getRequest()
            ->getRequestParameter('editAmazonCarrier');
        $oDelSet = oxNew(DeliverySet::class);
        $oDelSet->assign($aParams);
        $oDelSet->save();

        // set oxid if inserted
        $this->setEditObjectId($oDelSet->getId());
    }
}
