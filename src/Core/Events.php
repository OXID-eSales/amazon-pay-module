<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Core;

use Exception;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
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

    /**
     * Execute action on activate event
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function onActivate()
    {
        self::createLogTable();
        self::updateOxpsToOsc();
        self::addPaymentMethods();
        self::addArticleColumn();
        self::addCategoryColumn();
        self::addDeliverySetColumn();
        self::addOrderColumn();
        self::addRequireSession();

        $dbMetaDataHandler = oxNew(DbMetaDataHandler::class);
        $dbMetaDataHandler->updateViews();
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function updateOxpsToOsc()
    {
        self::updateOxpsToOscArticleColumn();
        self::updateOxpsToOscCategoryColumn();
        self::updateOxpsToOscDeliverySetColumn();
        self::updateOxpsToOscOrderColumn();
        self::updateOxpsToOscLogTable();
    }

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    protected static function updateOxpsToOscArticleColumn()
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

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function addArticleColumn()
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

    protected static function updateOxpsToOscCategoryColumn()
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

    protected static function addCategoryColumn()
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

    protected static function updateOxpsToOscDeliverySetColumn()
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


    protected static function addDeliverySetColumn()
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

    protected static function updateOxpsToOscOrderColumn()
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

    protected static function addOrderColumn()
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
    protected static function addPaymentMethods()
    {
        foreach (Constants::PAYMENT_DESCRIPTIONS as $paymentId => $paymentDescription) {
            self::createPaymentMethod($paymentId, $paymentDescription);
        }
    }

    /**
     * @param string[][] $paymentDescription
     *
     * @throws Exception
     */
    protected static function createPaymentMethod(string $paymentId, array $paymentDescription)
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
                    $languageId = (int)$languageId;
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
     * @param string $paymentId
     * @return void
     * @throws Exception
     */
    protected static function assignPaymentToActiveDeliverySets(string $paymentId)
    {
        $deliverySetIds = self::getActiveDeliverySetIds();
        foreach ($deliverySetIds as $deliverySetId) {
            self::assignPaymentToDelivery($paymentId, $deliverySetId);
        }
    }

    /**
     * @param string $paymentId
     * @param string $deliverySetId
     * @return void
     * @throws Exception
     */
    protected static function assignPaymentToDelivery(string $paymentId, string $deliverySetId)
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

    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function getActiveDeliverySetIds(): array
    {
        $sql = 'SELECT `OXID`
                FROM `oxdeliveryset`
                WHERE `oxactive` = 1';
        $fromDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($sql);

        $result = [];
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
    public static function onDeactivate()
    {
    }

    /**
     * add details controller to requireSession
     * @return void
     */
    protected static function addRequireSession()
    {
        $config = Registry::getConfig();
        $cfg = $config->getConfigParam('aRequireSessionWithParams');
        $cfg = is_array($cfg) ? $cfg : [];
        $cfg = array_merge_recursive($cfg, self::$requireSessionWithParams);
        $config->saveShopConfVar('arr', 'aRequireSessionWithParams', $cfg, (string)$config->getShopId());
    }

    protected static function updateOxpsToOscLogTable()
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

    protected static function createLogTable()
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
