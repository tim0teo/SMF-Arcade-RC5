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

/*    This file handles the error reporting for SMF Arcade games

	void ArcadeReport()
		- Report Game Errors option for SMF Arcade v2.5
		- Updates report flags to mysql db values

	void checkFieldPDL($tableName,$columnName)
		- Checks if mysql columns exists

	void checkTablePDL($tableName)
		- Checks amount of columns in mysql table

	void check_table_existsPDL($table)
		- Checks if mysql table exists

	void createpdlval1($userid, $count, $year, $day, $latest_year, $latest_day, $permission)
		- Updates mysql columns with new given values for arcade_pdl1 table

	void createpdlval2($gameid, $gamename, $repday, $repyear, $repuser, $repid, $dl_count)
		- Updates mysql columns with new given values for arcade_pdl2 table

	void disableGame($gameid)
		- Disable game that is reported (if enabled)

*/

/*  Add Error Report to the database if logic allows  */
function ArcadeReport()
{
	global $txt, $context, $db_prefix, $smcFunc, $modSettings, $scripturl;
	db_extend('packages');
	$gameid = !empty($_REQUEST['game']) ? (int) $_REQUEST['game'] : 0;
	$repid = 1;
	$url = 'action=arcade;';
	if  (($modSettings['arcadeEnableReport'] == false) || (AllowedTo('arcade_report') == false))
		redirectexit($url);

	$repuser = 0;
	if (empty($context['user']['id']))
		$context['user']['id'] = 0;

	if ((int)$context['user']['id'] > 0)
		$repuser = $context['user']['id'];

	$tableName = 'arcade_pdl2';
	$checkTable = false;
	$checkTable = check_table_existsPDLReport($tableName);
	if ($checkTable == false)
		redirectexit($url);

	$pdl_array1 = array('download_count', 'download_disable', 'report_id', 'report_year', 'report_day', 'id_game');

	/* Set GMT time zone, Check date and then reset back to original time zone */
	$myzone = date("e");
	date_default_timezone_set('GMT');
	$repday = date("z");
	$repyear = date("Y");
	date_default_timezone_set($myzone);

	/* Gather needed mysql db data  */
	$where = "game.id_game = {int:game}";
	$result = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, rep.download_count, rep.report_id, rep.report_day, rep.report_year, rep.download_disable
		FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_pdl2 AS rep ON (rep.pdl_gameid = game.id_game)
		WHERE ' . $where . '
		LIMIT 1',
		array(
			'game' => $gameid,
			'string_empty' => '',
			'member' => $repuser,
		)
	);

	$check = false;
	while ($gamex = $smcFunc['db_fetch_assoc']($result))
	{
		foreach ($pdl_array1 as $pdl1)
		{
			if (empty($gamex[$pdl1]))
				$gamex[$pdl1] = 0;
		}

		if (empty($gamex['game_name']))
			$gamex['game_name'] = false;

		if ($gamex['report_year'] == $repyear && $gamex['report_day'] == $repday)
			$check = true;

		$data_game['pdl'] = array(
			'name' => $gamex['game_name'],
			'count' => $gamex['download_count'],
			'disable' => $gamex['download_disable'],
			'report' => $gamex['report_id'],
			'year' => $gamex['report_year'],
			'day' => $gamex['report_day'],
			'gameid' => $gameid,
		);

	}

	$smcFunc['db_free_result']($result);
	if ($check == true)
		redirectexit($url);

	$gamename = $data_game['pdl']['name'];
	$dl_count = (int)$data_game['pdl']['count'];
	$dl_disable = (int)$data_game['pdl']['disable'];
	createpdlval2($gameid, $gamename, $repday, $repyear, $repuser, $repid, $dl_count, $dl_disable);
	if (empty($modSettings['arcadeEnableGameDisable']))
		$modSettings['arcadeEnableGameDisable'] = false;

	if ($modSettings['arcadeEnableGameDisable'] == true)
		disableGame($gameid);

	redirectexit($url);
}

/* Check if the column exists */
function checkFieldPDLReport($tableName,$columnName)
{
	if (check_table_existsPDLReport($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		if (in_array($columnName, $check))
			return true;
	}

	return false;
}

/*  Returns amount of columns in a table  */
function checkTablePDLReport($tableName)
{
	global $smcFunc;

	if (check_table_existsPDLReport($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		return !empty($check) ? count($check) : false;
	}
	return false;
}

/*  Check if table exists  */
function check_table_existsPDLReport($table)
{
	global $db_prefix, $smcFunc;

	if ($smcFunc['db_list_tables'](false, $db_prefix . $table))
		return true;

	return false;
}

/*  Update arcade_pdl1 values  */
function createpdlval1($userid, $count, $year, $day, $latest_year, $latest_day, $permission)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl1
		WHERE id_member = {int:userid}',
		array('userid' => $userid,
		)
	);

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_pdl1',
		array(
			'id_member' => 'int', 'count' => 'int', 'year' => 'string', 'day' => 'string', 'latest_year' => 'string', 'latest_day' => 'string', 'permission' => 'int',
		),
		array(
			$userid, $count, $year, $day, $latest_year, $latest_day, $permission,
		),
		array('id_member',
		)
	);
}

/*  Update arcade_pdl2 values  */
function createpdlval2($gameid, $gamename, $repday, $repyear, $repuser, $repid, $dl_count, $dl_disable)
{
	global $smcFunc;
	$game_name = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $gamename);

	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl2
		WHERE pdl_gameid = {int:pdl_gameid}',
		array('pdl_gameid' => $gameid,
		)
	);

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_pdl2',
		array(
			'pdl_gameid' => 'int', 'game_name' => 'string', 'report_day' => 'string', 'report_year' => 'string', 'user_id' => 'int', 'report_id' => 'int', 'download_count' => 'int', 'download_disable' => 'int',
		),
		array(
			$gameid, $game_name, $repday, $repyear, $repuser, $repid, $dl_count, $dl_disable,
		),
		array('pdl_gameid',
		)
	);
}

/* Disable the game */
function disableGame($gameid)
{
	if ((int)$gameid < 1)
		return;

	global $db_prefix, $smcFunc;
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_games
		SET enabled = 0
		WHERE id_game = {int:game}',
		array(
			'game' => $gameid,
		)
	);
}
?>