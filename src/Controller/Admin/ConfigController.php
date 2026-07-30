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
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidSolutionCatalysts\AmazonPay\Core\Config;
use OxidSolutionCatalysts\AmazonPay\Core\Constants;

/**
 * Controller for admin > Amazon Pay/Configuration page
 */
class ConfigController extends AdminController
{
    // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore
    protected $_sThisTemplate = 'amazonpay/amazonconfig.tpl';

    /**
     * @inheritDoc
     *
     * @return string
     *
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
     */
    public function save()
    {
        $confArr = (array)Registry::getRequest()->getRequestEscapedParameter('conf');
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
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function saveConfig(array $conf, int $shopId)
    {
        $oModuleConfiguration = null;
        $oModuleConfigurationDaoBridge = null;
        /** @var ModuleActivationBridgeInterface $oModuleActivationBridge */
        $oModuleActivationBridge = null;
        if ($this->useDaoBridge()) {
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
                    (string)$shopId,
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
     * @return array
     */
    protected function handleSpecialFields(array $conf): array
    {
        $config = new Config();
        $conf['blAmazonPaySandboxMode'] = $conf['blAmazonPaySandboxMode'] === 'sandbox';

        // remove FakePrivateKeys before save
        if ($conf['sAmazonPayPrivKey'] === '' || $conf['sAmazonPayPrivKey'] === $config->getFakePrivateKey()) {
            unset($conf['sAmazonPayPrivKey']);
        }

        if (!isset($conf['amazonPayCapType'])) {
            $conf['amazonPayCapType'] = '1';
        }

        $conf['amazonPayLoginByEMail'] = $this->sanitizeLoginByEMailMode(
            $conf['amazonPayLoginByEMail'] ?? null
        );

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
     * Mode of the "sign in via the Amazon email address" feature as posted by the
     * admin form. Never falls back to an enabled state: an absent or unknown
     * value means "off".
     *
     * @param mixed $value
     * @return string one of the Constants::LOGIN_BY_EMAIL_* modes
     */
    protected function sanitizeLoginByEMailMode($value): string
    {
        return in_array(
            $value,
            [Constants::LOGIN_BY_EMAIL_GUEST_ONLY, Constants::LOGIN_BY_EMAIL_ALL],
            true
        ) ? (string)$value : Constants::LOGIN_BY_EMAIL_OFF;
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
