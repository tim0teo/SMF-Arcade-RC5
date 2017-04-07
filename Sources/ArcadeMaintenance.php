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
	void ArcadeMaintenance()
		- ???

	void ArcadeMaintenanceActions()
		- ???

	void MaintenanceFixScores()
		- ???

	void fixScores()
		- ???

	void fixCategories()
		- ???

	void ArcadeMaintenanceCategory()
		- ???

	void ArcadeMaintenanceOnline()
		- ???

	void ArcadeMaintenanceDownload()
		- ???

	void ArcadeScanDir()
		- ???
*/

function ArcadeMaintenance()
{
	global $sourcedir, $scripturl, $txt, $modSettings, $context, $settings;

	require_once($sourcedir . '/Arcade.php');
	require_once($sourcedir . '/Subs-ArcadeAdmin.php');
	require_once($sourcedir . '/ManageServer.php');

	isAllowedTo('arcade_admin');
	loadArcade('admin', 'arcademaintenance');

	// Template
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_maintenance'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_maintenance_desc'];

	$subActions = array(
		'main' => array('ArcadeMaintenanceActions'),
		'highscore' =>  array('ArcadeMaintenanceHighscore'),
		'category' => array('ArcadeMaintenanceCategory'),
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$subActions[$_REQUEST['sa']][0]();
}

function ArcadeMaintenanceActions()
{
	global $sourcedir, $scripturl, $txt, $modSettings, $context, $settings;

	$maintenanceActions = array(
		'fixScores' => array('MaintenanceFixScores'),
		'updateGamecache' => array('MaintenanceGameCache'),
		'onlinePurge' => array('ArcadeMaintenanceOnline'),
		'downloadPurge' => array('ArcadeMaintenanceDownload'),
		'shoutboxPurge' => array('arcadeTruncateShouts'),
		'iconPurge' => array('arcadeCatIconPurge'),
	);

	$context['maintenance_finished'] = false;

	if (!empty($_REQUEST['maintenance']) && isset($maintenanceActions[$_REQUEST['maintenance']]))
	{
		checkSession('request');

		$maintenanceActions[$_REQUEST['maintenance']][0]();

		$context['maintenance_finished'] = true;
	}

	// Template
	$context['sub_template'] = 'arcade_admin_maintenance';
}

function ArcadeMaintenanceHighscore()
{
	global $sourcedir, $scripturl, $txt, $modSettings, $context, $settings, $smcFunc;

	if (isset($_REQUEST['score_action']))
	{
		checkSession();

		if ($_REQUEST['score_action'] == 'older' && is_numeric($_REQUEST['age']))
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_scores
				WHERE end_time < {int:time}',
				array(
					'time' => time() - ((int) $_REQUEST['age'] * 86400)
				)
			);
		}
		elseif ($_REQUEST['score_action'] == 'all')
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_scores',
				array(
				)
			);
		}

		redirectexit('action=admin;area=arcademaintenance;maintenance=fixScores;back=score;' . $context['session_var'] . '=' . $context['session_id']);
	}

	// Template
	$context['sub_template'] = 'arcade_admin_maintenance_highscore';
}

