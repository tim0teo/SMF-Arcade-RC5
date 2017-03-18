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

function doTables($tables, $columnRename = array())
{
	global $smcFunc, $db_prefix, $db_type, $db_show_debug;

	$log = array();

	foreach ($tables as $table)
	{
		$table_name = $table['name'];

		// Create table
		if (!checkTableExistsArcade($table['name']) && empty($table['smf']))
			$smcFunc['db_create_table']('{db_prefix}' . $table_name, $table['columns'], $table['indexes'], array(), 'ignore');
		// Update table
		else
		{
			$currentTable = $smcFunc['db_table_structure']('{db_prefix}' . $table_name);

			// Renames in this table?
			if (!empty($table['rename']))
			{
				foreach ($currentTable['columns'] as $column)
				{
					if (isset($table['rename'][$column['name']]))
					{
						$old_name = $column['name'];
						$column['name'] = $table['rename'][$column['name']];

						$smcFunc['db_change_column']('{db_prefix}' . $table_name, $old_name, $column, array(), 'ignore');
					}
				}
			}

			// Global renames? (should be avoided)
			if (!empty($columnRename))
			{
				foreach ($currentTable['columns'] as $column)
				{
					if (isset($columnRename[$column['name']]))
					{
						$old_name = $column['name'];
						$column['name'] = $columnRename[$column['name']];
						$smcFunc['db_change_column']('{db_prefix}' . $table_name, $old_name, $column, array(), 'ignore');
					}
				}
			}

			// Check that all columns are in
			foreach ($table['columns'] as $id => $col)
			{
				$exists = false;

				// TODO: Check that definition is correct
				foreach ($currentTable['columns'] as $col2)
				{
					if ($col['name'] === $col2['name'])
					{
						$exists = true;
						break;
					}
				}

				// Add missing columns
				if (!$exists)
					$smcFunc['db_add_column']('{db_prefix}' . $table_name, $col, array(), 'update');
			}

			// Remove any unnecassary columns
			foreach ($currentTable['columns'] as $col)
			{
				$exists = false;

				foreach ($table['columns'] as $col2)
				{
					if ($col['name'] === $col2['name'])
					{
						$exists = true;
						break;
					}
				}

				if (!$exists && isset($table['upgrade']['columns'][$col['name']]))
				{
					if ($table['upgrade']['columns'][$col['name']] == 'drop')
						$smcFunc['db_remove_column']('{db_prefix}' . $table_name, $col['name'], array(), 'ignore');
				}
				elseif (!$exists && !empty($db_show_debug) && empty($table['smf']))
					$log[] = sprintf('Table %s has non-required column %s', $table_name, $col['name']);
			}

			// Check that all indexes are in and correct
			foreach ($table['indexes'] as $id => $index)
			{
				$exists = false;

				foreach ($currentTable['indexes'] as $index2)
				{
					// Primary is special case
					if ($index['type'] == 'primary' && $index2['type'] == 'primary')
					{
						$exists = true;

						if ($index['columns'] !== $index2['columns'])
						{
							$smcFunc['db_remove_index']('{db_prefix}' . $table_name, 'primary', array(), 'ignore');
							$smcFunc['db_add_index']('{db_prefix}' . $table_name, $index, array(), 'ignore');
						}

						break;
					}
					// Make sure index is correct
					elseif (isset($index['name']) && isset($index2['name']) && $index['name'] == $index2['name'])
					{
						$exists = true;

						// Need to be changed?
						if ($index['type'] != $index2['type'] || $index['columns'] !== $index2['columns'])
						{
							$smcFunc['db_remove_index']('{db_prefix}' . $table_name, $index['name'], array(), 'ignore');
							$smcFunc['db_add_index']('{db_prefix}' . $table_name, $index, array(), 'ignore');
						}

						break;
					}
				}

				if (!$exists)
					$smcFunc['db_add_index']('{db_prefix}' . $table_name, $index, array(), 'ignore');
			}

			// Remove unnecassary indexes
			foreach ($currentTable['indexes'] as $index)
			{
				$exists = false;

				foreach ($table['indexes'] as $index2)
				{
					// Primary is special case
					if ($index['type'] == 'primary' && $index2['type'] == 'primary')
						$exists = true;
					// Make sure index is correct
					elseif (isset($index['name']) && isset($index2['name']) && $index['name'] == $index2['name'])
						$exists = true;
				}

				if (!$exists)
				{
					if (isset($table['upgrade']['indexes']))
					{
						foreach ($table['upgrade']['indexes'] as $index2)
						{
							if ($index['type'] == 'primary' && $index2['type'] == 'primary' && $index['columns'] === $index2['columns'])
								$smcFunc['db_remove_index']('{db_prefix}' . $table_name, 'primary', array(), 'ignore');
							elseif (isset($index['name']) && isset($index2['name']) && $index['name'] == $index2['name'] && $index['type'] == $index2['type'] && $index['columns'] === $index2['columns'])
								$smcFunc['db_remove_index']('{db_prefix}' . $table_name, $index['name'], array(), 'ignore');
							elseif (!empty($db_show_debug))
								$log[] = $table_name . ' has Unneeded index ' . var_dump($index);
						}
					}
					elseif (!empty($db_show_debug))
						$log[] = $table_name . ' has Unneeded index ' . var_dump($index);
				}
			}
		}
	}

	if (!empty($log))
		log_error(implode('<br />', $log));

	return $log;
}

