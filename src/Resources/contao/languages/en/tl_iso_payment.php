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
    'wirecardUser' => ['Username', 'Your username from Wirecard.'],
    'wirecardPassword' => ['Password', 'Your password from Wirecard.'],
    'wirecardMerchantId' => ['Merchant Account ID (MAID)', 'Your "Merchant Account ID" ("MAID") from Wirecard.'],
    'wirecardSecret' => ['Secret', 'Your secret with Wirecard.'],
    'wirecardPaymentMethod' => ['Payment method', 'The payment method that should be used at Wirecard.'],
    'wirecardPaymentMethodBlank' => 'All available payment methods',
    'wirecardPaymentMethods' => [
        'creditcard' => 'Credit card',
        'alipay-xborder' => 'Alipay Cross-border',
        'bancontact' => 'Bancontact',
        'eps' => 'eps-Überweisung',
        'ideal' => 'iDEAL',
        'paybox' => 'paybox',
        'paypal' => 'PayPal',
        'paysafecard' => 'paysafecard',
        'p24' => 'Przelewy24 (P24)',
        'sepadirectdebit' => 'SEPA Direct Debit',
        'sofortbanking' => 'Pay now with Klarna',
    ],
    'wirecardBaseUrl' => ['Base URL', 'The base URL of the payment page API given by Wirecard.'],
]);