function MaintenanceFixScores()
{
	global $db_prefix, $modSettings, $smcFunc, $context;

	$request = $smcFunc['db_query']('', '
		SELECT id_game, score_type, extra_data
		FROM {db_prefix}arcade_games');

	while ($row = $smcFunc['db_fetch_assoc']($request))
		fixScores($row['id_game'], $row['score_type']);

	$smcFunc['db_free_result']($request);

	if (isset($_REQUEST['back']) && $_REQUEST['back'] == 'score')
		redirectexit('action=admin;area=arcademaintenance;sa=highscore');
}

function MaintenanceGameCache()
{
	global $db_prefix, $modSettings, $smcFunc, $context;

	loadClassFile('Class-Package.php');
	updateGameCache();
}

function fixScores($id_game, $score_type)
{
	global $db_prefix, $modSettings, $smcFunc;

	// This will use a lot of queries so don't use unless necassary ;)

	if ($score_type == 0)
		$order = 'DESC';
	elseif ($score_type == 1)
		$order = 'ASC';
	else
		return false;

	$users = array();
	$position = 1;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS scores, id_member
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}
		GROUP BY id_member',
		array(
			'game' => $id_game,
		)
	);

	$removeScores = array();
	$scoreCount = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($modSettings['arcadeMaxScores']) && $row['scores'] > $modSettings['arcadeMaxScores'])
		{
			$removeScores[$row['id_member']] = $row['scores'] - $modSettings['arcadeMaxScores'];
			$scoreCount[$row['id_member']] = $row['scores'] - $modSettings['arcadeMaxScores'];
		}
		else
		{
			$scoreCount[$row['id_member']] = $row['scores'];
		}
	}
	$smcFunc['db_free_result']($request);

	// Remove some scores
	if (!empty($removeScores))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member, id_score
			FROM {db_prefix}arcade_scores
			WHERE id_game = {int:game}
				AND id_member IN({array_int:members})
			ORDER BY score ' . ($score_type == 0 ? 'ASC' : 'DESC'),
			array(
				'game' => $id_game,
				'members' => array_keys($removeScores)
			)
		);

		$removeIds = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($removeScores[$row['id_member']] > 0)
			{
				$removeIds[] = $row['id_score'];
				$removeScores[$row['id_member']]--;
			}
		}

		if (!empty($removeIds))
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_scores
				WHERE id_score IN({array_int:scores})',
				array(
					'scores' => $removeIds,
				)
			);
	}

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_scores
		SET personal_best = 0
		WHERE id_game = {int:game}',
		array(
			'game' => $id_game,
		)
	);

	$request = $smcFunc['db_query']('', '
		SELECT id_score, score, id_member, position
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}
		ORDER BY score ' . $order,
		array(
			'game' => $id_game,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
		updateGame($id_game, array('champion' => 0, 'champion_score' => 0));

	// Postions and personalbest
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$set = array();

		if (!in_array($row['id_member'], $users))
		{
			$users[] = $row['id_member'];
			$set[] = 'personal_best = 1';
		}

		if ($position != $row['position'])
			$set[] = 'position = {int:position}';

		if (count($set) > 0)
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_scores
				SET ' . implode(',', $set) . '
				WHERE id_score = {int:score}',
				array(
					'score' => $row['id_score'],
					'position' => $position,
				)
			);

		if ($position == 1)
			updateGame($id_game, array('champion' => $row['id_member'], 'champion_score' => $row['id_score'],));

		$position++;
	}
	$smcFunc['db_free_result']($request);

	// And champion times is still left
	$request = $smcFunc['db_query']('', '
		SELECT id_score, score, end_time
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}
		ORDER BY score ' . $order,
		array(
			'game' => $id_game,
		)
	);

	$best = 0;
	$best_id = 0;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (($score_type == 0 && $best <= $row['score']) || ($score_type == 1 && $best >= $row['score']))
		{
			$end = $row['end_time'] - 1;

			if ($best_id > 0)
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_scores
					SET champion_from = end_time, champion_to = {int:champion_to}
					WHERE id_score = {int:best}',
				array(
					'champion_to' => $end,
					'best' => $best_id,
				)
			);

			$best = $row['score'];
			$best_id = $row['id_score'];
		}
	}
	$smcFunc['db_free_result']($request);

	return true;
}

function fixCategories($type = 'undefault')
{
	global $db_prefix, $modSettings, $smcFunc, $context;

	$modSettings['arcadeDefaultCategory'] = !empty($modSettings['arcadeDefaultCategory']) ? (int)$modSettings['arcadeDefaultCategory'] : 0;
	if ($type === 'undefault')
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_game, IFNULL(cat.cat_name, {string:empty}) AS cn
			FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_categories AS cat ON cat.id_cat = game.id_cat',
			array(
				'empty' => '',
			)
		);

		while ($game = $smcFunc['db_fetch_assoc']($request))
			if ($game['cn'] == '')
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					SET id_cat = {int:category}
					WHERE id_game = {int:game}',
					array(
						'category' => $modSettings['arcadeDefaultCategory'],
						'game' => $game['id_game'],
					)
				);

		$smcFunc['db_free_result']($request);
	}
	elseif ($type === 'default')
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_game
			FROM {db_prefix}arcade_games AS game
			ORDER BY id_game',
			array()
		);

		while ($game = $smcFunc['db_fetch_assoc']($request))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_games
				SET id_cat = {int:category}
				WHERE id_game = {int:game}',
				array(
					'category' => $modSettings['arcadeDefaultCategory'],
					'game' => $game['id_game'],
				)
			);

		$smcFunc['db_free_result']($request);
	}

	// recount number of games for each category
	$category = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_game, id_cat
		FROM {db_prefix}arcade_games
		WHERE id_game
		ORDER BY id_game',
		array()
	);

	while ($game = $smcFunc['db_fetch_assoc']($request))
	{
		$id = !empty($game['id_cat']) ? (int)$game['id_cat'] : 0;
		$category[$id] = empty($category[$id]) ? 1 : $category[$id] + 1;
	}
	$smcFunc['db_free_result']($request);

	foreach ($category as $catid => $total)
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_categories
			SET num_games = {int:total}
			WHERE id_cat = {int:category}',
			array(
				'category' => $catid,
				'total' => $total,
			)
		);

	$request = $smcFunc['db_query']('', '
		SELECT id_cat
		FROM {db_prefix}arcade_categories
		WHERE id_cat
		ORDER BY id_cat',
		array()
	);

	while ($cat = $smcFunc['db_fetch_assoc']($request))
	{
		$id = !empty($cat['id_cat']) ? (int)$cat['id_cat'] : 0;
		if(empty($category[$id]))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_categories
				SET num_games = {int:total}
				WHERE id_cat = {int:category}',
				array(
					'category' => $id,
					'total' => 0,
				)
			);
	}

	$smcFunc['db_free_result']($request);

	redirectexit('action=admin;area=arcademaintenance;sa=category;maintenance=done');
}

