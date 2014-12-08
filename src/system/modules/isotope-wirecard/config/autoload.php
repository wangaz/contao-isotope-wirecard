<?php

/**
 * Wirecard Payment Module for Isotope eCommerce for Contao Open Source CMS
 *
 * 
 * @copyright  Wangaz. GbR 2014
 * @author     Wangaz. GbR <hallo@wangaz.com>
 * @package    isotope-wirecard
 */


/**
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('Isotope', 'system/modules/isotope-wirecard/library');


/**
 * Register the templates
 */
TemplateLoader::addFiles(array(
	'iso_payment_wirecard'	=> 'system/modules/isotope-wirecard/templates'
));
