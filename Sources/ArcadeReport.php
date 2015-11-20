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
	global $db_prefix, $smcFunc;
	$check = false;
	$checkval = false;
	$check = $smcFunc['db_query']('', "DESCRIBE {$db_prefix}$tableName $columnName");
	$checkval = $smcFunc['db_num_rows']($check);
	$smcFunc['db_free_result']($check);
	if ($checkval > 0)
		return true;

	return false;
}

/*  Returns amount of columns in a table  */
function checkTablePDLReport($tableName)
{
	global $db_prefix, $smcFunc;
	$check = false;
	$checkval = false;
	$check = $smcFunc['db_query']('', "DESCRIBE {$db_prefix}$tableName");
	$checkval = $smcFunc['db_num_rows']($check);
	$smcFunc['db_free_result']($check);
	if ($checkval > 0)
		return $checkval;

	return false;
}

/*  Check if table exists  */
function check_table_existsPDLReport($table)
{
	global $db_prefix, $smcFunc;
	$check = false;
	$checkval = false;
	$check = $smcFunc['db_query']('', "SHOW TABLES LIKE '{$db_prefix}$table'");
	$checkval = $smcFunc['db_num_rows']($check);
	$smcFunc['db_free_result']($check);
	if ($checkval >0)
		return true;

	return false;
}

/*  Update arcade_pdl1 values  */
function createpdlval1($userid, $count, $year, $day, $latest_year, $latest_day, $permission)
{
	global $smcFunc, $db_prefix, $db_name;
	$tableName = 'arcade_pdl1';
	$request = false;
	$request = $smcFunc['db_query']('', "DELETE FROM `{db_prefix}$tableName` WHERE `{db_prefix}$tableName`.`id_member` = '$userid'");
	$request = $smcFunc['db_query']('', "INSERT INTO `{db_prefix}$tableName` (`id_member` , `count` ,`year` ,`day` ,`latest_year` ,`latest_day` ,`permission`)
               VALUES ('$userid', '$count', '$year', '$day', '$latest_year', '$latest_day', '$permission')");
}

/*  Update arcade_pdl2 values  */
function createpdlval2($gameid, $gamename, $repday, $repyear, $repuser, $repid, $dl_count, $dl_disable)
{
	global $smcFunc, $db_prefix, $db_name;
	$tableName = 'arcade_pdl2';
	$request = $smcFunc['db_query']('', "DELETE FROM `{db_prefix}$tableName` WHERE `{db_prefix}$tableName`.`pdl_gameid` = '$gameid'");
	$request = $smcFunc['db_query']('', "INSERT INTO `{db_prefix}$tableName` (`pdl_gameid`, `game_name`, `report_day`, `report_year`, `user_id`, `report_id`, `download_count`, `download_disable`)
				VALUES ('$gameid', '$gamename', '$repday', '$repyear', '$repuser', '$repid', '$dl_count', '$dl_disable')");
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