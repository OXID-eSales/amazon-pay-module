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
$sLangName = 'Deutsch';

$aLang = [
    'charset'                           => 'UTF-8',
    'amazonpay'                         => 'Amazon Pay',
    'OXPS_AMAZONPAY_CONFIG'             => 'Konfiguration',
    'OXPS_AMAZONPAY_GENERAL'            => 'Allgemein',
    'OXPS_AMAZONPAY_CREDENTIALS'        => 'Anmeldeinformationen',
    'OXPS_AMAZONPAY_OPMODE'             => 'Betriebsmodus',
    'OXPS_AMAZONPAY_OPMODE_PROD'        => 'Produktion',
    'OXPS_AMAZONPAY_OPMODE_SANDBOX'     => 'Sandbox',
    'HELP_OXPS_AMAZONPAY_OPMODE'        => 'Verwenden Sie Sandbox (Test), um Amazon Pay zu konfigurieren und zu testen. Wenn Sie bereit sind,
        echte Transaktionen zu empfangen, wechseln Sie zu Produktion (live).',
    'OXPS_AMAZONPAY_PRIVKEY'            => 'Privater Schlüssel',
    'HELP_OXPS_AMAZONPAY_PRIVKEY'       => 'Ihr privater Schlüssel für die Integration. Um ihn zu generieren, melden Sie sich bei Seller Central
        an und gehen Sie dann zu Integration',
    'OXPS_AMAZONPAY_PUBKEYID'           => 'ID des öffentlichen Schlüssels',
    'HELP_OXPS_AMAZONPAY_PUBKEYID'      => 'Die ID des öffentlichen Schlüssels, die Sie zusammen mit dem in der Verkäuferzentrale generierten
        privaten Schlüssel erhalten.',
    'OXPS_AMAZONPAY_MERCHANTID'         => 'Händler-ID',
    'HELP_OXPS_AMAZONPAY_MERCHANTID'    => 'Wenn Sie sich nicht sicher sind, wie Ihre Händler-ID lautet,
         Melden Sie sich bei Seller Central an und gehen Sie dann zu Integration > MWS Access Key unter Allgemeine Informationen, Händler-ID.',
    'OXPS_AMAZONPAY_STOREID'            => 'Amazon-Client-ID',
    'HELP_OXPS_AMAZONPAY_STOREID'       => 'Melden Sie sich mit der Amazon-Client-ID an. Verwenden Sie nicht die Anwendungs-ID.
        Rufen Sie diesen Wert ab von Login mit Amazon in Seller Central.',
    'OXPS_AMAZONPAY_PAYREGION'          => 'Zahlungsbereich',
    'HELP_OXPS_AMAZONPAY_PAYREGION'     => 'Die in Ihrem Shop erlaubten und bei Amazon Pay möglichen Währungen.',
    'OXPS_AMAZONPAY_SELLER'             => 'Verkäufer',
    'OXPS_AMAZONPAY_IPN'                => 'IPN-Endpunkt',
    'HELP_OXPS_AMAZONPAY_IPN'           => 'IPN-Nachrichten werden von Amazon Pay ohne Ihr Zutun gesendet und können zur Aktualisierung Ihres
        internen Auftragsverwaltungssystems und zur Bearbeitung von Bestellungen verwendet werden',
    'OXPS_AMAZONPAY_PLACEMENT'          => 'Platzierung',
    'HELP_OXPS_AMAZONPAY_PLACEMENT'     => 'Definieren Sie, wo die Amazon Pay-Schaltfläche in Ihrem Online-Shop angezeigt werden soll.',
    'OXPS_AMAZONPAY_PDP'                => 'Produktdetailseite',
    'OXPS_AMAZONPAY_MINICART_AND_MODAL' => 'Warenkorb + Warenkorb-PopUp',
    'OXPS_AMAZONPAY_PERFORMANCE'        => 'Performance',
    'OXPS_AMAZONPAY_EXCLUSION'          => '"AmazonPay ausschließen" nutzen',
    'HELP_OXPS_AMAZONPAY_EXCLUSION'     => 'Es können Produkte und Kategorien von AmazonPay ausgeschlossen werden. Wenn Sie das nicht tun, können Sie das Feature aus Performancegründen generell deaktivieren',
    'OXPS_AMAZONPAY_SAVE'               => 'Speichern',
    'OXPS_AMAZONPAY_ERR_CONF_INVALID'   =>
        'Ein oder mehrere Konfigurationswerte sind entweder nicht festgelegt oder falsch. Bitte überprüfen Sie sie noch einmal.<br>
        <b>Modul inaktiv.</b>',
    'OXPS_AMAZONPAY_CONF_VALID'         => 'Konfigurationswerte OK.<br><b>Modul ist aktiv</b>',
    'OXPS_AMAZONPAY_CAPTYPE'            => 'Capture-Typ',
    'HELP_OXPS_AMAZONPAY_CAPTYPE'       => 'Einstufig erfasst die Zahlung sofort. Zweistufig erfasst die Zahlung nach dem Versand.',
    'OXPS_AMAZONPAY_CAPTYPE_ONE_STEP'   => 'Einstufig',
    'OXPS_AMAZONPAY_CAPTYPE_TWO_STEP'   => 'Zweistufig',
    'OXPS_AMAZONPAY_EXCLUDED'           => 'AmazonPay ausschließen',
    'OXPS_AMAZONPAY_CARRIER_CODE'       => 'Amazon Carrier Code',
    'OXPS_AMAZONPAY_PLEASE_CHOOSE'      => 'Bitte wählen',

    'OXPS_AMAZONPAY_PAYMENT_WAS_SHIPPING'    => 'Amazon-Zahlung nach Lieferung erfolgt',
    'OXPS_AMAZONPAY_PAYMENT_WHEN_SHIPPING'   => 'Amazon-Zahlung bei Lieferung erfolgt',
    'OXPS_AMAZONPAY_PAYMENT_DURING_CHECKOUT' => 'Amazon-Zahlung während des Checkouts erfolgt',
    'OXPS_AMAZONPAY_TRANSACTION_HISTORY'     => 'Transaktions-Historie',
    'OXPS_AMAZONPAY_IPN_HISTORY'             => 'IPN-Historie',
    'OXPS_AMAZONPAY_DATE'                    => 'Datum',
    'OXPS_AMAZONPAY_REFERENCE'               => 'Referenz',
    'OXPS_AMAZONPAY_RESULT'                  => 'Ergebnis',

    'OXPS_AMAZONPAY_PAYTYPE'            => 'AmazonPay Checkout Type',
    'HELP_OXPS_AMAZONPAY_PAYTYPE'       => 'Auswahl Lieferadresse über Amazon Pay (PayAndShip) oder nur Zahlung über Amazon Pay (PayOnly)'
];
