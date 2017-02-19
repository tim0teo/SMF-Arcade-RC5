<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

global $txt, $smcFunc, $db_prefix, $modSettings;
global $project_version, $addSettings, $permissions, $tables, $sourcedir;

$version = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

if (!defined('SMF'))
	require '../SSI.php';

remove_integration_function('integrate_pre_include', '$sourcedir/ArcadeHooks.php');
remove_integration_function('integrate_actions', 'Arcade_actions');
remove_integration_function('integrate_core_features', 'Arcade_core_features');
remove_integration_function('integrate_load_permissions', 'Arcade_load_permissions');
remove_integration_function('integrate_menu_buttons', 'Arcade_menu_buttons');
remove_integration_function('integrate_admin_areas', 'Arcade_admin_areas');
remove_integration_function('integrate_load_theme', 'Arcade_load_theme');

if ($version === 'v2.0')
	remove_integration_function('integrate_profile_areas', 'Arcade_profile_areas');
else
	remove_integration_function('integrate_pre_profile_areas', 'Arcade_profile_areas');
?>
