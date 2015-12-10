<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
// Load PEAR & PEAR5 Classes if they do not exist

if (!defined('SMF'))
	die('Hacking attempt...');

global $sourcedir;

if (!class_exists('PEAR'))
    require_once($sourcedir . '/PEAR.php');

if (!class_exists('PEAR5'))
    require_once($sourcedir . '/PEAR5.php');

if (class_exists('PEAR5') && class_exists('PEAR5'))
    require_once ($sourcedir . '/TarClass.php');
else
{
	fatal_lang_error('pdl_error_tar', false);
	exit();
}

?>