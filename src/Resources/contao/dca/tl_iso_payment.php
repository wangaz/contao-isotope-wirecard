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

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_iso_payment']['fields'], [
    'wirecardUser' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardUser'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'wirecardPassword' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardPassword'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'wirecardMerchantId' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardMerchantId'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'wirecardSecret' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardSecret'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'wirecardPaymentMethod' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardPaymentMethod'],
        'exclude' => true,
        'inputType' => 'select',
        'options' => array_keys(\Wangaz\ContaoIsotopeWirecardBundle\Isotope\Model\Payment\Wirecard::$paymentMethods),
        'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardPaymentMethods'],
        'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'wirecardBaseUrl' => [
        'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardBaseUrl'],
        'exclude' => true,
        'default' => 'wpp-test.wirecard.com',
        'inputType' => 'text',
        'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true, 'rgxp' => 'url'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
]);

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['wirecard'] = $GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['cash'];

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('gateway_legend', 'price_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField('wirecardUser', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardPassword', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardMerchantId', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardSecret', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardBaseUrl', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardPaymentMethod', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('wirecard', 'tl_iso_payment')
;
