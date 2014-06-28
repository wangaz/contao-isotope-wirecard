<?php

/**
 * Wirecard Payment Module for Isotope eCommerce for Contao Open Source CMS
 *
 * 
 * @copyright  Wangaz GbR 2014
 * @author     Wangaz GbR <hallo@wangaz.com>
 * @package    isotope_wirecard
 */

/**
 * Paletts
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['wirecard'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend:hide},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},wirecard_customer_id,wirecard_secret,wirecard_contact;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled';

/**
 * Fields
 */
array_insert($GLOBALS['TL_DCA']['tl_iso_payment']['fields'], 0, array(
	'wirecard_failure_order_status'	=> array(
		'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecard_failure_order_status'],
		'exclude'           => true,
		'inputType'         => 'select',
		'foreignKey'        => \Isotope\Model\OrderStatus::getTable().'.name',
		'options_callback'	=> array('\Isotope\Backend', 'getOrderStatus'),
		'eval'              => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		'sql'               => "int(10) NOT NULL default '0'",
		'relation'          => array('type'=>'hasOne', 'load'=>'lazy'),
    ),
	'wirecard_customer_id'			=> array(
		'label'				=> &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecard_customer_id'],
		'exclude'			=> true,
		'inputType'			=> 'text',
		'eval'				=> array('mandatory' => true, 'maxlength' => 16, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
		'sql'				=> "varchar(16) NOT NULL default '0'",
	),
	'wirecard_secret'				=> array(
		'label'				=> &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecard_secret'],
		'exclude'			=> true,
		'inputType'			=> 'text',
		'eval'				=> array('mandatory' => true, 'maxlength' => 64, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
		'sql'				=> "varchar(64) NOT NULL default '0'",
	),
	'wirecard_contact'		=> array(
		'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['wirecard_contact'],
		'exclude'           => true,
		'inputType'         => 'pageTree',
		'foreignKey'        => 'tl_page.title',
		'eval'              => array('mandatory' => true, 'fieldType' => 'radio'),
		'sql'               => "int(10) unsigned NOT NULL default '0'",
		'relation'          => array('type'=>'hasOne', 'load'=>'lazy'),
	),
));
