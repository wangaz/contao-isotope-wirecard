<?php

declare(strict_types=1);

/*
 * This file is part of the Wangaz\ContaoIsotopeWirecardBundle.
 *
 * (c) Wangaz
 * (c) inspiredminds
 *
 * @license CC-BY-SA-4.0
 */

$GLOBALS['TL_LANG']['tl_iso_payment'] = array_merge($GLOBALS['TL_LANG']['tl_iso_payment'], [
    'wirecardUser' => ['Benutzername', 'Ihr Benutzername bei Wirecard.'],
    'wirecardPassword' => ['Passwort', 'Ihr Passwort bei Wirecard.'],
    'wirecardMerchantId' => ['Merchant Account ID (MAID)', 'Ihre "Merchant Account ID" ("MAID") bei Wirecard.'],
    'wirecardSecret' => ['Secret', 'Ihr Secret bei Wirecard.'],
    'wirecardPaymentMethod' => ['Bezahlmethode', 'Die Bezahlmethode, die bei Wirecard benutzt werden soll.'],
    'wirecardPaymentMethodBlank' => 'Alle verfügbaren Bezahlmethoden',
    'wirecardPaymentMethods' => [
        'creditcard' => 'Kreditkarte',
        'alipay-xborder' => 'Alipay Cross-border',
        'bancontact' => 'Bancontact',
        'eps' => 'eps-Überweisung',
        'ideal' => 'iDEAL',
        'paybox' => 'paybox',
        'paypal' => 'PayPal',
        'paysafecard' => 'paysafecard',
        'p24' => 'Przelewy24 (P24)',
        'sepadirectdebit' => 'SEPA-Lastschrift',
        'sofortbanking' => 'Sofortüberweisung',
    ],
    'wirecardBaseUrl' => ['Base URL', 'Die Basis URL, die von Wirecard für die Zahlungsschnittstelle übermittelt wurde.'],
]);
