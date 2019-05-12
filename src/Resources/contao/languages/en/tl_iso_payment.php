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

array_insert($GLOBALS['TL_LANG']['tl_iso_payment'], 0, [
    'wirecard_customer_id' => ['Customer ID', 'Your "customerId" at Wirecard'],
    'wirecard_secret' => ['Secret', 'Your "secret" at Wirecard'],
    'wirecard_contact' => ['Contact', 'Please select the page which contains your contact information'],
]);
