<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleSettingNotFountException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Controller for admin > Amazon Pay/Configuration page
 */
class ConfigController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->_sThisTemplate = '@osc_amazonpay/admin/amazonconfig';
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
            Registry::getUtilsView()->addErrorToDisplay(
                $e,
                false,
                true,
                'amazonpay_error'
            );
        }


        return $thisTemplate;
    }

    /**
     * Saves configuration values
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ModuleSettingNotFountException
     * @throws NotFoundExceptionInterface
     */
    public function save()
    {
        $confArr = (array)Registry::getRequest()->getRequestEscapedParameter('conf');
        $shopId = (string)Registry::getConfig()->getShopId();

        $confArr = $this->handleSpecialFields($confArr);
        $this->saveConfig($confArr, $shopId);

        parent::save();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function saveConfig(array $conf, string $shopId)
    {
        $oModuleConfiguration = null;
        $oModuleConfigurationDaoBridge = null;
        $oModuleActivationBridge = null;
        if ($this->useDaoBridge()) {
            /** @var ModuleActivationBridgeInterface $oModuleActivationBridge */
            $oModuleActivationBridge = ContainerFactory::getInstance()->getContainer()->get(
                ModuleActivationBridgeInterface::class
            );
            $oModuleActivationBridge->deactivate(Constants::MODULE_ID, $shopId);

            /** @var ModuleConfigurationDaoBridgeInterface $oModuleConfigurationDaoBridge */
            $oModuleConfigurationDaoBridge = ContainerFactory::getInstance()->getContainer()->get(
                ModuleConfigurationDaoBridgeInterface::class
            );
            $oModuleConfiguration = $oModuleConfigurationDaoBridge->get(Constants::MODULE_ID);
        }

        foreach ($conf as $confName => $value) {
            $value = trim($value);
            if ($this->useDaoBridge()) {
                $oModuleSetting = $oModuleConfiguration->getModuleSetting($confName);
                $value = $oModuleSetting->getType() === 'bool' ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : $value;
                $value = $oModuleSetting->getType() === 'str' ? trim($value) : $value;
                $oModuleSetting->setValue($value);
            }
            if (!$this->useDaoBridge()) {
                $type = strpos($confName, 'bl') ? 'bool' : 'str';
                $value = $type === 'bool' ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : $value;
                $value = $type === 'str' ? trim($value) : $value;

                Registry::getConfig()->saveShopConfVar(
                    $type,
                    $confName,
                    $value,
                    $shopId,
                    'module:' . Constants::MODULE_ID
                );
            }
        }
        if ($this->useDaoBridge()) {
            $oModuleConfigurationDaoBridge->save($oModuleConfiguration);
            $oModuleActivationBridge->activate(Constants::MODULE_ID, $shopId);
        }
    }

    /**
     * Handles checkboxes/dropdowns
     *
     * @param array $conf
     *
     * @return array
     */
    protected function handleSpecialFields(array $conf): array
    {
        $config = new Config();
        $conf['blAmazonPaySandboxMode'] = $conf['blAmazonPaySandboxMode'] === 'sandbox' ? true : false;

        // remove \r\n from the keys
        // because the key string is saved with single ticks in yaml that lead to memory overflow issues
        // keys with \r\n will be loaded correctly with double ticks in yaml
        $conf['sAmazonPayPrivKey'] = str_replace(['\r\n', '\r', '\n'], '', $conf['sAmazonPayPrivKey']);

        // remove FakePrivateKeys before save
        if ($conf['sAmazonPayPrivKey'] === '' || $conf['sAmazonPayPrivKey'] === $config->getFakePrivateKey()) {
            unset($conf['sAmazonPayPrivKey']);
        }

        if (!isset($conf['amazonPayCapType'])) {
            $conf['amazonPayCapType'] = '1';
        }

        if (!isset($conf['blAmazonPayExpressPDP'])) {
            $conf['blAmazonPayExpressPDP'] = false;
        }

        if (!isset($conf['blAmazonSocialLoginDeactivated'])) {
            $conf['blAmazonSocialLoginDeactivated'] = false;
        }

        if (!isset($conf['blAmazonPayExpressMinicartAndModal'])) {
            $conf['blAmazonPayExpressMinicartAndModal'] = false;
        }

        return $conf;
    }

    /**
     * check if using DaoBridge is possible
     *
     * @return boolean
     */
    protected function useDaoBridge(): bool
    {
        return class_exists(
            '\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory'
        );
    }
}
