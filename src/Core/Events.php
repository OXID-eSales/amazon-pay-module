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
use OxidEsales\Eshop\Core\Model\BaseModel as EshopBaseModel;
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

    protected static $paymentIds;

    /**
     * Execute action on activate event
     */
    public static function onActivate(): void
    {
        self::$paymentIds = Constants::getPaymentIds();
        self::createLogTable();
        self::updateOxpsToOsc();
        self::addPaymentMethods();
        self::enablePaymentMethods();
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
        $sql = 'show columns
                from `oxarticles`
                like \'OXPS_AMAZON_EXCLUDE\'';

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
        $sql = 'show columns
                from `oxarticles`
                like \'OSC_AMAZON_EXCLUDE\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (!count($result)) {
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
        $sql = 'show columns
                from `oxcategories`
                like \'OXPS_AMAZON_EXCLUDE\'';

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
        $sql = 'show columns
                from `oxcategories`
                like \'OSC_AMAZON_EXCLUDE\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (!count($result)) {
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
        $sql = 'show columns
                from `oxdeliveryset`
                like \'OXPS_AMAZON_CARRIER\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (count($result)) {
            $sql = 'ALTER TABLE `oxdeliveryset` CHANGE `OXPS_AMAZON_CARRIER` `OSC_AMAZON_CARRIER`
                                VARCHAR (100)';

            DatabaseProvider::getDb()->execute($sql);
        }
    }


    protected static function addDeliverySetColumn(): void
    {
        $sql = 'show columns
                from `oxdeliveryset`
                like \'OSC_AMAZON_CARRIER\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (!count($result)) {
            $sql = 'ALTER TABLE `oxdeliveryset` ADD COLUMN `OSC_AMAZON_CARRIER`
                                VARCHAR (100)';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    protected static function updateOxpsToOscOrderColumn(): void
    {
        $sql = 'show columns
                from `oxorder`
                like \'OXPS_AMAZON_REMARK\'';

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
        $sql = 'show columns
                from `oxorder`
                like \'OSC_AMAZON_REMARK\'';

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        if (!count($result)) {
            $sql = 'ALTER TABLE `oxorder` ADD COLUMN `OSC_AMAZON_REMARK`
                                varchar(255)
                                NOT NULL
                                DEFAULT ""
                                COMMENT \'Remark from amazonpay\'';

            DatabaseProvider::getDb()->execute($sql);
        }
    }

    /**
     * Add payment methods set EN and DE long descriptions
     */
    protected static function addPaymentMethods(): void
    {
        foreach (Constants::PAYMENT_DESCRIPTIONS as $paymentId => $paymentDescription) {
            self::createPaymentMethod($paymentId, $paymentDescription);
        }
    }

    protected static function createPaymentMethod($paymentId, $paymentDescription): void
    {
        $payment = oxNew(Payment::class);
        $paymentLoaded = $payment->load($paymentId);
        if (!$paymentLoaded) {
            $payment->setId($paymentId);
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
            self::assignPaymentToActiveDeliverySets($paymentId);

            $languages = Registry::getLang()->getLanguageIds();
            foreach ($paymentDescription as $languageAbbreviation => $values) {
                $languageId = array_search($languageAbbreviation, $languages, true);
                if ($languageId !== false) {
                    $payment->loadInLang($languageId, $paymentId);
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
     * Disables payment methods
     */
    protected static function disablePaymentMethods(): void
    {
        foreach(self::$paymentIds as $id){
            $payment = oxNew(Payment::class);
            if ($payment->load($id)) {
                $payment->oxpayments__oxactive = new Field(0);
                $payment->save();
            }
        }
    }

    /**
     * Activates  payment methods
     */
    protected static function enablePaymentMethods(): void
    {
        foreach(self::$paymentIds as $id){
            $payment = oxNew(Payment::class);
            if ($payment->load($id)) {
                $payment->oxpayments__oxactive = new Field(1);
                $payment->save();
            }
        }
    }

    protected static function assignPaymentToActiveDeliverySets(string $paymentId): void
    {
        $deliverySetIds = self::getActiveDeliverySetIds();
        foreach ($deliverySetIds as $deliverySetId) {
            self::assignPaymentToDelivery($paymentId, $deliverySetId);
        }
    }

    protected static function assignPaymentToDelivery(string $paymentId, string $deliverySetId): void
    {
        $object2Payment = oxNew(EshopBaseModel::class);
        $object2Payment->init('oxobject2payment');
        $object2Payment->assign(
            [
                'oxpaymentid' => $paymentId,
                'oxobjectid'  => $deliverySetId,
                'oxtype'      => 'oxdelset'
            ]
        );
        $object2Payment->save();
    }

    protected static function getActiveDeliverySetIds(): array
    {
        $sql = 'SELECT `OXID`
                FROM `oxdeliveryset`
                WHERE `oxactive` = 1';
        $fromDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        foreach ($fromDb as $row) {
            $result[$row['OXID']] = $row['OXID'];
        }

        return $result;
    }

    /**
     * Execute action on deactivate event
     *
     * @return void
     */
    public static function onDeactivate(): void
    {
        self::disablePaymentMethods();
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
        $sql = 'show columns
                from `' . LogRepository::TABLE_NAME . '`
                like \'OXPS_AMAZON_PAYLOGID\'';

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
