<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

$sLangName = 'Deutsch';
$aLang = [
    'charset'                          => 'UTF-8',
    'amazonpay'                        => 'Amazon Pay',
    'OSC_AMAZONPAY_CONFIG'             => 'Konfiguration',
    'OSC_AMAZONPAY_GENERAL'            => 'Allgemein',
    'OSC_AMAZONPAY_CREDENTIALS'        => 'Anmeldeinformationen',
    'OSC_AMAZONPAY_OPMODE'             => 'Betriebsmodus',
    'OSC_AMAZONPAY_OPMODE_PROD'        => 'Produktion',
    'OSC_AMAZONPAY_OPMODE_SANDBOX'     => 'Sandbox',
    'HELP_OSC_AMAZONPAY_OPMODE'        => 'Verwenden Sie Sandbox (Test), um Amazon Pay zu konfigurieren und zu testen. Wenn Sie bereit sind,
        echte Transaktionen zu empfangen, wechseln Sie zu Produktion (live).',
    'OSC_AMAZONPAY_PRIVKEY'            => 'Privater Schlüssel',
    'HELP_OSC_AMAZONPAY_PRIVKEY'       => 'Ihr privater Schlüssel für die Integration. Um ihn zu generieren, melden Sie sich bei Seller Central
        an und gehen Sie dann zu Integration',
    'OSC_AMAZONPAY_PUBKEYID'           => 'ID des öffentlichen Schlüssels',
    'HELP_OSC_AMAZONPAY_PUBKEYID'      => 'Die ID des öffentlichen Schlüssels, die Sie zusammen mit dem in der Verkäuferzentrale generierten
        privaten Schlüssel erhalten.',
    'OSC_AMAZONPAY_MERCHANTID'         => 'Händler-ID',
    'HELP_OSC_AMAZONPAY_MERCHANTID'    => 'Wenn Sie sich nicht sicher sind, wie Ihre Händler-ID lautet,
         Melden Sie sich bei Seller Central an und gehen Sie dann zu Integration > MWS Access Key unter Allgemeine Informationen, Händler-ID.',
    'OSC_AMAZONPAY_STOREID'            => 'Amazon-Client-ID',
    'HELP_OSC_AMAZONPAY_STOREID'       => 'Melden Sie sich mit der Amazon-Client-ID an. Verwenden Sie nicht die Anwendungs-ID.
        Rufen Sie diesen Wert ab von Login mit Amazon in Seller Central.',
    'OSC_AMAZONPAY_PAYREGION'          => 'Zahlungsbereich',
    'HELP_OSC_AMAZONPAY_PAYREGION'     => 'Die in Ihrem Shop erlaubten und bei Amazon Pay möglichen Währungen.',
    'OSC_AMAZONPAY_DELREGION'          => 'Lieferbereich',
    'HELP_OSC_AMAZONPAY_DELREGION'     => 'Die in Ihrem Shop erlaubten und bei Amazon Pay möglichen Lieferländer.',
    'OSC_AMAZONPAY_SELLER'             => 'Verkäufer',
    'OSC_AMAZONPAY_IPN'                => 'IPN-Endpunkt (bitte URL kopieren und im Amazon-Backend hinterlegen)',
    'HELP_OSC_AMAZONPAY_IPN'           => 'IPN-Nachrichten werden von Amazon Pay ohne Ihr Zutun gesendet und können zur Aktualisierung Ihres
        internen Auftragsverwaltungssystems und zur Bearbeitung von Bestellungen verwendet werden.<br>Besteht Ihre Server-/Shopkonfiguration aus mehreren URLs,
        so tauschen Sie die vorgeschlagene Domain der o.g. URL gegen Ihre passende Domain aus und achten darauf, das die neue URL frei durch Amazon zugänglich ist.',
    'OSC_AMAZONPAYEXPRESS_PLACEMENT'          => 'Platzierung Amazon Express',
    'HELP_OSC_AMAZONPAYEXPRESS_PLACEMENT'     => 'Definieren Sie, wo die Amazon Pay-Schaltfläche in Ihrem Online-Shop angezeigt werden soll.',
    'OSC_AMAZONPAY_PDP'                => 'Produktdetailseite',
    'OSC_AMAZONPAY_MINICART_AND_MODAL' => 'Warenkorb + Warenkorb-PopUp',
    'OSC_AMAZONPAY_PERFORMANCE'        => 'Performance',
    'OSC_AMAZONPAY_EXCLUSION'          => '"AmazonPay ausschließen" nutzen',
    'HELP_OSC_AMAZONPAY_EXCLUSION'     => 'Es können Produkte und Kategorien von AmazonPay ausgeschlossen werden. Wenn Sie das nicht tun, können Sie das Feature aus Performancegründen generell deaktivieren',
    'OSC_AMAZONPAY_SOCIAL_LOGIN'       => 'Amazon Social Login',
    'OSC_AMAZONPAY_SOCIAL_LOGIN_DEACTIVATED'        => 'Amazon Social Login deaktivieren',
    'HELP_OSC_AMAZONPAY_SOCIAL_LOGIN_DEACTIVATED'   => 'Es besteht die Möglichkeit, sich im Shop mit einem Amazon-Kundenkonto anzumelden. Dabei werden die Adressdaten von Amazon übernommen. Die Anmeldung ist nur dann möglich, wenn noch kein Konto im Shop mit der gleichen Amazon-EMail-Adresse existiert.<br>Wenn Sie diese Funktion nicht Ihren Kunden anbieten wollen, können sie diese hier deaktivieren.',
    'OSC_AMAZONPAY_AUTOMATED_REFUND'   => 'Amazon automatische Geld-Rückerstattung und Bestellrückabwicklung',
    'OSC_AMAZONPAY_AUTOMATED_REFUND_ACTIVATED'      => 'Amazon automatische Geld Rückerstattung aktivieren (Bestellpostions- oder Bestell-Storno)',
    'OSC_AMAZONPAY_AUTOMATED_CANCEL_ACTIVATED'      => 'Amazon automatische Bestellrückabwicklung aktivieren (Bestelllöschung)',
    'HELP_OSC_AMAZONPAY_AUTOMATED_REFUND'           => 'Wenn Artikel einer Amazonbestellung entfernt werden, oder die Bestellung storniert wird, oder die ganze Bestellung gelöscht wird, kann automatisch ein Amazon-Geldrückerstattung bzw. -Bestellrückabwicklung durchgeführt werden.',
    'OSC_AMAZONPAY_SAVE'               => 'Speichern',
    'OSC_AMAZONPAY_ERR_CONF_INVALID'   =>
        'Ein oder mehrere Konfigurationswerte sind entweder nicht festgelegt oder falsch. Bitte überprüfen Sie sie noch einmal.<br>
        <b>Modul inaktiv.</b>',
    'OSC_AMAZONPAY_CONF_VALID'         => 'Konfigurationswerte OK.<br><b>Modul ist aktiv</b>',
    'OSC_AMAZONPAY_CAPTYPE'            => 'Capture-Typ',
    'HELP_OSC_AMAZONPAY_CAPTYPE'       => 'Einstufig erfasst die Zahlung sofort. Zweistufig erfasst die Zahlung nach dem Versand.',
    'OSC_AMAZONPAY_CAPTYPE_ONE_STEP'   => 'Einstufig',
    'OSC_AMAZONPAY_CAPTYPE_TWO_STEP'   => 'Zweistufig',
    'OSC_AMAZONPAY_EXCLUDED'           => 'AmazonPay ausschließen',
    'OSC_AMAZONPAY_CARRIER_CODE'       => 'Amazon Carrier Code',

    'OSC_AMAZONPAY_PAYMENT_WAS_SHIPPING'    => 'Amazon-Zahlung nach Lieferung erfolgt',
    'OSC_AMAZONPAY_PAYMENT_WHEN_SHIPPING'   => 'Amazon-Zahlung bei Lieferung erfolgt',
    'OSC_AMAZONPAY_PAYMENT_DURING_CHECKOUT' => 'Amazon-Zahlung während des Checkouts erfolgt',
    'OSC_AMAZONPAY_TRANSACTION_HISTORY'     => 'Transaktions-Historie',
    'OSC_AMAZONPAY_IPN_HISTORY'             => 'IPN-Historie',
    'OSC_AMAZONPAY_DATE'                    => 'Datum',
    'OSC_AMAZONPAY_REFERENCE'               => 'Referenz',
    'OSC_AMAZONPAY_RESULT'                  => 'Ergebnis',
    'OSC_AMAZONPAY_REMARK'                  => 'Amazon Pay Mitteilung',
    'GENERAL_ARTICLE_OSC_AMAZON_EXCLUDE'    => 'AmazonPay ausschließen',
    'OSC_AMAZONPAY_REFUND'                  => 'Erstatten',
    'OSC_AMAZONPAY_REFUND_ANNOTATION'       => 'Machen Sie eine Rückerstattung von bis zu ',
    'OSC_AMAZONPAY_DELETE_ERROR'            => 'Bestellung kann nicht gelöscht werden, da bereits Geld zwischen dem Shop und Amazon transferiert wurde.',
    'OSC_AMAZONPAY_CAPTURE_ANNOTATION'      => 'Machen Sie eine Gebühr von bis zu ',
    'OSC_AMAZONPAY_CAPTURE'                 => 'Abbuchung',
];
