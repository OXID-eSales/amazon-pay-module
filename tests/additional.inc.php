<?php

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

DatabaseProvider::getDb()->execute("DELETE FROM oxconfig WHERE  oxvarname = 'aModuleControllers'");
