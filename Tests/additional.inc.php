<?php

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Facts\Facts;
use Webmozart\PathUtil\Path;

$facts    = new Facts();
$settings = include Path::join(
    __DIR__,
    'Unit',
    'amazonpayData.php'
);

define('STORE_ID', $settings['storeId']);
define('MERCHANT_ID', $settings['merchantId']);
define('PUBLIC_KEY_ID', $settings['publicKeyId']);
define('PRIVATE_KEY', $settings['privateKey']);

try {
    DatabaseProvider::getDb()->execute("DELETE FROM oxconfig WHERE  oxvarname = 'aModuleControllers'");
} catch (DatabaseConnectionException $e) {
} catch (DatabaseErrorException $e) {
}
