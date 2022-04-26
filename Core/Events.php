<?php

/**
 * This file is part of OXID eSales AmazonPay module.
 *
 * OXID eSales AmazonPay module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales AmazonPay module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales AmazonPay module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2020
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
        if (!$payment->load('oxidamazon')) {
            $payment->setId('oxidamazon');
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
                    $payment->loadInLang($languageId, 'oxidamazon');
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
        if ($payment->load('oxidamazon')) {
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
        $payment->load('oxidamazon');
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
