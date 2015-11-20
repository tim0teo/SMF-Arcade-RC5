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
 
/* This file checks if SMF Arcade v2.5 RC4 is installed on your forum */
$check = checkArcadeVersion();

if ($check == false)
    fatal_error('Installation cancelled!  SMF Arcade v2.5 RC4 does not appear to be installed.', false);
return;

/* Check if the column exists */
function checkArcadeVersion()
{
    global $modSettings, $sourcedir;
    if (substr($modSettings['arcadeVersion'], 0, 3) == '2.5' && @file_exists($sourcedir . '/ArcadeHooks.php'))
		return true;
    else
		return false;
}
?>