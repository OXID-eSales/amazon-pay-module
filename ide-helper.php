<?php

declare(strict_types=1);

/*
 * This file is part of Mount7 GmbH EasyCredit module
 *
 * Copyright (C) Mount7 GmbH
 * Portions Copyright (C) OXID eSales AG 2003-2022
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

use OxidEsales\EshopIdeHelper\Core\DirectoryScanner;
use OxidEsales\EshopIdeHelper\Core\ModuleExtendClassMapProvider;
use OxidEsales\EshopIdeHelper\Core\ModuleMetadataParser;
use OxidEsales\EshopIdeHelper\Generator;
use OxidEsales\EshopIdeHelper\HelpFactory;

require __DIR__ . '/vendor/autoload.php';

try {
    /**
     * Custom location since this is a library
     */
    $parser = new ModuleMetadataParser((new DirectoryScanner('metadata.php', __DIR__)));
    $moduleExtendClassMapProvider = new ModuleExtendClassMapProvider($parser);
    $helpFactory = new HelpFactory();
    $generator = new Generator(
        $helpFactory->getFacts(),
        $helpFactory->getUnifiedNameSpaceClassMapProvider(),
        $helpFactory->getBackwardsCompatibilityClassMapProvider(),
        $moduleExtendClassMapProvider
    );
    $generator->generate();
} catch (\Exception $exception) {
    $message = $exception->getMessage();
    $code = $exception->getCode();
    $traceString = $exception->getTraceAsString();

    echo $message . PHP_EOL;
    echo "error code: $code" . PHP_EOL;
    echo "stack trace:" . PHP_EOL;
    echo $traceString . PHP_EOL;
    exit(1);
}
