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

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidProfessionalServices\AmazonPay\Core\Config;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidProfessionalServices\AmazonPay\Core\Constants;

/**
 * Controller for admin > Amazon Pay/Configuration page
 */
class ConfigController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->_sThisTemplate = 'amazonpay/amazonconfig.tpl';
    }

    /**
     * @return string
     */
    public function render()
    {
        $thisTemplate = parent::render();

        $config = new Config();
        $this->addTplParam('config', $config);

        $displayPrivateKey = $config->getPrivateKey() ? $config->getFakePrivateKey() : '';
        $this->addTplParam('displayPrivateKey', $displayPrivateKey);

        try {
            $config->checkHealth();
        } catch (StandardException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e, false, true, 'amazonpay_error');
        }

        return $thisTemplate;
    }

    /**
     * Saves configuration values
     */
    public function save()
    {
        $confArr = Registry::getRequest()->getRequestEscapedParameter('conf');
        $shopId = Registry::getConfig()->getShopId();

        $confArr = $this->handleSpecialFields($confArr);
        $this->saveConfig($confArr, $shopId);

        parent::save();
    }

    /**
     * Saves configuration values
     *
     * @param array $conf
     * @param int $shopId
     */
    protected function saveConfig(array $conf, int $shopId): void
    {
        if ($this->useDaoBridge()) {
            $oModuleConfigurationDaoBridge = ContainerFactory::getInstance()->getContainer()->get(ModuleConfigurationDaoBridgeInterface::class);
            $oModuleConfiguration = $oModuleConfigurationDaoBridge->get(Constants::MODULE_ID);
        }

        foreach ($conf as $confName => $value) {
            $value = trim($value);
            if ($this->useDaoBridge()) {
                $oModuleSetting = $oModuleConfiguration->getModuleSetting($confName);
                $oModuleSetting->setValue($value);
                $oModuleConfigurationDaoBridge->save($oModuleConfiguration);
            }

            Registry::getConfig()->saveShopConfVar(
                strpos($confName, 'bl') !== false ? 'bool' : 'str',
                $confName,
                $value,
                $shopId,
                'module:' . Constants::MODULE_ID
            );
        }
    }

    /**
     * Handles cheboxes/dropdowns
     *
     * @param array $conf
     *
     * @return array
     */
    protected function handleSpecialFields(array $conf): array
    {
        $config = new Config();

        if ($conf['blAmazonPaySandboxMode'] === 'sandbox') {
            $conf['blAmazonPaySandboxMode'] = 1;
        } else {
            $conf['blAmazonPaySandboxMode'] = 0;
        }

        // remove FakePrivateKeys before save
        if (
            $conf['sAmazonPayPrivKey'] === '' ||
            $conf['sAmazonPayPrivKey'] === $config->getFakePrivateKey()
        ) {
            unset($conf['sAmazonPayPrivKey']);
        }

        if (!isset($conf['amazonPayCapType'])) {
            $conf['amazonPayCapType'] = '1';
        }

        if (!isset($conf['blAmazonPayPDP'])) {
            $conf['blAmazonPayPDP'] = 0;
        }

        if (!isset($conf['blAmazonPayMinicartAndModal'])) {
            $conf['blAmazonPayMinicartAndModal'] = 0;
        }

        if (!isset($conf['amazonPayType'])) {
            $conf['amazonPayType'] = 'PayAndShip';
        }

        return $conf;
    }

    /**
    * check if using DaoBridge is possible
    *
    * @return boolean
    */
    protected function useDaoBridge()
    {
        return class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory');
    }
}
