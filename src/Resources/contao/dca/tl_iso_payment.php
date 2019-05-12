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
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['wirecardUser'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardUser'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['wirecardPassword'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardPassword'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['wirecardMerchantID'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardMerchantID'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['wirecardSecret'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardSecret'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['wirecardTestAPI'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardTestAPI'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['wirecardContact'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecardContact'],
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['wirecard'] = $GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['cash'];

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('gateway_legend', 'price_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField('wirecardUser', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardPassword', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardMerchantID', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardSecret', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardTestAPI', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('wirecardContact', 'gateway_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('wirecard', 'tl_iso_payment')
;
