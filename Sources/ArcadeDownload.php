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

/*   This file handles the download option for the arcade

	void ArcadeDownload()
		- Download option for SMF Arcade v2.5
		- Auto-zip game files
		- Updates mysql database values for download data

	void deleteArchives($directory, $empty = true)
		- Purges download directory (if enabled from Arcade/Admin/Settings)

	void checkFieldPDL($tableName,$columnName)
		- Checks if mysql columns exists

	void checkTablePDL($tableName)
		- Checks amount of columns in mysql table

	void check_table_existsPDL($table)
		- Checks if mysql table exists

	void createxpdlval1($tableName, $userid, $count, $year, $day, $latest_year, $latest_day, $permission)
		- Updates mysql columns for arcade_pdl1 table

	void createxpdlval2($tableName, $id_of_game, $gamename_name, $repday, $repyear, $repuser, $repid, $dl_count, $dl_disable)
		- Updates mysql columns for arcade_pdl2 table

*/

function ArcadeDownload()
{
	global $db_prefix, $smcFunc, $context, $boardurl, $modSettings, $user_info, $txt;
	if (empty($modSettings['gamesUrl']))
		$main = 'Games';
	else
		$main = preg_replace('~'.$boardurl.'/~', '', $modSettings['gamesUrl']);

	ob_end_clean();
	@ob_start('ob_gzhandler');
	if (AllowedTo('arcade_download') == false)
	{
		fatal_lang_error('pdl_error_perm', false);
		$a = 'Unauthorized';
		return $a;
		exit();
	}

	/* edit the zip storage directory and Games folder here (only change if needed) */
	$gamesave = 'games_download/';
	$mygames_folder = $boardurl . '/'.$main.'/';

	/*   zero out/set variables   */
	$gamefile_name = '';
	$gamename_name = '';
	$gamedirectory = '';
	$dl_count = 0;
	$id_of_game = '';
	$latest_day = 0;
	$latest_year = 0;
	$permission = 0;
	$checkpdlgame = false;
	$pdl_arrayx = array('pdl_gameid', 'download_count', 'download_disable', 'latest_day', 'latest_year', 'permission', 'report_id', 'report_day', 'report_year', 'user_id');
	if (empty($modSettings['pdl_DownMax']))
		$modSettings['pdl_DownMax'] = 0;

	$id_of_game = !empty($_REQUEST['game']) ? (int) $_REQUEST['game'] : 0;
	$arcade_amount = 1;
	$search1 = 'id_game ='. $id_of_game;
	if ($context['user']['is_logged'])
	{
		$search2 = 'id_member ='. $context['user']['id'];
		$member = $context['user']['id'];
	}
	else
	{
		$search2 = 0;
		$member = 0;
	}

	/* Clear the game archives library if set */
	if (!empty($modSettings['arcadeDisableArchive']))
	{
		if ($modSettings['arcadeDisableArchive'] == 1)
			echo deleteArchives($gamesave, $empty = "true");

	}

	/* Check if download is enabled */
	if (empty($modSettings['arcadeEnableDownload']))
		$modSettings['arcadeEnableDownload'] = false;

	if (($modSettings['arcadeEnableDownload'] == false) && (AllowedTo('arcade_admin') == false))
	{
		fatal_lang_error('pdl_error_disable', false);
		exit();
	}

	/* Check number of posts */
	if (isset($modSettings['arcadeDownPost']))
	{
		if (($user_info['posts'] < $modSettings['arcadeDownPost']) && (AllowedTo('arcade_admin') == false))
		{
			$txt['pdl_error3'] = fatal_lang_error( 'pdl_error_post', false);
			exit();
		}
	}

	if ($id_of_game == false)
	{
		fatal_lang_error('pdl_error_nogame', false);
		exit();
	}

	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.game_directory, game.game_file, pdl.latest_day, pdl.latest_year, pdl.permission, rep.report_day, rep.report_year, rep.user_id, rep.report_id, rep.download_count, rep.download_disable
		FROM {db_prefix}arcade_games AS game
		LEFT JOIN {db_prefix}arcade_pdl1 AS pdl ON (pdl.id_member = {int:member})
		LEFT JOIN {db_prefix}arcade_pdl2 AS rep ON (rep.pdl_gameid = {int:search})
		WHERE ' . $search1 .'
		ORDER BY game.id_game
		LIMIT 1',
		array('search' => $id_of_game, 'member' => $member)
	);

	$checkpdlgame = true;
	while ($gameInfo = $smcFunc['db_fetch_assoc']($request))
	{
		foreach ($pdl_arrayx as $pdlx)
		{
			if (empty($gameInfo[$pdlx]))
				$gameInfo[$pdlx] = 0;
		}

		$gamefile_name = $gameInfo['game_file'];
        $gamename_name = $gameInfo['game_name'];
        $gamedirectory = $gameInfo['game_directory'];
		$latest_day = (int)$gameInfo['latest_day'];
		$latest_year = (int)$gameInfo['latest_year'];
		$permission = (int)$gameInfo['permission'];
		$dl_count =  (int)$gameInfo['download_count'] + 1;
		$dl_disable = (int)$gameInfo['download_disable'];
		$repday = (int)$gameInfo['report_day'];
		$repyear = (int)$gameInfo['report_year'];
		$repid = (int)$gameInfo['report_id'];
		$repuser = (int)$gameInfo['user_id'];
	}

	$smcFunc['db_free_result']($request);
	$gamefile_name = str_replace (".swf", "", $gamefile_name);
	$gamefile_name = str_replace (".php", "", $gamefile_name);
	$gamefile_name = str_replace (" ", "_", $gamefile_name);

	if ($gamefile_name == false)
	{
		fatal_lang_error('pdl_error_db', false);
		exit();
	}

	/* Check if zip or tar file already exists and if yes download - if not zip and download */
	$url_array[1] = $gamesave . $gamefile_name . '.zip';
	$url_array[2] = $gamesave . $gamefile_name . '.tar';
	$url_array[3] = $gamesave . 'game_' . $gamefile_name . '.zip';
	$url_array[4] = $gamesave . 'game_' . $gamefile_name . '.tar';

	/* Check to see if admin has manually denied downloading for this game - password is the file prefix */
	$deny = 'DENY_';
	if (!empty($modSettings['arcadeDownPass']))
		$deny = $modSettings['arcadeDownPass'] . '_';

	$url_skip[1] = $gamesave . $deny . $gamefile_name . '.zip';
	$url_skip[2] = $gamesave . $deny . $gamefile_name . '.tar';
	$ct = 1;
	while ($ct < 3)
	{
		if ((file_exists($url_skip[$ct])) && (AllowedTo('arcade_admin') == false))
		{
			fatal_lang_error('pdl_error_dl', false);
			exit();
		}

		$ct++;
	}

	/* Check to see if downloading is denied from the games settings  */
	if (($dl_disable > 0)  && (AllowedTo('arcade_admin') == false))
	{
		fatal_lang_error('pdl_error_dl', false);
		exit();
	}

	if ($context['user']['is_logged'] && $search2 !== 0 && $modSettings['pdl_DownMax'] > 0)
	{
		/* Check for daily download limit  */
		$myzone = date("e");
		date_default_timezone_set('GMT');
		$day = date("z");
		$year = date("Y");
		$max = ((int)$modSettings['pdl_DownMax'] - 1);
		$tableName = 'arcade_pdl1';
		$columnName = 'count';
		if (empty($count))
			$count = 0;

		$request = $smcFunc['db_query']('', '
			SELECT game.id_member, game.count, game.year, game.day
			FROM {db_prefix}arcade_pdl1 AS game
			WHERE ' . $search2 .'
			ORDER BY game.id_member
			LIMIT 1',
			array('search' => $id_of_game,)
		);

		$user_info['count'] = 0;
		$user_info['day'] = false;
		$user_info['year'] = false;
		while ($gameInfo = $smcFunc['db_fetch_assoc']($request))
		{
			if ((int)$gameInfo['count'] > 0)
			{
				$user_info['count'] = (int)$gameInfo['count'];
				$user_info['day'] = $gameInfo['day'];
				$user_info['year'] = $gameInfo['year'];
			}
			else
			{
				$user_info['count'] = 0;
				$user_info['day'] = $day;
				$user_info['year'] = $year;
			}
		}

		$smcFunc['db_free_result']($request);

		/* Check date */
		$count = 1;
		if ($day == $user_info['day'] && $year == $user_info['year'])
		{
			if  (($user_info['count'] > $max) && (AllowedTo('arcade_admin') == false))
			{
				fatal_lang_error('pdl_error_max', false);
				exit();
			}
			else
				$count = (int)$user_info['count'] + 1;
		}

		$tableName = 'arcade_pdl1';
		$userid = $context['user']['id'];
		/* Update user count */
		createxpdlval1($tableName, $userid, $count, $year, $day, $latest_year, $latest_day, $permission);
		/* reset time zone back to original setting  */
		date_default_timezone_set($myzone);
	}

	$tableName = 'arcade_pdl2';
	/* Update download count */
	createxpdlval2($tableName, $id_of_game, $gamename_name, $repday, $repyear, $repuser, $repid, $dl_count, $dl_disable);
	$ct = 1;
	while ($ct < 5)
	{
		if (file_exists($url_array[$ct]))
		{
			ob_start();
			@header('Location: ' . $url_array[$ct]);
			ob_end_flush();
			exit();
		}
		$ct++;
	}
	/*  It's not in your zip library, so let's add it...  */
	$directoryToZip1= $main.'/'.$gamedirectory;
	$directoryToZip2='arcade/gamedata/'.$gamefile_name;
	$outputDir= '';
	$zipName= 'game_' . $gamefile_name.'.zip';
	if (file_exists('Sources/CreateZipFile.php'))
	{
		include_once("Sources/CreateZipFile.php");
		if (is_dir($directoryToZip1))
		{
			$createZipFile=new CreateZipFile;
			/* If there is no sub-directory  */
			if (empty($gamedirectory))
			{
				if (is_dir($directoryToZip2))
					$createZipFile->zipDirectory2($directoryToZip2, $outputDir);

				$createZipFile->zipDirectory4($gamefile_name, $outputDir, '');
			}
			/* If the gamename and filename do not match ... assume it is a sub-directory gamepack and scope out specific files */
			elseif ($gamedirectory !== $gamefile_name)
			{
				if (is_dir($directoryToZip2))
					$createZipFile->zipDirectory2($directoryToZip2, $outputDir);

				$createZipFile->zipDirectory4($gamefile_name, $outputDir, $gamedirectory);
			}
			/*  else create zip from default sub-directory setup  */
			else
			{
				if (!file_exists($directoryToZip1 . '/gamedata/' . $gamefile_name . '/' . $gamefile_name . '.txt') && is_dir($directoryToZip2))
					$createZipFile->zipDirectory2($directoryToZip2, $outputDir);

				$createZipFile->ZipDirectory3($directoryToZip1,$outputDir, $gamedirectory);
			}
			/* Create gamedata folder if it exists  */
			/* if (is_dir($directoryToZip2))
				{$createZipFile->zipDirectory2($directoryToZip2, $outputDir);}  */

			$zipName= 'games_download/'.$zipName;
			$fd=fopen($zipName, "wb");
			$out = fwrite($fd,$createZipFile->getZippedfile());
			fclose($fd);
			$createZipFile->forceDownload($zipName);
		}

		if (file_exists($zipName))
		{
			ob_start();
			@header('Location: ' . $zipName);
			ob_end_flush();
			exit();
		}
	}

	fatal_lang_error('pdl_error_db', false);
	exit();
}