function doSettings($addSettings)
{
	global $smcFunc, $modSettings;

	$update = array();

	foreach ($addSettings as $variable => $value)
	{
		list ($value, $overwrite) = $value;

		if ($overwrite || !isset($modSettings[$variable]))
			$update[$variable] = $value;
	}

	if (!empty($update))
		updateSettings($update);
}

function doPermission($permissions)
{
	global $smcFunc;

	$perm = array();

	foreach ($permissions as $permission => $default)
	{
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}permissions
			WHERE permission = {string:permission}',
			array(
				'permission' => $permission
			)
		);

		list ($num) = $smcFunc['db_fetch_row']($result);

		if ($num == 0)
		{
			foreach ($default as $grp)
				$perm[] = array($grp, $permission);
		}
	}

	if (empty($perm))
		return;

	$smcFunc['db_insert']('insert',
		'{db_prefix}permissions',
		array(
			'id_group' => 'int',
			'permission' => 'string'
		),
		$perm,
		array()
	);
}

function updateAdminFeatures($item, $enabled = false)
{
	global $modSettings;

	$admin_features = isset($modSettings['admin_features']) ? explode(',', $modSettings['admin_features']) : array();

	if (!is_array($item))
		$item = array($item);

	if ($enabled)
	{
		foreach ($item as $spec)
			if (!in_array($spec, $admin_features))
				$admin_features[] = $spec;
	}
	else
		$admin_features = array_diff($admin_features, $item);

	updateSettings(array('admin_features' => implode(',', $admin_features)));

	return true;
}

function checkTableExistsArcade($table)
{
	global $db_prefix, $smcFunc;

	if ($smcFunc['db_list_tables'](false, $db_prefix . $table))
		return true;

	return false;
}

function arcadeChangeOld()
{
	global $smcFunc;

	$smcFunc['db_remove_index'] ('{db_prefix}package_servers', 'SMF Arcade Package Server', array(), false);
	$smcFunc['db_remove_index'] ('{db_prefix}settings', 'skin_avatar_size', array(), false);

	// redo member settings
	if (checkTableExistsArcade('arcade_settings') && checkTableExistsArcade('arcade_members'))
	{
		$arcadeSettings = array();
		$request = $smcFunc['db_query']('', '
			SELECT id_member, variable, value
			FROM {db_prefix}arcade_settings
			ORDER BY id_member ASC',
			array(
			)
		);

		while ($row = $smcFunc['db_fetch_row']($request))
		{
			if (empty($arcadeSettings[$row[0]]))
				$arcadeSettings[$row[0]] = array($row[1] => $row[2]);
			else
				$arcadeSettings[$row[0]] += array($row[1] => $row[2]);
		}
		$smcFunc['db_free_result']($request);

		foreach ($arcadeSettings as $member => $value)
		{
			$request = $smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_members
				WHERE id_member = {int:user}',
				array(
					'user' => $member,
				)
			);

			$smcFunc['db_insert']('insert',
				'{db_prefix}arcade_members',
				array(
					'id_member' => 'int',
					'arena_invite' => 'int',
					'arena_match_end' => 'int',
					'arena_new_round' => 'int',
					'champion_email' => 'int',
					'champion_pm' => 'int',
					'games_per_page' => 'int',
					'new_champion_any' => 'int',
					'new_champion_own' => 'int',
					'scores_per_page' => 'int',
					'skin' => 'int',
					'list' => 'int',
				),
				array(
					(int)$member,
					!empty($value['arena_invite']) ? (int)$value['arena_invite'] : 0,
					!empty($value['arena_match_end']) ? (int)$value['arena_match_end'] : 0,
					!empty($value['arena_new_round']) ? (int)$value['arena_new_round'] : 0,
					!empty($value['championEmail']) ? (int)$value['championEmail'] : 0,
					!empty($value['championPM']) ? (int)$value['championPM'] : 0,
					!empty($value['gamesPerPage']) ? (int)$value['gamesPerPage'] : 0,
					!empty($value['new_champion_any']) ? (int)$value['new_champion_any'] : 0,
					!empty($value['new_champion_own']) ? (int)$value['new_champion_own'] : 0,
					!empty($value['scoresPerPage']) ? (int)$value['scoresPerPage'] : 0,
					0,
					0,
				),
				array()
			);
		}
	}

	if (checkTableExistsArcade('arcade_settings'))
		$smcFunc['db_drop_table'] ('{db_prefix}arcade_settings');
}
?>