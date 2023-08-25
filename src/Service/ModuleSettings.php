<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSolutionCatalysts\AmazonPay\Core\AmazonPayModule;

final class ModuleSettings
{
    /** @var ModuleSettingServiceInterface */
    private $moduleSettingService;

    public function __construct(
        ModuleSettingServiceInterface $moduleSettingService
    ) {
        $this->moduleSettingService = $moduleSettingService;
    }

    protected function getStringSettingValue(string $key): string
    {
        return $this->moduleSettingService->getString(
            $key,
            AmazonPayModule::MODULE_ID
        )->trim()->toString();
    }

    protected function getBoolSettingValue(string $key): bool
    {
        return $this->moduleSettingService->getBoolean(
            $key,
            AmazonPayModule::MODULE_ID
        );
    }
}