/* START - Delete archives function */
function deleteArchives($directory, $empty = true)
{
	global $modSettings;
	$action_b = 'DENY_';
	if (!empty($modSettings['arcadeDownPass']))
		$action_b = $modSettings['arcadeDownPass'];

    if(substr($directory,-1) == "/")
        $directory = substr($directory,0,-1);

    if(!file_exists($directory) || !is_dir($directory))
        return false;
    elseif(!is_readable($directory))
        return false;
    else
	{
        $directoryHandle = opendir($directory);

        while ($contents = readdir($directoryHandle))
		{
            if($contents != '.' && $contents != '..')
			{
				$path = $directory . "/" . $contents;
				$action_a = $contents;
				$trigger = false;
				if ($contents == '.htaccess' || $contents == 'index.php')
					$trigger = true;
				if ((strstr($action_a,$action_b)))
					$trigger = true;
				if (is_dir($path))
					deleteArchives($path);
                elseif ($trigger == false)
                    unlink($path);
            }
        }

        closedir($directoryHandle);

        if($empty == false)
		{
            if(!rmdir($directory))
                return false;
		}

        return true;
    }
}


/* Check if the column exists */
function checkFieldPDL($tableName,$columnName)
{
	$checkTable = false;
	$checkTable = check_table_existsPDL($tableName);
	if ($checkTable == true)
	{
		global $db_prefix, $smcFunc;
		$check = false;
		$checkval = false;
		$check = $smcFunc['db_query']('', "DESCRIBE {$db_prefix}$tableName $columnName");
		$checkval = $smcFunc['db_num_rows']($check);
		$smcFunc['db_free_result']($check);
		if ($checkval > 0)
			return true;
	}
	return false;
}

