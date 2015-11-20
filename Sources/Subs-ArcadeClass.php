<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	!!!
*/
// LoadClassFile
function loadClassFile($file = false)
{
	global $sourcedir;

	if (!$file)
		return false;

	require_once($sourcedir . '/Class-Package.php');
	return true;
}
?>