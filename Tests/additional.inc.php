<?php

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

try {
    DatabaseProvider::getDb()->execute("DELETE FROM oxconfig WHERE  oxvarname = 'aModuleControllers'");
} catch (DatabaseConnectionException $e) {
} catch (DatabaseErrorException $e) {
}