function ArcadeMaintenanceCategory()
{
	global $sourcedir, $scripturl, $txt, $modSettings, $context, $settings;

	$maintenanceActions = array(
		'ArcadeFixCats' => array('fixCategories'),
	);

	$context['maintenance_finished'] = false;
	$modSettings['arcadeDefaultCategory'] = !empty($modSettings['arcadeDefaultCategory']) ? (int)$modSettings['arcadeDefaultCategory'] : 0;

	if ((isset($_REQUEST['cat_default'])) && $modSettings['arcadeDefaultCategory'] != (int)$_REQUEST['cat_default'])
	{
		checkSession('request');
		$setArray['arcadeDefaultCategory'] = (int)$_REQUEST['cat_default'];
		updateSettings($setArray);
		$modSettings['arcadeDefaultCategory'] = (int)$_REQUEST['cat_default'];
		$context['maintenance_finished'] = true;
	}
	elseif (isset($_REQUEST['cat_action']))
	{
		checkSession('request');
		if ($_REQUEST['cat_action'] == 'peruse')
			fixCategories('peruse');
		elseif ($_REQUEST['cat_action'] == 'default')
			fixCategories('default');
		else
			fixCategories('undefault');

		$context['maintenance_finished'] = true;
	}

	// Template
	$context['sub_template'] = 'arcade_admin_maintenance_category';
}

function ArcadeMaintenanceOnline()
{
	global $smcFunc;
	$time = time();

	// Just check we haven't ended up with something theme exclusive somehow.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_member_data
		WHERE {int:now} - online_time > 600',
		array(
			'now' => $time,
		)
	);
}

function ArcadeMaintenanceDownload()
{
	global $boarddir;
	$files = ArcadeScanDir($boarddir . '/games_download', 'index.php');

	array_map('unlink', $files);
}

function ArcadeScanDir($dir, $ignore = array('index.php'))
{
	$arrfiles = array();
	if (!array($ignore))
		$ignore = array($ignore);

	if (is_dir($dir))
	{
		if ($handle = opendir($dir))
		{
			chdir($dir);
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != ".." && substr($file, -1) !== '~' && !in_array($file, $ignore))
				{
					if (is_dir($file))
					{
						$arr = ArcadeScanDir($file, 'index.php');
						foreach ($arr as $value)
							$arrfiles[] = $dir . '/' . $value;
                    }
					else
                        $arrfiles[] = $dir . '/' . $file;
				}
			}
			chdir("../");
		}
		closedir($handle);
	}

	return $arrfiles;
}

function arcadeTruncateShouts()
{
	global  $smcFunc;
	isAllowedTo('arcade_admin');

	$smcFunc['db_query']('', 'TRUNCATE {db_prefix}arcade_newshouts',array());
}

function arcadeCatIconPurge()
{
	global $settings, $smcFunc;
	list($cats, $theme_paths) = array(array(), array());

	$ignore = array(
		'index.php',
		'arcade_popup_error.gif',
		'arcade_popup_logo.png',
		'cat_new.gif',
		'cup_g.gif',
		'cup_s.gif',
		'cup_b.gif',
		'del.png',
		'del1.png',
		'del2.png',
		'delete.gif',
		'dl_btn.png',
		'dl_btn_popup.png',
		'download2.gif',
		'favorite.gif',
		'favorite2.gif',
		'first.gif',
		'firstxx.gif',
		'game.gif',
		'game_popup_saver.swf',
		'gold.gif',
		'gold1.gif',
		'icon.jpg',
		'medals.png',
		'modify.gif',
		'modify.png',
		'noavatar.gif',
		'pdl_clean.gif',
		'pdl_download.gif',
		'popup_ie_bg.png',
		'popup_play_btn.gif',
		'second.gif',
		'star.gif',
		'star2.gif',
		'thearcade.png',
		'third.gif',
		'tick.gif',
		'arcade_esc.png',
		);

	$request = $smcFunc['db_query']('', '
		SELECT cat_icon
		FROM {db_prefix}arcade_categories
		ORDER BY cat_icon ASC',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($cats['cat_icon']))
			$cats[] = $row['cat_icon'];
	}
	$smcFunc['db_free_result']($request);

	$files = ArcadeScanDir($settings['default_theme_dir'] . '/images/arc_icons', array_merge_recursive($cats, $ignore));

	foreach ($files as $file)
	{
		if ((!empty($file)) && file_exists($file))
			unset($file);
	}

	// remove files leftover in custom themes
	$request = $smcFunc['db_query']('', '
		SELECT id_theme, variable, value
		FROM {db_prefix}themes
		WHERE variable = {string:themedir}',
		array(
			'themedir' => 'theme_dir',
		)
	);
	$theme_paths = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$theme_paths[] = $row['value'];

	$smcFunc['db_free_result']($request);

	foreach ($theme_paths as $path)
	{
		if (is_dir($path))
		{
			$customFiles = ArcadeScanDir($path . '/images/arc_icons', array());

			foreach ($customFiles as $file)
				if ((!empty($file)) && file_exists($file))
					unset($file);

			$customFilesCheck = ArcadeScanDir($path . '/images/arc_icons', array());
			if (empty($customFilesCheck))
				rmdir($path);
		}
	}
}
?>