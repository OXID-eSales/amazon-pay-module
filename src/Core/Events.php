<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidProfessionalServices\AmazonPay\Core;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\AmazonPay\Core\Repository\LogRepository;

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
    public static function onActivate()
    {
        self::createLogTable();
        self::addPaymentMethod();
        self::enablePaymentMethod();
        self::addArticleColumn();
        self::addCategoryColumn();
        self::addOrderColumn();
        self::addRequireSession();

        $dbMetaDataHandler = oxNew(DbMetaDataHandler::class);
        $dbMetaDataHandler->updateViews();
    }

    public static function addArticleColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxarticles') . '\'
                AND COLUMN_NAME = \'OXPS_AMAZON_EXCLUDE\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result) === 0) {
            $sql = 'ALTER TABLE `oxarticles` ADD COLUMN `OXPS_AMAZON_EXCLUDE`
                                tinyint(1)
                                NOT NULL
                                DEFAULT 0
                                COMMENT \'Exclude from amazonpay\'';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    public static function addCategoryColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxcategories') . '\'
                AND COLUMN_NAME = \'OXPS_AMAZON_EXCLUDE\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result) === 0) {
            $sql = 'ALTER TABLE `oxcategories` ADD COLUMN `OXPS_AMAZON_EXCLUDE`
                                tinyint(1)
                                NOT NULL
                                DEFAULT 0
                                COMMENT \'Exclude from amazonpay\'';

            DatabaseProvider::getDb()->execute($sql);
        }
        self::alterDeliverySetTable();
    }

    protected static function alterDeliverySetTable(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = sprintf('SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxdeliveryset') . '\'
                AND COLUMN_NAME = \'OXPS_AMAZON_CARRIER\'');

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result) === 0) {
            $sql = sprintf(
                'ALTER TABLE %s ADD COLUMN OXPS_AMAZON_CARRIER VARCHAR (100)',
                'oxdeliveryset'
            );

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    public static function addOrderColumn(): void
    {
        $viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);

        $sql = 'SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = \'' . $viewNameGenerator->getViewName('oxorder') . '\'
                AND COLUMN_NAME = \'OXPS_AMAZON_REMARK\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result) === 0) {
            $sql = 'ALTER TABLE `oxorder` ADD COLUMN `OXPS_AMAZON_REMARK`
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
    public static function addPaymentMethod(): void
    {
        $paymentDescriptions = [
            'en' => '<div>AmazonPay</div>',
            'de' => '<div>AmazonPay</div>'
        ];

        $payment = oxNew(Payment::class);
        if (!$payment->load(Constants::PAYMENT_ID)) {
            $payment->setId(Constants::PAYMENT_ID);
            $payment->oxpayments__oxactive = new Field(1);
            $payment->oxpayments__oxdesc = new Field('AmazonPay');
            $payment->oxpayments__oxaddsum = new Field(0);
            $payment->oxpayments__oxaddsumtype = new Field('abs');
            $payment->oxpayments__oxfromboni = new Field(0);
            $payment->oxpayments__oxfromamount = new Field(0);
            $payment->oxpayments__oxtoamount = new Field(10000);
            $payment->save();

            $languages = Registry::getLang()->getLanguageIds();
            foreach ($paymentDescriptions as $languageAbbreviation => $description) {
                $languageId = array_search($languageAbbreviation, $languages, true);
                if ($languageId !== false) {
                    $payment->loadInLang($languageId, Constants::PAYMENT_ID);
                    $payment->oxpayments__oxlongdesc = new Field($description);
                    $payment->save();
                }
            }
        }
    }

    /**
     * Disables payment method
     */
    public static function disablePaymentMethod(): void
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
    public static function enablePaymentMethod(): void
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
    public static function onDeactivate(): void
    {
        self::disablePaymentMethod();
    }

    /**
     * add details controller to requireSession
     */
    public static function addRequireSession(): void
    {
        $config = Registry::getConfig();
        $cfg = $config->getConfigParam('aRequireSessionWithParams');
        $cfg = is_array($cfg) ? $cfg : [];
        $cfg = array_merge_recursive($cfg, self::$requireSessionWithParams);
        $config->saveShopConfVar('arr', 'aRequireSessionWithParams', $cfg, (string)$config->getShopId());
    }

    protected static function createLogTable(): void
    {
        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS %s (
                        `OXPS_AMAZON_PAYLOGID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Record id\',
                        `OXPS_AMAZON_OXSHOPID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Shop id (oxshops)\',
                        `OXPS_AMAZON_OXUSERID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'User id (oxuser)\',
                        `OXPS_AMAZON_OXORDERID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Order id (oxorder)\',
                        `OXPS_AMAZON_RESPONSE_MSG`
                            TEXT
                            NOT NULL
                            COMMENT \'Response from Amazon SDK\',
                        `OXPS_AMAZON_STATUS_CODE`
                            VARCHAR(100)
                            NOT NULL
                            COMMENT \'Status code from Amazon SDK\',
                        `OXPS_AMAZON_REQUEST_TYPE`
                            VARCHAR(100)
                            NOT NULL
                            COMMENT \'Request type\',
                        `OXTIMESTAMP`
                            timestamp
                            NOT NULL
                            default CURRENT_TIMESTAMP
                            on update CURRENT_TIMESTAMP
                            COMMENT \'Timestamp\',
                        `OXPS_AMAZON_IDENTIFIER`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon index to search by\',
                        `OXPS_AMAZON_CHARGE_PERMISSION_ID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon chargePermissionId\',
                        `OXPS_AMAZON_CHARGE_ID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon chargeId\',
                        `OXPS_AMAZON_OBJECT_TYPE`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon objectType\',
                        `OXPS_AMAZON_OBJECT_ID`
                            char(32)
                            character set latin1
                            collate latin1_general_ci
                            NOT NULL
                            COMMENT \'Amazon objectId\',
                        PRIMARY KEY (`OXPS_AMAZON_PAYLOGID`))
                            ENGINE=InnoDB
                            COMMENT \'Amazon Payment transaction log\'',
            LogRepository::TABLE_NAME
        );

        DatabaseProvider::getDb()->execute($sql);
    }
}
