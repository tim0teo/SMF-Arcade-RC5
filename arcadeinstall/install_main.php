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

if (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please run arcadeinstall/index.php instead');

$forced = false;
$version = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
db_extend('packages');

// Step 1: Rename E-Arcade tables if needed
doRenameTables();

// Step 2: Create and/or Upgrade tables
doTables($tables, $columnRename, true);

// Step 3: Add Settings to database
doSettings($addSettings);

// Step 4: Update "Admin Features"
updateAdminFeatures('arcade', !empty($modSettings['arcadeEnabled']));

// Step 5: Add Permissions to database
doPermission($permissions);

// Step 6: Insert SMF Arcade Package Server to list
$request = $smcFunc['db_query']('', '
	SELECT COUNT(*)
	FROM {db_prefix}package_servers
	WHERE name = {string:name}',
	array(
		'name' => 'SMF Arcade Package Server',
	)
);

list ($count) = $smcFunc['db_fetch_row']($request);
$smcFunc['db_free_result']($request);

if ($count == 0 || $forced)
{
	$smcFunc['db_insert']('insert',
		'{db_prefix}package_servers',
		array(
			'name' => 'string',
			'url' => 'string',
		),
		array(
			'SMF Arcade Package Server',
			'http://download.smfarcade.info',
		),
		array()
	);
}

// Step 7: Insert Default Category
$request = $smcFunc['db_query']('', '
	SELECT COUNT(*)
	FROM {db_prefix}arcade_categories');

list ($count) = $smcFunc['db_fetch_row']($request);
$smcFunc['db_free_result']($request);

if ($count == 0 || $forced)
{
	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_categories',
		array('cat_name' => 'string', 'member_groups' => 'string', 'cat_order' => 'int', 'cat_icon' => 'string'),
		array('Default', '-2,-1,0,1,2', 1, ''),
		array('id_cat')
	);
}

// Step 8: Update Arcade Version in Database
// updateSettings(array('arcadeVersion' => $arcade_version));

// Step 9: Hooks
add_integration_function('integrate_pre_include', '$sourcedir/ArcadeHooks.php');
add_integration_function('integrate_pre_load', 'Arcade_load_language');
add_integration_function('integrate_actions', 'Arcade_actions');
add_integration_function('integrate_core_features', 'Arcade_core_features');
add_integration_function('integrate_load_permissions', 'Arcade_load_permissions');
add_integration_function('integrate_menu_buttons', 'Arcade_menu_buttons');
add_integration_function('integrate_admin_areas', 'Arcade_admin_areas');
add_integration_function('integrate_load_theme', 'Arcade_load_theme');

if ($version === 'v2.0')
	add_integration_function('integrate_profile_areas', 'Arcade_profile_areas');
else
	add_integration_function('integrate_pre_profile_areas', 'Arcade_profile_areas');

function doRenameTables()
{
	global $smcFunc, $db_prefix, $db_type;

	if ($db_type != 'mysql')
		return;

	$tables = $smcFunc['db_list_tables']();

	// Detect eeks mod from unique table name
	if (in_array($db_prefix . 'arcade_v3temp', $tables))
	{
		$tables = array(
			'arcade_games' => 'earcade_games',
			'arcade_personalbest' => 'earcade_personalbest',
			'arcade_scores' => 'earcade_scores',
			'arcade_categories' => 'earcade_categories',
			'arcade_favorite' => 'earcade_favorite',
			'arcade_rates' => 'earcade_rates',
			'arcade_settings' => 'earcade_settings',
			'arcade_v3temp' => 'earcade_v3temp',
			'arcade_shouts' => 'earcade_shouts',
			'arcade_tournament_rounds' => 'earcade_tournament_rounds',
			'arcade_tournament_players' => 'earcade_tournament_players',
			'arcade_tournament_scores' => 'earcade_tournament_scores',
			'arcade_tournament' => 'earcade_tournament',
		);

		foreach ($tables as $old => $new)
		{
			// Drop old copies of earcade tables if exists
			if (check_table_existsInstall($new))
				$smcFunc['db_drop_table']($new);

			if (check_table_existsInstall($old))
				$smcFunc['db_query']('', '
					ALTER TABLE {db_prefix}{raw:old} RENAME {db_prefix}{raw:new}',
					array(
						'old' => $old,
						'new' => $new,
					)
				);
		}
	}
}

function check_table_existsInstall($table)
{
	global $db_prefix, $smcFunc;

	if ($smcFunc['db_list_tables'](false, $db_prefix . $table))
		return true;

	return false;
}
?>