/*  Returns amount of columns in a table  */
function checkTablePDL($tableName)
{
	$checkTable = false;
	$checkTable = check_table_existsPDL($tableName);
	if ($checkTable == true)
	{
		global $db_prefix, $smcFunc;
		$check = false;
		$checkval = false;
		$check = $smcFunc['db_query']('', "DESCRIBE {$db_prefix}$tableName");
		$checkval = $smcFunc['db_num_rows']($check);
		$smcFunc['db_free_result']($check);
		if ($checkval > 0)
			return $checkval;
	}

	return false;
}

/*  Check if table exists  */
function check_table_existsPDL($table)
{
	global $db_prefix, $smcFunc;
	$check = false;
	$checkval = false;
	$check = $smcFunc['db_query']('', "SHOW TABLES LIKE '{$db_prefix}$table'");
	$checkval = $smcFunc['db_num_rows']($check);
	$smcFunc['db_free_result']($check);
	if ($checkval >0) {return true;}
		return false;
}

/*  Update arcade_pdl1 values  */
function createxpdlval1($tableName, $userid, $count, $year, $day, $latest_year, $latest_day, $permission)
{
	global $smcFunc, $db_prefix, $db_name;
	$mydb = $db_name;
	$i = 0;
	$request = false;
	$request = $smcFunc['db_query']('', "DELETE FROM `{db_prefix}$tableName` WHERE `{db_prefix}$tableName`.`id_member` = '$userid'");
	$request = $smcFunc['db_query']('', "INSERT INTO `{db_prefix}$tableName` (`id_member`, `count`, `year`, `day`, `latest_year`, `latest_day`, `permission`)
		VALUES ('$userid', '$count', '$year', '$day', '$latest_year', '$latest_day', '$permission')");
}

/* Update arcade_pdl2 values  */

function createxpdlval2($tableName, $id_of_game, $gamename_name, $repday, $repyear, $repuser, $repid, $dl_count, $dl_disable)
{
	global $smcFunc, $db_prefix;
	$tableName = 'arcade_pdl2';
	$columnName = 'pdl_gameid';
	$gamename = cleanGamenameQuery($gamename_name);
	$request = $smcFunc['db_query']('', "DELETE FROM `{db_prefix}$tableName` WHERE `{db_prefix}$tableName`.`pdl_gameid` = '$id_of_game'");
	$request = $smcFunc['db_query']('', "
		INSERT INTO `{db_prefix}$tableName`
		(`pdl_gameid`, `game_name`, `report_day`, `report_year`, `user_id`, `report_id`, `download_count`, `download_disable`)
		VALUES ('$id_of_game', '$gamename', '$repday', '$repyear', '$repuser', '$repid', '$dl_count', '$dl_disable')"
	);
}

/* mysql query filter */
function cleanGamenameQuery($string = false)
{
	if(get_magic_quotes_gpc())
		$string = stripslashes($string);

	if(is_array($string))
        return array_map(__METHOD__, $string);

    if(!empty($string) && is_string($string))
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string);

	return $string;
}
?>