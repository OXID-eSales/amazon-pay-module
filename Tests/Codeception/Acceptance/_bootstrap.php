<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

// This is acceptance bootstrap
use OxidEsales\Codeception\Module\FixturesHelper;

require __DIR__ . '/BaseCest.php';

$helper = new FixturesHelper();
$helper->loadRuntimeFixtures(__DIR__ . '/../_data/fixtures.php');
$helper->loadRuntimeFixtures(__DIR__ . '/../_data/amazonpayData.php');
