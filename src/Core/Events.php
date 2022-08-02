<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\AmazonPay\Core\Repository\LogRepository;

class Events
{
    /**
     * Force session start for details-controller, so Amazon-Buttons works everytime
     *
     * @var array
     */
    protected static $requireSessionWithParams = [
        'cl' => [
            'details'        => true,
            'amazondispatch' => true
        ]
    ];

    /**
     * Execute action on activate event
     */
    public static function onActivate(): void
    {
        self::updateOxpsToOsc();
        self::createLogTable();
        self::addPaymentMethod();
        self::enablePaymentMethod();
        self::addArticleColumn();
        self::addCategoryColumn();
        self::addDeliverySetColumn();
        self::addOrderColumn();
        self::addRequireSession();

        $dbMetaDataHandler = oxNew(DbMetaDataHandler::class);
        $dbMetaDataHandler->updateViews();
    }

    protected static function updateOxpsToOsc(): void
    {
        self::updateOxpsToOscArticleColumn();
        self::updateOxpsToOscCategoryColumn();
        self::updateOxpsToOscDeliverySetColumn();
        self::updateOxpsToOscOrderColumn();
        self::updateOxpsToOscLogTable();
    }

    protected static function updateOxpsToOscArticleColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxarticles') . '\'
                AND COLUMN_NAME = \'OXPS_AMAZON_EXCLUDE\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result)) {
            $sql = 'ALTER TABLE `oxarticles` CHANGE `OXPS_AMAZON_EXCLUDE` `OSC_AMAZON_EXCLUDE`
                                tinyint(1)
                                NOT NULL
                                DEFAULT 0
                                COMMENT \'Exclude from amazonpay\'';
            DatabaseProvider::getDb()->execute($sql);
        }
    }

    protected static function addArticleColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxarticles') . '\'
                AND COLUMN_NAME = \'OSC_AMAZON_EXCLUDE\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result) === 0) {
            $sql = 'ALTER TABLE `oxarticles` ADD COLUMN `OSC_AMAZON_EXCLUDE`
                                tinyint(1)
                                NOT NULL
                                DEFAULT 0
                                COMMENT \'Exclude from amazonpay\'';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    protected static function updateOxpsToOscCategoryColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxcategories') . '\'
                AND COLUMN_NAME = \'OXPS_AMAZON_EXCLUDE\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result)) {
            $sql = 'ALTER TABLE `oxcategories` CHANGE `OXPS_AMAZON_EXCLUDE` `OSC_AMAZON_EXCLUDE`
                                tinyint(1)
                                NOT NULL
                                DEFAULT 0
                                COMMENT \'Exclude from amazonpay\'';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    protected static function addCategoryColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxcategories') . '\'
                AND COLUMN_NAME = \'OSC_AMAZON_EXCLUDE\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result) === 0) {
            $sql = 'ALTER TABLE `oxcategories` ADD COLUMN `OSC_AMAZON_EXCLUDE`
                                tinyint(1)
                                NOT NULL
                                DEFAULT 0
                                COMMENT \'Exclude from amazonpay\'';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    protected static function updateOxpsToOscDeliverySetColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = sprintf('SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxdeliveryset') . '\'
                AND COLUMN_NAME = \'OXPS_AMAZON_CARRIER\'');

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result)) {
            $sql = 'ALTER TABLE `oxdeliveryset` CHANGE `OXPS_AMAZON_CARRIER` `OSC_AMAZON_CARRIER`
                                VARCHAR (100)';

            DatabaseProvider::getDb()->execute($sql);
        }
    }


    protected static function addDeliverySetColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = sprintf('SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxdeliveryset') . '\'
                AND COLUMN_NAME = \'OSC_AMAZON_CARRIER\'');

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result) === 0) {
            $sql = 'ALTER TABLE `oxdeliveryset` ADD COLUMN `OSC_AMAZON_CARRIER`
                                VARCHAR (100)';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    protected static function updateOxpsToOscOrderColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxorder') . '\'
                AND COLUMN_NAME = \'OXPS_AMAZON_REMARK\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result)) {
            $sql = 'ALTER TABLE `oxorder` CHANGE `OXPS_AMAZON_REMARK` `OSC_AMAZON_REMARK`
                                varchar(255)
                                NOT NULL
                                DEFAULT ""
                                COMMENT \'Remark from amazonpay\'';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    protected static function addOrderColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxorder') . '\'
                AND COLUMN_NAME = \'OSC_AMAZON_REMARK\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result) === 0) {
            $sql = 'ALTER TABLE `oxorder` ADD COLUMN `OSC_AMAZON_REMARK`
                                varchar(255)
                                NOT NULL
                                DEFAULT ""
                                COMMENT \'Remark from amazonpay\'';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    /**
     * Add PayPal payment method set EN and DE long descriptions
     */
    protected static function addPaymentMethod(): void
    {
        $payment = oxNew(Payment::class);
        if (!$payment->load(Constants::PAYMENT_ID)) {
            $payment->setId(Constants::PAYMENT_ID);
            $params = [
                'oxpayments__oxactive' => true,
                'oxpayments__oxaddsum' => 0,
                'oxpayments__oxaddsumtype' => 'abs',
                'oxpayments__oxfromboni' => 0,
                'oxpayments__oxfromamount' => 0,
                'oxpayments__oxtoamount' => 10000
            ];
            $payment->assign($params);
            $payment->save();

            $languages = Registry::getLang()->getLanguageIds();
            foreach (Constants::PAYMENT_DESCRIPTIONS as $languageAbbreviation => $values) {
                $languageId = array_search($languageAbbreviation, $languages, true);
                if ($languageId !== false) {
                    $payment->loadInLang($languageId, Constants::PAYMENT_ID);
                    $params = [
                        'oxpayments__oxdesc' => $values['title'],
                        'oxpayments__oxlongdesc' => $values['desc']
                    ];
                    $payment->assign($params);
                    $payment->save();
                }
            }
        }
    }

    /**
     * Disables payment method
     */
    protected static function disablePaymentMethod(): void
    {
        $payment = oxNew(Payment::class);
        if ($payment->load(Constants::PAYMENT_ID)) {
            $payment->oxpayments__oxactive = new Field(0);
            $payment->save();
        }
    }

    /**
     * Activates PayPal payment method
     */
    protected static function enablePaymentMethod(): void
    {
        $payment = oxNew(Payment::class);
        $payment->load(Constants::PAYMENT_ID);
        $payment->oxpayments__oxactive = new Field(1);
        $payment->save();
    }

    /**
     * Execute action on deactivate event
     *
     * @return void
     */
    protected static function onDeactivate(): void
    {
        self::disablePaymentMethod();
    }

    /**
     * add details controller to requireSession
     */
    protected static function addRequireSession(): void
    {
        $config = Registry::getConfig();
        $cfg = $config->getConfigParam('aRequireSessionWithParams');
        $cfg = is_array($cfg) ? $cfg : [];
        $cfg = array_merge_recursive($cfg, self::$requireSessionWithParams);
        $config->saveShopConfVar('arr', 'aRequireSessionWithParams', $cfg, (string)$config->getShopId());
    }

    protected static function updateOxpsToOscLogTable(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName(LogRepository::TABLE_NAME) . '\'
                AND COLUMN_NAME = \'OXPS_AMAZON_PAYLOGID\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result)) {
            $sql = 'ALTER TABLE `amazonpaylog`
                CHANGE `OXPS_AMAZON_PAYLOGID` `OSC_AMAZON_PAYLOGID`
                    char(32) COLLATE \'latin1_general_ci\'
                    NOT NULL COMMENT \'Record id\',
                CHANGE `OXPS_AMAZON_OXSHOPID` `OSC_AMAZON_OXSHOPID`
                    char(32) COLLATE \'latin1_general_ci\'
                    NOT NULL COMMENT \'Shop id (oxshops)\',
                CHANGE `OXPS_AMAZON_OXUSERID` `OSC_AMAZON_OXUSERID`
                    char(32) COLLATE \'latin1_general_ci\'
                    NOT NULL COMMENT \'User id (oxuser)\',
                CHANGE `OXPS_AMAZON_OXORDERID` `OSC_AMAZON_OXORDERID`
                    char(32) COLLATE \'latin1_general_ci\'
                    NOT NULL COMMENT \'Order id (oxorder)\',
                CHANGE `OXPS_AMAZON_RESPONSE_MSG` `OSC_AMAZON_RESPONSE_MSG`
                    text COLLATE \'utf8_general_ci\'
                    NOT NULL COMMENT \'Response from Amazon SDK\',
                CHANGE `OXPS_AMAZON_STATUS_CODE` `OSC_AMAZON_STATUS_CODE`
                    varchar(100) COLLATE \'utf8_general_ci\'
                    NOT NULL COMMENT \'Status code from Amazon SDK\',
                CHANGE `OXPS_AMAZON_REQUEST_TYPE` `OSC_AMAZON_REQUEST_TYPE`
                    varchar(100) COLLATE \'utf8_general_ci\'
                    NOT NULL COMMENT \'Request type\',
                CHANGE `OXPS_AMAZON_IDENTIFIER` `OSC_AMAZON_IDENTIFIER`
                    char(32) COLLATE \'latin1_general_ci\'
                    NOT NULL COMMENT \'Amazon index to search by\',
                CHANGE `OXPS_AMAZON_CHARGE_PERMISSION_ID` `OSC_AMAZON_CHARGE_PERMISSION_ID`
                    char(32) COLLATE \'latin1_general_ci\'
                    NOT NULL COMMENT \'Amazon chargePermissionId\',
                CHANGE `OXPS_AMAZON_CHARGE_ID` `OSC_AMAZON_CHARGE_ID`
                    char(32) COLLATE \'latin1_general_ci\'
                    NOT NULL COMMENT \'Amazon chargeId\',
                CHANGE `OXPS_AMAZON_OBJECT_TYPE` `OSC_AMAZON_OBJECT_TYPE`
                    char(32) COLLATE \'latin1_general_ci\'
                    NOT NULL COMMENT \'Amazon objectType\',
                CHANGE `OXPS_AMAZON_OBJECT_ID` `OSC_AMAZON_OBJECT_ID`
                    char(32) COLLATE \'latin1_general_ci\'
                    NOT NULL COMMENT \'Amazon objectId\'';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    protected static function createLogTable(): void
    {
        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS %s (
                        `OSC_AMAZON_PAYLOGID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Record id\',
                        `OSC_AMAZON_OXSHOPID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Shop id (oxshops)\',
                        `OSC_AMAZON_OXUSERID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'User id (oxuser)\',
                        `OSC_AMAZON_OXORDERID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Order id (oxorder)\',
                        `OSC_AMAZON_RESPONSE_MSG`
                            TEXT
                            NOT NULL
                            COMMENT \'Response from Amazon SDK\',
                        `OSC_AMAZON_STATUS_CODE`
                            VARCHAR(100)
                            NOT NULL
                            COMMENT \'Status code from Amazon SDK\',
                        `OSC_AMAZON_REQUEST_TYPE`
                            VARCHAR(100)
                            NOT NULL
                            COMMENT \'Request type\',
                        `OXTIMESTAMP`
                            timestamp
                            NOT NULL
                            default CURRENT_TIMESTAMP
                            on update CURRENT_TIMESTAMP
                            COMMENT \'Timestamp\',
                        `OSC_AMAZON_IDENTIFIER`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon index to search by\',
                        `OSC_AMAZON_CHARGE_PERMISSION_ID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon chargePermissionId\',
                        `OSC_AMAZON_CHARGE_ID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon chargeId\',
                        `OSC_AMAZON_OBJECT_TYPE`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon objectType\',
                        `OSC_AMAZON_OBJECT_ID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon objectId\',
                        PRIMARY KEY (`OSC_AMAZON_PAYLOGID`))
                            ENGINE=InnoDB
                            COMMENT \'Amazon Payment transaction log\'',
            LogRepository::TABLE_NAME
        );

        DatabaseProvider::getDb()->execute($sql);
    }
}
