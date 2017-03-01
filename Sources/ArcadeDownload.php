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

	void arcade_create_zip($files, $destination, $overwrite)
		- Zip an array of files

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
	global $db_prefix, $smcFunc, $context, $boardurl, $modSettings, $user_info, $txt, $boarddir;

	if (empty($modSettings['gamesUrl']))
		$main = 'Games';
	else
		$main = preg_replace('~' . $boardurl . '/~', '', $modSettings['gamesUrl']);

	if (AllowedTo('arcade_download') == false)
	{
		fatal_lang_error('pdl_error_perm', false);
		$a = 'Unauthorized';
		return $a;
		exit();
	}

	/*   zero out/set variables   */
	list($files, $outputDir, $ct, $checkpdlgame, $arcade_amount, $gamefile_name, $gamename_name, $gamedirectory, $dl_count, $id_of_game, $latest_day, $latest_year, $permission, $checkpdlgame, $gamesave, $mygames_folder) = array(
		array(), '', 1, true, 1, '', '', '', 0, '', 0, 0, 0, false, 'games_download/', $boardurl . '/' . $main . '/'
	);
	$pdl_arrayx = array('pdl_gameid', 'download_count', 'download_disable', 'latest_day', 'latest_year', 'permission', 'report_id', 'report_day', 'report_year', 'user_id');
	if (empty($modSettings['pdl_DownMax']))
		$modSettings['pdl_DownMax'] = 0;

	$id_of_game = !empty($_REQUEST['game']) ? (int) $_REQUEST['game'] : 0;
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

	if (empty($modSettings['arcadeEnableDownload']) && !allowedTo('arcade_admin'))
		fatal_lang_error('pdl_error_disable', false);

	/* Check number of posts */
	if (isset($modSettings['arcadeDownPost']))
	{
		if (($user_info['posts'] < $modSettings['arcadeDownPost']) && !allowedTo('arcade_admin'))
			$txt['pdl_error3'] = fatal_lang_error( 'pdl_error_post', false);
	}

	if ($id_of_game == false)
		fatal_lang_error('pdl_error_nogame', false);

	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.game_directory, game.game_file, pdl.latest_day, pdl.latest_year, pdl.permission, rep.report_day,
		rep.report_year, rep.user_id, rep.report_id, rep.download_count, rep.download_disable, game.thumbnail, game.thumbnail_small
		FROM {db_prefix}arcade_games AS game
		LEFT JOIN {db_prefix}arcade_pdl1 AS pdl ON (pdl.id_member = {int:member})
		LEFT JOIN {db_prefix}arcade_pdl2 AS rep ON (rep.pdl_gameid = {int:search})
		WHERE ' . $search1 .'
		ORDER BY game.id_game
		LIMIT 1',
		array('search' => $id_of_game, 'member' => $member)
	);

	while ($gameInfo = $smcFunc['db_fetch_assoc']($request))
	{
		foreach ($pdl_arrayx as $pdlx)
		{
			if (empty($gameInfo[$pdlx]))
				$gameInfo[$pdlx] = 0;
		}

		$thumbnail = !empty($gameinfo['thumbnail']) ? $gameinfo['thumbnail'] : '';
		$thumbnailsmall = !empty($gameinfo['thumbnail_small']) ? $gameinfo['thumbnail_small'] : '';
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
		fatal_lang_error('pdl_error_db', false);

	/* Check if zip or tar file already exists and if yes download - if not zip and download */
	$url_array[1] = $boarddir . '/' . $gamesave . $gamefile_name . '.zip';
	$url_array[2] = $boarddir . '/' . $gamesave . $gamefile_name . '.tar';
	$url_array[3] = $boarddir . '/' . $gamesave . 'game_' . $gamefile_name . '.zip';
	$url_array[4] = $boarddir . '/' . $gamesave . 'game_' . $gamefile_name . '.tar';

	/* Check to see if admin has manually denied downloading for this game - password is the file prefix */
	$deny = empty($modSettings['arcadeDownPass']) ? 'DENY_' : $modSettings['arcadeDownPass'] . '_';

	$url_skip[1] = $boarddir . '/' . $gamesave . $deny . $gamefile_name . '.zip';
	$url_skip[2] = $boarddir . '/' . $gamesave . $deny . $gamefile_name . '.tar';

	while ($ct < 3)
	{
		if ((file_exists($url_skip[$ct])) && !allowedTo('arcade_admin'))
			fatal_lang_error('pdl_error_dl', false);
		$ct++;
	}

	/* Check to see if downloading is denied from the games settings  */
	if ($dl_disable > 0  && !allowedTo('arcade_admin'))
		fatal_lang_error('pdl_error_dl', false);

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
				fatal_lang_error('pdl_error_max', false);
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
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="game_' . $gamefile_name . '.zip"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($url_array[$ct]));
			ob_end_flush();
			readfile($url_array[$ct]);
			exit;
		}
		$ct++;
	}
	// It's not in your zip library, so let's add it...
	$directoryToZip1 = $main . '/' . $gamedirectory;
	$directoryToZip2 = 'arcade/gamedata/' . $gamefile_name;

	list($gamefiles, $gamedatafiles, $zipName) = array(array(), array(), $boarddir . '/games_download/' . $gamefile_name);

	if (is_dir($boarddir . '/' . $directoryToZip1))
	{
		// no game directory?
		if (empty($gamedirectory))
		{
			if ((!empty($thumbnailsmall)) && file_exists($boarddir . '/' . $main . '/' . $thumbnailsmall))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $thumbnailsmall;
			if ((!empty($thumbnail)) && file_exists($boarddir . '/' . $main . '/' . $thumbnail))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $thumbnailsmall;
			if ((!empty($gameInfo['game_file'])) && file_exists($boarddir . '/' . $main . '/' . $gameInfo['game_file']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gameInfo['game_file'];
			// gamedata files?
			if (is_dir($boarddir . '/' . $directoryToZip2))
				$gamedatafiles = scandir($boarddir . '/' . $directoryToZip2);

			unset($gamefiles[array_search('.', $gamefiles)]);
			unset($gamefiles[array_search('..', $gamefiles)]);
			unset($gamedatafiles[array_search('.', $gamedatafiles)]);
			unset($gamedatafiles[array_search('..', $gamedatafiles)]);
			foreach($gamefiles as $key => $value)
				$gamefiles[$key] = $boarddir . '/' . $main . '/' . $value;
			foreach($gamedatafiles as $key => $value)
				$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;
		}
		// inside a gamepack ?
		elseif ($gamedirectory !== $gamefile_name)
		{
			if ((!empty($thumbnailsmall)) && file_exists($boarddir . '/' . $main . '/' . $gamedirectory . '/' . $thumbnailsmall))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gamedirectory . '/' . $thumbnailsmall;
			if ((!empty($thumbnail)) && file_exists($boarddir . '/' . $main . '/' . $gamedirectory . '/' . $thumbnail))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gamedirectory . '/' . $thumbnailsmall;
			if ((!empty($gameInfo['game_file'])) && file_exists($boarddir . '/' . $main . '/' . $gamedirectory . '/' . $gameInfo['game_file']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gamedirectory . '/' . $gameInfo['game_file'];
			// gamedata files?
			if (is_dir($boarddir . '/' . $directoryToZip2))
				$gamedatafiles = scandir($boarddir . '/' . $directoryToZip2);

			unset($gamefiles[array_search('.', $gamefiles)]);
			unset($gamefiles[array_search('..', $gamefiles)]);
			unset($gamedatafiles[array_search('.', $gamedatafiles)]);
			unset($gamedatafiles[array_search('..', $gamedatafiles)]);
			foreach($gamefiles as $key => $value)
				$gamefiles[$key] = $boarddir . '/' . $main . '/' . $gamedirectory . '/' . $value;
			foreach($gamedatafiles as $key => $value)
				$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;
		}
		// proper default subdirectory
		else
		{
			// gamedata files if they are not also present in the default subdirectory else use them from the default directory
			if (!file_exists($boarddir . '/' . $directoryToZip1 . '/gamedata/' . $gamefile_name . '/' . $gamefile_name . '.txt') && is_dir($boarddir . '/' . $directoryToZip2))
			{
				$gamedatafiles = scandir($boarddir . '/' . $directoryToZip2);
				unset($gamedatafiles[array_search('.', $gamedatafiles)]);
				unset($gamedatafiles[array_search('..', $gamedatafiles)]);
				foreach($gamedatafiles as $key => $value)
					$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;
			}
			elseif (file_exists($directoryToZip1 . '/gamedata/' . $gamefile_name . '/' . $gamefile_name . '.txt'))
			{
				$gamedatafiles = scandir($boarddir . '/' . $directoryToZip1 . '/gamedata');
				unset($gamedatafiles[array_search('.', $gamedatafiles)]);
				unset($gamedatafiles[array_search('..', $gamedatafiles)]);
				foreach($gamedatafiles as $key => $value)
					$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip1 . '/gamedata/' . $gamefile_name . '/' . $value;
			}

			$gamefiles = scandir($boarddir . '/' . $directoryToZip1);
			unset($gamefiles[array_search('.', $gamefiles)]);
			unset($gamefiles[array_search('..', $gamefiles)]);
			foreach($gamefiles as $key => $value)
				$gamefiles[$key] = $boarddir . '/' . $directoryToZip1 . '/' . $value;


		}

		$files = array_merge_recursive($gamefiles, $gamedatafiles);
		arcade_create_zip($files, $boarddir . '/' . $gamesave . 'game_' . $gamefile_name . '.zip');

		if (file_exists($boarddir . '/' . $gamesave . 'game_' . $gamefile_name . '.zip'))
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="game_' . $gamefile_name . '.zip"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($boarddir . '/' . $gamesave . 'game_' . $gamefile_name . '.zip'));
			ob_end_flush();
			readfile($boarddir . '/' . $gamesave . 'game_' . $gamefile_name . '.zip');
			exit;
		}
		return true;
	}

	fatal_lang_error('pdl_error_db', false);
	exit;
}

function arcade_create_zip($files = array(),$destination = '',$overwrite = false)
{
	if(file_exists($destination) && !$overwrite)
		return false;

	list($valid_files, $pathParts) = array(array(), array());

	if(is_array($files))
	{
		foreach($files as $file)
		{
			if(file_exists($file))
				$valid_files[] = $file;
		}
	}

	if(count($valid_files))
	{
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true)
			return false;

		foreach($valid_files as $file)
		{
			$pathinfo = pathinfo($file);
			$pathParts = explode('/', stripslashes(rtrim($pathinfo['dirname'], '/')));
			$count = count($pathParts);

			if ((!empty($pathParts[$count-2])) && $pathParts[$count-2] == 'gamedata')
				$newfile = $pathParts[$count-2] . '/' . $pathParts[$count-1] . '/' . basename($file);
			else
				$newfile = basename($file);

			$zip->addFile($file, $newfile);
		}

		$zip->close();

        return file_exists($destination);
	}
	else
		return false;
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