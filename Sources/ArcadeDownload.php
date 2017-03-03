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
//   This file handles the download option for the arcade
/*
	void ArcadeDownload()
		- Download option for SMF Arcade v2.5
		- Auto-zip game files
		- Updates mysql database values for download data

	void arcade_game_down($data, $filepath)
		- creates php file containing game information

	void arcade_scan_dir($files, $destination, $overwrite)
		- returns all files including subfolder paths

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

	void createxpdlval1($userid, $count, $year, $day, $latest_year, $latest_day, $permission)
		- Updates mysql columns for arcade_pdl1 table

	void createxpdlval2($gameid, $gamename, $repday, $repyear, $repuser, $repid, $dl_count, $dl_disable)
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

	$pdl_arrayx = array(
		'pdl_gameid',
		'download_count',
		'download_disable',
		'latest_day',
		'latest_year',
		'permission',
		'report_id',
		'report_day',
		'report_year',
		'user_id'
	);
	if (empty($modSettings['pdl_DownMax']))
		$modSettings['pdl_DownMax'] = 0;

	$id_of_game = !empty($_REQUEST['game']) ? (int) $_REQUEST['game'] : 0;
	$search1 = 'id_game ='. $id_of_game;
	if (!$user_info['is_guest'])
	{
		$search2 = 'id_member ='. $user_info['id'];
		$member = $user_info['id'];
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
			deleteArchives('games_download/', $empty = "true");
	}
	/* Check if download is enabled */
	$modSettings['arcadeEnableDownload'] = empty($modSettings['arcadeEnableDownload']) ? false : true;

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
		SELECT game.id_game, game.game_name, game.game_directory, game.game_file, pdl.latest_day, pdl.latest_year, pdl.permission, rep.report_day, game.id_cat, game.game_rating, game.description, game.internal_name,
		rep.report_year, rep.user_id, rep.report_id, rep.download_count, rep.download_disable, game.thumbnail, game.thumbnail_small, game.extra_data, game.submit_system, game.enabled, game.score_type
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
			if (empty($gameInfo[$pdlx]))
				$gameInfo[$pdlx] = 0;

		$gameData = array(
			'id_game' => !empty($gameInfo['id_game']) ? $gameInfo['id_game'] : 0,
			'enabled' => !empty($gameInfo['enabled']) ? $gameInfo['enabled'] : 0,
			'score_type' => !empty($gameInfo['score_type']) ? $gameInfo['score_type'] : 0,
			'gamename' => !empty($gameInfo['game_name']) ? $gameInfo['game_name'] : '',
			'internal_name' => !empty($gameInfo['internal_name']) ? $gameInfo['internal_name'] : '',
			'help' => !empty($gameInfo['help']) ? $gameInfo['help'] : '',
			'description' => !empty($gameInfo['description']) ? $gameInfo['description'] : '',
			'game_directory' => !empty($gameInfo['game_directory']) ? $gameInfo['game_directory'] : '',
			'game_file' => !empty($gameInfo['game_file']) ? $gameInfo['game_file'] : 'generated_file.swf',
			'gamephp' => !empty($gameInfo['game_file']) ? str_replace('.swf', '.php', $gameInfo['game_file']) : 'generated_file.php',
			'latest_day' => !empty($gameInfo['latest_day']) ? $gameInfo['latest_day'] : '',
			'latest_year' => !empty($gameInfo['latest_year']) ? $gameInfo['latest_year'] : '',
			'permission' => !empty($gameInfo['permission']) ? (int)$gameInfo['permission'] : 0,
			'report_day' => !empty($gameInfo['report_day']) ? $gameInfo['report_day'] : '',
			'report_year' => !empty($gameInfo['report_year']) ? $gameInfo['report_year'] : '',
			'user_id' => !empty($gameInfo['user_id']) ? $gameInfo['user_id'] : 0,
			'report_id' => !empty($gameInfo['report_id']) ? $gameInfo['report_id'] : 0,
			'thumbnail' => !empty($gameInfo['thumbnail']) ? $gameInfo['thumbnail'] : '',
			'thumbnail_small' => !empty($gameInfo['thumbnail_small']) ? $gameInfo['thumbnail_small'] : '',
			'extra_data' => !empty($gameInfo['extra_data']) ? unserialize($gameInfo['extra_data']) : array(),
			'id_cat' => !empty($gameInfo['id_cat']) ? $gameInfo['id_cat'] : 0,
			'submit_system' => !empty($gameInfo['submit_system']) ? $gameInfo['submit_system'] : '',
			'game_rating' => !empty($gameInfo['game_rating']) ? $gameInfo['game_rating'] : 0,
			'gamefile_name' => !empty($gameInfo['game_file']) ? str_replace(array(".swf", ".php", " "), array("", "", "_"), $gameInfo['game_file']) : '',
			'gamesave' => 'games_download',
			'dl_disable' => !empty($gameInfo['download_disable']) ? $gameInfo['download_disable'] : 0,
			'dl_count' => ((int)$gameInfo['download_count'] + 1),
			'report_user' => !empty($gameInfo['user_id']) ? (int)$gameInfo['user_id'] : 0,
		);
	}
	$smcFunc['db_free_result']($request);

	if (!$gameData['gamefile_name'])
		fatal_lang_error('pdl_error_db', false);

	/* Check if zip or tar file already exists and if yes download - if not zip and download */
	$url_array = array(
		$boarddir . '/' . $gameData['gamesave'] . '/' . $gameData['gamefile_name'] . '.zip',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $gameData['gamefile_name'] . '.tar',
		$boarddir . '/' . $gameData['gamesave'] . '/' . 'game_' . $gameData['gamefile_name'] . '.zip',
		$boarddir . '/' . $gameData['gamesave'] . '/' . 'game_' . $gameData['gamefile_name'] . '.tar',
	);

	/* Check to see if admin has manually denied downloading for this game - password is the file prefix */
	$deny = empty($modSettings['arcadeDownPass']) ? 'DENY_' : $modSettings['arcadeDownPass'] . '_';
	$url_skip = array(
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $gameData['gamefile_name'] . '.zip',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $gameData['gamefile_name'] . '.tar',
	);
	foreach ($url_skip as $skip)
	{
		if ((file_exists($skip)) && !allowedTo('arcade_admin'))
			fatal_lang_error('pdl_error_dl', false);
	}

	/* Check to see if downloading is denied from the games settings  */
	if ($gameData['dl_disable'] > 0  && !allowedTo('arcade_admin'))
		fatal_lang_error('pdl_error_dl', false);

	if ($context['user']['is_logged'] && $search2 !== 0 && $modSettings['pdl_DownMax'] > 0)
	{
		/* Check for daily download limit  */
		$myzone = date("e");
		date_default_timezone_set('GMT');
		list($count, $day, $year, $max, $user_info['count'], $user_info['day'], $user_info['year']) = array(1, date("z"), date("Y"), ((int)$modSettings['pdl_DownMax'] - 1), 0, false, false);

		$request = $smcFunc['db_query']('', '
			SELECT game.id_member, game.count, game.year, game.day
			FROM {db_prefix}arcade_pdl1 AS game
			WHERE ' . $search2 .'
			ORDER BY game.id_member
			LIMIT 1',
			array('search' => $gameData['id_game'],)
		);

		while ($gameInfo = $smcFunc['db_fetch_assoc']($request))
		{
			if ((int)$gameInfo['count'] > 0)
				list($user_info['count'], $user_info['day'], $user_info['year']) = array((int)$gameInfo['count'], $gameInfo['day'], $gameInfo['year']);
			else
				list($user_info['count'], $user_info['day'], $user_info['year']) = array(0, $day, $year);
		}
		$smcFunc['db_free_result']($request);
		/* Check date */
		if ($day == $user_info['day'] && $year == $user_info['year'])
		{
			if  (($user_info['count'] > $max) && !allowedTo('arcade_admin'))
				fatal_lang_error('pdl_error_max', false);
			else
				$count = (int)$user_info['count'] + 1;
		}

		/* Update user count */
		createxpdlval1($user_info['id'], $count, $year, $day, $gameData['latest_year'], $gameData['latest_day'], $gameData['permission']);
		/* reset time zone back to original setting  */
		date_default_timezone_set($myzone);
	}

	/* Update download count */
	createxpdlval2($gameData['id_game'], $gameData['gamename'], $gameData['report_day'], $gameData['report_year'], $gameData['report_user'], $gameData['report_id'], $gameData['dl_count'], $gameData['dl_disable']);
	foreach ($url_array as $url)
	{
		if (file_exists($url))
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="game_' . $gameData['gamefile_name'] . '.zip"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($url));
			ob_end_flush();
			readfile($url);
			exit;
		}
	}

	// It's not in your zip library, so let's add it...
	$directoryToZip1 = $main . '/' . $gameData['game_directory'];
	$directoryToZip2 = 'arcade/gamedata/' . $gameData['gamefile_name'];
	list($gamefiles, $gamedatafiles, $gfiles, $gdatafiles, $zipName, $ignore) = array(array(), array(), array(), array(), $boarddir . '/games_download/' . $gameData['gamefile_name'], '');
	if (is_dir($boarddir . '/' . $directoryToZip1))
	{
		// no game directory?
		if (empty($gameData['game_directory']))
		{
			if ((!empty($gameData['thumbnail_small'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['thumbnail_small']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['thumbnail_small'];
			if ((!empty($gameData['thumbnail'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['thumbnail']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['thumbnail'];
			if ((!empty($gameData['game_file'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_file']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_file'];
			// gamedata files?
			if (is_dir($boarddir . '/' . $directoryToZip2))
				$gdatafiles = scandir($boarddir . '/' . $directoryToZip2);

			$gamedatafiles = array_diff($gdatafiles, array('.', '..'));
			foreach($gamedatafiles as $key => $value)
				$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;
		}
		// inside a gamepack or unique directory?
		elseif ($gameData['game_directory'] !== $gameData['gamefile_name'])
		{
			// unique default directory?
			if ($gameData['game_directory'] == $smcFunc['strtolower']($gameData['gamefile_name']))
			{
				$gamefiles = arcade_scan_dir($boarddir . '/' . $directoryToZip1, $gameData['gamephp']);
				$ignore = $boarddir . '/' . $directoryToZip1;
			}
			// gamepack directory?
			else
			{
				if ((!empty($gameData['thumbnail_small'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['thumbnail_small']))
					$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['thumbnail_small'];
				if ((!empty($gameData['thumbnail'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['thumbnail']))
					$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['thumbnail'];
				if ((!empty($gameData['game_file'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['game_file']))
					$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['game_file'];
			}
			// gamedata files?
			if (is_dir($boarddir . '/' . $directoryToZip2))
				$gdatafiles = scandir($boarddir . '/' . $directoryToZip2);

			$gamedatafiles = array_diff($gdatafiles, array('.', '..'));
			foreach($gamedatafiles as $key => $value)
				$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;
		}
		// proper default subdirectory
		else
		{
			// gamedata files?
			if (is_dir($boarddir . '/' . $directoryToZip2))
				$gdatafiles = scandir($boarddir . '/' . $directoryToZip2);

			$gamedatafiles = array_diff($gdatafiles, array('.', '..'));
			foreach($gamedatafiles as $key => $value)
				$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;

			// the unique game folder may contain subfolders for some games
			$gamefiles = arcade_scan_dir($boarddir . '/' . $directoryToZip1, $gameData['gamephp']);
			$ignore = $boarddir . '/' . $directoryToZip1;
		}

		$tmpfile = arcade_game_down($gameData, $boarddir . '/' . $gameData['gamesave']);
		$files = array_merge_recursive($gamefiles, $gamedatafiles);
		arcade_create_zip($files, $boarddir . '/' . $gameData['gamesave'] . '/' . 'game_' . $gameData['gamefile_name'] . '.zip', $ignore, $tmpfile, $gameData['gamephp'], true);
		if (!empty($tmpfile))
			unlink($tmpfile);

		if (file_exists($boarddir . '/' . $gameData['gamesave'] . '/' . 'game_' . $gameData['gamefile_name'] . '.zip'))
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="game_' . $gameData['gamefile_name'] . '.zip"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($boarddir . '/' . $gameData['gamesave'] . '/' . 'game_' . $gameData['gamefile_name'] . '.zip'));
			ob_end_flush();
			readfile($boarddir . '/' . $gameData['gamesave'] . '/' . 'game_' . $gameData['gamefile_name'] . '.zip');
			exit;
		}

		return true;
	}
	fatal_lang_error('pdl_error_db', false);
	exit;
}

function arcade_game_down($data, $filepath)
{
	global $smcFunc;
	$word = preg_split('//', 'abcdefghijklmnopqrstuvwxyz_1234567890', -1);
	shuffle($word);
	$file = implode('', array_slice($word, 0, 16));
	$stype = array('auto', 'v1game', 'pnflash', 'silver', 'custom_game', 'ibp', 'ibp3', 'ibp32');
	$savetype = !empty($data['submit_system']) && in_array($data['submit_system'], $stype) ? $data['submit_system'] : 'v1game';

	$gameinfo = array(
		'active' => $data['enabled'],
		'bgcolor' => !empty($data['extra_data']['background_color']) ? arcade_hex_to_color($data['extra_data']['background_color']) : '',
		'gcat' => $data['id_cat'],
		'gheight' => !empty($data['extra_data']['height']) ? $data['extra_data']['height'] : 500,
		'gwidth' => !empty($data['extra_data']['width']) ? $data['extra_data']['width'] : 500,
		'gkeys' => str_replace(array('"', "'"), array('&quot;', '&apos;'), $data['help']),
		'gname' => $data['internal_name'],
		'gtitle' => $data['gamename'],
		'gwords' => str_replace(array('"', "'"), array('&quot;', '&apos;'), $data['description']),
		'object' => str_replace(array('"', "'"), array('&quot;', '&apos;'), $data['description']),
		'snggame' => $data['score_type'],
		'savetype' => $savetype,
		'date' => gmdate('D, d M Y H:i:s \G\M\T', time()),
		'thumbnail' => $data['thumbnail'],
		'thumbnail_small' => $data['thumbnail_small'],
		'file' => $data['game_file'],
		'flash_version' => !empty($data['extra_data']['flash_version']) ? $data['extra_data']['flash_version'] : 0,
		'score_type' => $data['score_type'],
	);

	if (in_array($savetype, array('ibp', 'ibp3', 'ibp32')))
	{
		$infofile = '<?php
/*--------------------------------------------------*/
/* File Created by SMF Arcade 2.55					*/
/* File Generated: ' . $gameinfo['date'] . '	*/
/*--------------------------------------------------*/

$config = array(
	\'active\'			=> \'' . $gameinfo['active'] . '\',
	\'bgcolor\'			=> \'' . $gameinfo['bgcolor'] . '\',
	\'gcat\'				=> \'' . $gameinfo['gcat'] . '\',
	\'gheight\'			=> \'' . $gameinfo['gheight'] . '\',
	\'gwidth\'			=> \'' . $gameinfo['gwidth'] . '\',
	\'gkeys\'				=> \'' . $gameinfo['gkeys'] . '\',
	\'gname\'				=> \'' . $gameinfo['gname'] . '\',
	\'gtitle\'			=> \'' . $gameinfo['gtitle'] . '\',
	\'gwords\'			=> \'' . $gameinfo['gwords'] . '\',
	\'object\'			=> \'' . $gameinfo['object'] . '\',
	\'snggame\'			=> \'' . $gameinfo['snggame'] . '\',
	\'savetype\'			=> \'' . $gameinfo['savetype'] . '\',
);
?>'. PHP_EOL . PHP_EOL;
		$ext = '.php';
	}
	else
	{
		$infofile ='<!-- 	File Created by SMF Arcade 2.55					-->
<!-- 	File Generated: ' . $gameinfo['date'] . '	-->
<game-info>
	<id>' . $gameinfo['gname'] . '</id>
	<name>' . $gameinfo['gtitle'] . '</name>
	<description>' . $gameinfo['gwords'] . '</description>
	<help>' . $gameinfo['gkeys'] . '</help>
	<thumbnail>' . $gameinfo['thumbnail'] . '</thumbnail>
	<thumbnail-small>' . $gameinfo['thumbnail_small'] . '</thumbnail-small>
	<file>' . $gameinfo['file'] . '</file>
	<scoring>' . $gameinfo['score_type'] . '</scoring>
	<submit>' . $gameinfo['savetype'] . '</submit>
	<flash>
		<version>' . $gameinfo['flash_version'] . '</version>
		<width>' . $gameinfo['gwidth'] . '</width>
		<height>' . $gameinfo['gheight'] . '</height>
		<bgcolor>' . $gameinfo['bgcolor'] . '</bgcolor>
	</flash>
</game-info>';
		$ext = '.xml';
	}

	$fp = fopen($filepath . '/' . $file . $ext, 'w');
	fwrite($fp, $infofile, strlen($infofile));
	fclose ($fp);
	return $filepath . '/' . $file . $ext;
}

function arcade_hex_to_color($hex)
{
	return sprintf("%x", ($hex[0] << 16) + ($hex[1] << 8) + $hex[2]);
}

function arcade_scan_dir($dir, $gamephp)
{
	$arrfiles = array();
	if (is_dir($dir))
	{
		if ($handle = opendir($dir))
		{
			chdir($dir);
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != ".." && substr($file, -1) !== '~' && $file !== $gamephp)
				{
					if (is_dir($file))
					{
						$arr = arcade_scan_dir($file, 'ignorefile.php');
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

function arcade_create_zip($files = array(), $destination = '', $ignore = '', $tmpfile = '', $phpfile = '', $overwrite = false)
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
			elseif(!empty($ignore))
				$newfile = str_replace($ignore . '/', '', $file);
			else
				$newfile = basename($file);
			$zip->addFile($file, $newfile);
		}


		$zip->close();
		if ($zip->open($destination) === TRUE && !empty($tmpfile))
		{
			if (substr($tmpfile, -4) == '.php')
				$zip->addfile($tmpfile, $phpfile);
			else
				$zip->addfile($tmpfile, 'game-info.xml');
			$zip->close();
		}
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

// Check if the column exists
function checkFieldPDL($tableName, $columnName)
{
	if (check_table_existsPDL($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		if (in_array($columnName, $check))
			return true;
	}

	return false;
}

// Returns amount of columns in a table
function checkTablePDL($tableName)
{
	global $smcFunc;

	if (check_table_existsPDL($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		return !empty($check) ? count($check) : false;
	}
	return false;
}

// Check if table exists
function check_table_existsPDL($table, $check = false)
{
	global $db_prefix, $smcFunc;

	if ($smcFunc['db_list_tables'](false, $db_prefix . $table))
		$check = true;

	return $check;
}

// Update arcade_pdl1 values
function createxpdlval1($userid, $count, $year, $day, $latest_year, $latest_day, $permission)
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

// Update arcade_pdl2 values
function createxpdlval2($gameid, $gamename, $repday, $repyear, $repuser, $repid, $dl_count, $dl_disable)
{
	global $smcFunc;
	$game_name = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $gamename);

	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl2
		WHERE pdl_gameid = {int:gameid}',
		array('gameid' => $gameid,
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
?>