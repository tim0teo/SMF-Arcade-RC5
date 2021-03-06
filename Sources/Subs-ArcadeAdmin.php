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
	int createGame()
		- ???

	boolean deleteGame()
		- ???

	array installGames()
		- ???

	array uninstallGames()
		- ???

	string getGameName()
		- ???

	string getInternalName()
		- ???

	array isGame()
		- ???

	void moveGames()
		- ???

	array readGameInfo()
		- ???

	boolean updateCategoryStats()
		- ???

	void updateGameCache()
		- ???

	array arcadeGetGroups()
		- ???

	void postArcadeGames()
		- ???

	void copyArcadeDirectory()
		- ???

	void deleteArcadeArchives()
		- ???

	void ArcadeAdminScanDir()
		- ???

	void ArcadeAdminCategoryDropdown()
		- ???

	void return_bytes()
		- ???

	void arcadeUnzip()
		- ???

	void arcadeCreateDirs()
		- ???

*/

function list_getNumGamesInstalled($filter)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_files AS f
			INNER JOIN {db_prefix}arcade_games AS g ON (g.id_game = f.id_game)
		WHERE (status = 1 OR status = 2)' . ($filter == 'disabled' || $filter == 'enabled' ? '
			AND g.enabled = {int:enabled}' : ''),
		array(
			'enabled' => $filter == 'disabled' ? 0 : 1,
		)
	);

	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $count;
}

function list_getGamesInstalled($start, $items_per_page, $sort, $filter)
{
	global $smcFunc, $scripturl, $context, $txt;

	$request = $smcFunc['db_query']('', '
		SELECT f.id_file, g.game_name, g.internal_name, f.status, g.id_game, cat.id_cat, cat.cat_name
		FROM {db_prefix}arcade_files AS f
			INNER JOIN {db_prefix}arcade_games AS g ON (g.id_game = f.id_game)
			LEFT JOIN {db_prefix}arcade_categories AS cat ON (cat.id_cat = g.id_cat)
		WHERE (f.status = 1 OR f.status = 2)' . ($filter == 'disabled' || $filter == 'enabled' ? '
			AND g.enabled = {int:enabled}' : '') . '
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:games_per_page}',
		array(
			'start' => $start,
			'games_per_page' => $items_per_page,
			'sort' => $sort,
			'enabled' => $filter == 'disabled' ? 0 : 1,
		)
	);

	$return = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$return[] = array(
			'id' => $row['id_game'],
			'id_file' => $row['id_file'],
			'name' => $row['game_name'],
			'href' => $scripturl . '?action=admin;area=managegames;sa=edit;game=' . $row['id_game'],
			'category' => array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name'],
			),
			'error' => $row['status'] != 1 ? $txt['arcade_missing_files'] : false,
		);
	$smcFunc['db_free_result']($request);

	return $return;
}

function list_getNumGamesInstall()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_files AS f
		WHERE status = 10',
		array(
		)
	);

	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $count;
}

function list_getGamesInstall($start, $items_per_page, $sort)
{
	global $smcFunc, $scripturl, $context, $txt;

	$request = $smcFunc['db_query']('', '
		SELECT f.id_file, f.game_name, f.status
		FROM {db_prefix}arcade_files AS f
		WHERE status = 10
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:games_per_page}',
		array(
			'start' => $start,
			'games_per_page' => $items_per_page,
			'sort' => $sort,
		)
	);

	$return = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$return[] = array(
			'id_file' => $row['id_file'],
			'name' => $row['game_name'],
			'href' => $scripturl . '?action=admin;area=managegames;sa=install2;file=' . $row['id_file'],
		);
	$smcFunc['db_free_result']($request);

	return $return;
}

// Creates new game. Returns false on error and id of game on success
function createGame($game)
{
	global $scripturl, $txt, $db_prefix, $user_info, $smcFunc, $modSettings;

	$smcFunc['db_insert']('ignore',
		'{db_prefix}arcade_games',
		array(
			'id_cat' => 'int',
			'internal_name' => 'string',
			'game_name' => 'string',
			'submit_system' => 'string',
			'description' => 'string',
			'help' => 'string',
			'enabled' => 'int',
			'num_rates' => 'int',
			'num_plays' => 'int',
			'game_file' => 'string',
			'game_directory' => 'string',
			'extra_data' => 'string',
		),
		array(
			0,
			$game['internal_name'],
			$game['name'],
			$game['submit_system'],
			'',
			'',
			1,
			0,
			0,
			$game['game_file'],
			$game['game_directory'],
			'',
		),
		array()
	);

	$id_game = $smcFunc['db_insert_id']('{db_prefix}arcade_games', 'id_game');

	if (empty($id_game))
		return false;


	// Post message with game info if enabled
	$modSettings['arcadeEnablePosting'] = !empty($modSettings['arcadeEnablePosting']) ? $modSettings['arcadeEnablePosting'] : false;
	if ((($modSettings['arcadeEnablePosting']) == true) && (($modSettings['gamesBoard']) == true))
	{
		$gameid = $id_game;
		postArcadeGames($game, $gameid);
	}

	unset($game['internal_name'], $game['name'], $game['submit_system'], $game['game_file'], $game['game_directory']);

	// Update does the rest...
	updateGame($id_game, $game);

	logAction('arcade_install_game', array('game' => $id_game));

	return $id_game;
}

function deleteGame($id, $remove_files)
{
	global $scripturl, $txt, $db_prefix, $user_info, $smcFunc, $modSettings;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_favorite
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_rates
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);

	if ($remove_files)
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_files
			WHERE id_game = {int:game}',
			array(
				'game' => $id,
			)
		);
	else
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_files
			SET id_game = 0, status = 10
			WHERE id_game = {int:game}',
			array(
				'game' => $id,
			)
		);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_games
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl2
		WHERE pdl_gameid = {int:game}',
		array(
			'game' => $id,
		)
	);


	logAction('arcade_delete_game', array('game' => $id));

	return true;
}

// Install games by game cache ids
function installGames($games, $move_games = false)
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $sourcedir, $smcFunc, $boarddir;

	loadClassFile('Class-Package.php');

	// SWF Reader will be needed
	require_once($sourcedir . '/SWFReader.php');
	list($swf, $masterGameinfo, $status, $directories) = array(new SWFReader(), array(), array(), array());

	$request = $smcFunc['db_query']('', '
		SELECT game_directory
		FROM {db_prefix}arcade_games
		WHERE id_game > 0',
		array(
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$directories[] = !empty($row['game_directory']) ? $row['game_directory'] : '';
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('', '
		SELECT f.id_file, f.game_name, f.status, f.game_file, f.game_directory
		FROM {db_prefix}arcade_files AS f
		WHERE id_file IN ({array_int:games})
			AND f.status = 10',
		array(
			'games' => $games,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		list($errors, $failed, $moveFail, $exists) = array(array(), true, false, false);

		// sanitize some stuff
		$directory = $modSettings['gamesDirectory'] . (!empty($row['game_directory']) ? '/' . $row['game_directory'] : '');
		$directory = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $directory)))));
		$internal_name = getInternalName($row['game_file'], $directory);

		// Search for thumbnails
		chdir($directory);

		if (basename(dirname($directory)) !== basename($modSettings['gamesDirectory']) && file_exists(dirname($directory) . '/master-info.xml'))
		{
			$masterGameinfo = array();
			$masterGameinfo = readGameInfo(dirname($directory) . '/master-info.xml');

			if (!isset($gameinfo['submit']))
				unset($gameinfo);
		}

		if (file_exists($directory . '/game-info.xml'))
		{
			$gameinfo = readGameInfo($directory . '/game-info.xml');
			if (!isset($gameinfo['id']))
				unset($gameinfo);
		}
		elseif (file_exists($directory . '/' . $internal_name . '-game-info.xml'))
		{
			$gameinfo = readGameInfo($directory . '/' . $internal_name . '-game-info.xml');

			if (!isset($gameinfo['id']))
				unset($gameinfo);
		}

		foreach ($masterGameinfo as $key => $masterSetting)
			if (!empty($masterGameinfo[$key]))
				$gameinfo[$key] = $masterGameinfo[$key];

		$thumbnail = glob($internal_name . '1.{png,gif,jpg}', GLOB_BRACE);
		if (empty($thumbnail))
			$thumbnail = glob($internal_name . '.{png,gif,jpg}', GLOB_BRACE);

		$thumbnailSmall = glob($internal_name . '2.{png,gif,jpg}', GLOB_BRACE);
		$thumbnail = empty($thumbnail) ? '' : $thumbnail;
		$tumbnailSmall = empty($thumbnailSmall) ? '' : $thumbnailSmall;
		$gameinfo['thumbnail'] = empty($gameinfo['thumbnail']) ? '' : $gameinfo['thumbnail'];
		$gameinfo['thumbnail-small'] = empty($gameinfo['thumbnail-small']) ? '' : $gameinfo['thumbnail-small'];
		$gameinfo['help'] = empty($gameinfo['help']) ? '' : $gameinfo['help'];

		$game = array(
			'id_file' => $row['id_file'],
			'name' => $row['game_name'],
			'directory' => !empty($row['game_directory']) ? preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $row['game_directory']))))) : '',
			'file' => $row['game_file'],
			'internal_name' => str_replace(array('/', '\\'), array('', ''), trim($internal_name, '.')),
			'thumbnail' => !empty($thumbnail[0]) ? $thumbnail[0] : !empty($gameinfo['thumbnail']) ? $gameinfo['thumbnail'] : '',
			'thumbnail_small' => !empty($thumbnailSmall[0]) ? $thumbnailSmall[0] : !empty($gameinfo['thumbnail-small']) ? $gameinfo['thumbnail-small'] : '',
			'extra_data' => array(
				'width' => !empty($gameinfo['flash']['width']) && is_numeric($gameinfo['flash']['width']) ? $gameinfo['flash']['width'] : '',
				'height' => !empty($gameinfo['flash']['height']) && is_numeric($gameinfo['flash']['height']) ? $gameinfo['flash']['height'] : '',
				'flash_version' => !empty($gameinfo['flash']['version']) && is_numeric($gameinfo['flash']['version']) ? $gameinfo['flash']['version'] : 0,
				'type' => !empty($gameinfo['flash']['type']) ? ArcadeSpecialChars($gameinfo['flash']['type'], 'name') : '',
				'background_color' => !empty($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6 ? array(
					hexdec(substr($gameinfo['flash']['bgcolor'], 0, 2)),
					hexdec(substr($gameinfo['flash']['bgcolor'], 2, 2)),
					hexdec(substr($gameinfo['flash']['bgcolor'], 4, 2))
				) : array(),
			),
			'help' => isset($gameinfo['help']) ? $gameinfo['help'] : '',
			'description' => isset($gameinfo['description']) ? $gameinfo['description'] : '',
		);

		unset($thumbnail, $thumbnailSmall);

		// Get information from flash
		if (substr($row['game_file'], -3) == 'swf')
		{
			if (isset($gameinfo['flash']))
			{
				if (!empty($gameinfo['flash']['width']) && is_numeric($gameinfo['flash']['width']))
					$game['extra_data']['width'] = (int)$gameinfo['flash']['width'];
				if (!empty($gameinfo['flash']['height']) && is_numeric($gameinfo['flash']['height']))
					$game['extra_data']['height'] = (int)$gameinfo['flash']['height'];
				if (!empty($gameinfo['flash']['version']) && is_numeric($gameinfo['flash']['version']))
					$game['extra_data']['flash_version'] = (int)$gameinfo['flash']['version'];
				if (!empty($gameinfo['flash']['type']))
					$game['extra_data']['type'] = ArcadeSpecialChars($gameinfo['flash']['type'], 'name');
				if (!empty($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6)
				{
					$game['extra_data']['background_color'] = array(
						hexdec(substr($gameinfo['flash']['bgcolor'], 0, 2)),
						hexdec(substr($gameinfo['flash']['bgcolor'], 2, 2)),
						hexdec(substr($gameinfo['flash']['bgcolor'], 4, 2))
					);
				}
			}

			// Do we need to detect at least something?
			if (!isset($game['extra_data']['width']) || !isset($game['extra_data']['height']) || !isset($game['extra_data']['version']) || !isset($game['extra_data']['bgcolor']))
			{
				$swf->open($directory . '/' . $row['game_file']);

				// Add missing values
				if (!$swf->error)
				{
					$game['extra_data'] += array(
						'width' => $swf->header['width'],
						'height' => $swf->header['height'],
						'flash_version' => $swf->header['version'],
						'background_color' => $swf->header['background'],
						'type' => !empty($gameinfo['flash']['type']) ? ArcadeSpecialChars($gameinfo['flash']['type'], 'name') : '',
					);
				}

				$swf->close();
			}
		}

		// Detect submit system
		if (empty($row['submit_system']))
		{
			if (isset($gameinfo['submit']))
				$row['submit_system'] = $gameinfo['submit'];
			elseif (substr($row['game_file'], -5) == '.html')
				$row['submit_system'] = 'html5';
			elseif (substr($row['game_file'], -3) == 'php')
				$row['submit_system'] = 'custom_game';
			elseif (substr($row['game_file'], -3) == 'xap')
				$row['submit_system'] = 'silver';
			elseif (file_exists($boarddir . '/arcade/gamedata/' . $internal_name . '/v32game.txt'))
				$row['submit_system'] = 'ibp32';
			elseif (file_exists($boarddir . '/arcade/gamedata/' . $internal_name . '/v3game.txt'))
				$row['submit_system'] = 'ibp3';
			elseif (file_exists($directory . '/' . $internal_name . '.ini'))
				$row['submit_system'] = 'pnflash';
			elseif (file_exists($directory . '/' . $internal_name . '.php'))
			{
				$file = file_get_contents($directory . '/' . $internal_name . '.php');

				if (strpos($file, '$config = array(') !== false)
					$row['submit_system'] = 'ibp';
				else
					$row['submit_system'] = 'v1game';

				unset($file);
			}
			else
				$row['submit_system'] = 'v1game';
		}
		$game['submit_system'] = $row['submit_system'];
		$game['score_type'] = isset($gameinfo) && isset($gameinfo['scoring']) ? (int) $gameinfo['scoring'] : 0;

		if (!empty($gameinfo['thumbnail']))
			$game['thumbnail'] = $gameinfo['thumbnail'];
		if (!empty($gameinfo['thumbnail-small']))
			$game['thumbnail_small'] = $gameinfo['thumbnail-small'];


		$game_directory = $game['directory'];

		// Move files if necessary
		if ($game_directory != $internal_name && $move_games)
		{
			if (!is_dir($modSettings['gamesDirectory'] . '/' . $internal_name) && !mkdir($modSettings['gamesDirectory'] . '/' . $internal_name, 0755))
			{
				$moveFail = true;
				$game['error'] = array('directory_make_failed', array($modSettings['gamesDirectory'] . '/' . $internal_name));

				continue;
			}

			if (!is_writable($modSettings['gamesDirectory'] . '/' . $internal_name))
				@chmod($modSettings['gamesDirectory'] . '/' . $internal_name, 0755);

			$renames = array(
				$directory . '/' . $game['file'] => $modSettings['gamesDirectory'] . '/' . $internal_name . '/' . $game['file'],
			);

			if (!empty($game['thumbnail']))
				$renames[$directory . '/' . $game['thumbnail']] = $modSettings['gamesDirectory'] . '/' . $internal_name . '/' . $game['thumbnail'];

			if (!empty($game['thumbnail_small']))
				$renames[$directory . '/' . $game['thumbnail_small']] = $modSettings['gamesDirectory'] . '/' . $internal_name . '/' . $game['thumbnail_small'];

			foreach ($renames as $from => $to)
			{
				if (!file_exists($from) && file_exists($to))
					continue;

				if (!rename($from, $to))
				{
					$moveFail = true;

					$game['error'] = array('file_move_failed', array($from, $to));

					continue;
				}
			}

			if (!$moveFail)
			{
				$game_directory = $internal_name;
				$directory = $modSettings['gamesDirectory'] . '/' . $game_directory;
			}
		}


		if(file_exists($modSettings['gamesDirectory'] . '/' .$game_directory . '/game-info.xml'))
		{
			$gameinfo = readGameInfo($modSettings['gamesDirectory'] . '/' .$game_directory . '/game-info.xml');
			$game['help'] = isset($gameinfo['help']) ? $gameinfo['help'] : '';
			$game['description'] = isset($gameinfo['description']) ? $gameinfo['description'] : '';
			if (isset($gameinfo['flash']))
			{
				if (!empty($gameinfo['flash']['width']) && is_numeric($gameinfo['flash']['width']))
					$game['extra_data']['width'] = (int)$gameinfo['flash']['width'];
				if (!empty($gameinfo['flash']['height']) && is_numeric($gameinfo['flash']['height']))
					$game['extra_data']['height'] = (int)$gameinfo['flash']['height'];
				if (!empty($gameinfo['flash']['version']) && is_numeric($gameinfo['flash']['version']))
					$game['extra_data']['flash_version'] = (int)$gameinfo['flash']['version'];
				if (!empty($gameinfo['flash']['type']))
					$game['extra_data']['type'] =  ArcadeSpecialChars($gameinfo['flash']['type'], 'name');
				if (!empty($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6)
				{
					$game['extra_data']['background_color'] = array(
						hexdec(substr($gameinfo['flash']['bgcolor'], 0, 2)),
						hexdec(substr($gameinfo['flash']['bgcolor'], 2, 2)),
						hexdec(substr($gameinfo['flash']['bgcolor'], 4, 2))
					);
				}
			}
		}

		// override some settings if the php configuration file is available
		if(file_exists($modSettings['gamesDirectory'] . '/' . $game_directory . '/' . $game['internal_name'] . '.php'))
		{
			@require_once($modSettings['gamesDirectory'] . '/' . $game_directory . '/' . $game['internal_name'] . '.php');
			$imageArray = array('gif', 'png', 'jpg');
			$game_info = array('gtitle', 'gwords', 'gkeys');
			$arcade_info = array('name', 'description', 'help');
			$x = 0;
			foreach ($game_info as $info)
			{
				if (!empty($config[$info]))
				{
					$config[$info] = un_htmlspecialchars($config[$info]);
					$game[$arcade_info[$x]] = $config[$info];
				}
				$x++;
			}
			$gameinfo['help'] = !empty($game['help']) ? $game['help'] : '';
			if (!empty($config['gwidth']) && is_numeric($config['gwidth']))
				$game['extra_data']['width'] = (int)$config['gwidth'];
			if (!empty($config['gheight']) && is_numeric($config['gheight']))
				$game['extra_data']['height'] = (int)$config['gheight'];
			if (!empty($config['gtype']))
				$game['extra_data']['type'] = ArcadeSpecialChars($config['gtype'], 'name');
			if (!empty($config['bgcolor']) && strlen($config['bgcolor']) == 6)
			{
				$game['extra_data']['background_color'] = array(
					hexdec(substr($config['bgcolor'], 0, 2)),
					hexdec(substr($config['bgcolor'], 2, 2)),
					hexdec(substr($config['bgcolor'], 4, 2))
				);
			}

			foreach ($imageArray as $type)
			{
				if (empty($game['thumbnail']))
					if (file_exists($modSettings['gamesDirectory'] . '/' . $game_directory . '/' . $game['internal_name'] . '1.' . $type))
						$game['thumbnail'] = $game['internal_name'] . '1.' . $type;

				if (empty($game['thumbnail_small']))
					if (file_exists($modSettings['gamesDirectory'] . '/' . $game_directory . '/' . $game['internal_name'] . '2.' . $type))
						$game['thumbnail_small'] = $game['internal_name'] . '2.' . $type;
			}

			$swf->open($modSettings['gamesDirectory'] . '/' . $game_directory . '/' . $game['file']);

			// Add missing values
			if (!$swf->error)
			{
				$game['extra_data']['flash_version'] = $swf->header['version'];
				$game['extra_data']['background_color'] = empty($game['extra_data']['background_color']) ? $swf->header['background'] : $game['extra_data']['background_color'];
				$game['extra_data']['width'] = empty($game['extra_data']['width']) ? $swf->header['width'] : $game['extra_data']['width'];
				$game['extra_data']['height'] = empty($game['extra_data']['height']) ? $swf->header['height'] : $game['extra_data']['height'];
				$game['extra_data']['type'] = empty($game['extra_data']['type']) ? '' : $game['extra_data']['type'];
			}

			$swf->close();

		}

		// Final install data
		$gameOptions = array(
			'internal_name' => $game['internal_name'],
			'name' => $game['name'],
			'description' => !empty($game['description']) ? $game['description'] : '',
			'thumbnail' => $game['thumbnail'],
			'thumbnail_small' => $game['thumbnail_small'],
			'help' => (!empty($game['help']) ? $game['help'] : !empty($gameinfo['help']) ? $gameinfo['help'] : ''),
			'game_file' => $game['file'],
			'game_directory' => $game_directory,
			'submit_system' => $game['submit_system'],
			'score_type' => $game['score_type'],
			'extra_data' => $game['extra_data'],
		);

		$success = false;
		if (!isset($game['error']) && $id_game = createGame($gameOptions))
			$success = true;
		elseif (!in_array($game_directory, $directories))
		{
			$files = array_unique(
				array(
					$game['file'],
					!empty($game['thumbnail']) ? $game['thumbnail'] : '',
					!empty($game['thumbnail_small']) ? $game['thumbnail_small'] : '',
					mb_substr($game['file'], 0, -4) . '.php',
					mb_substr($game['file'], 0, -4) . '-game-info.xml',
					mb_substr($game['file'], 0, -4) . '.xap',
					mb_substr($game['file'], 0, -5) . '.html',
					mb_substr($game['file'], 0, -4) . '.ini',
					mb_substr($game['file'], 0, -4) . '.gif',
					mb_substr($game['file'], 0, -4) . '.png',
					mb_substr($game['file'], 0, -4) . '.jpg',
				)
			);
			$dest = rtrim($modSettings['gamesDirectory'] . '/' . $game_directory, '/');
			foreach ($files as $key => $data)
			{
				if ((!empty($files[$key])) && file_exists($dest . '/' . $files[$key]))
					unlink($dest . '/' . $files[$key]);
			}

			if ((!empty($dest)) && $dest !== $modSettings['gamesDirectory'] && $dest !== $boarddir)
			{
				if (dirname($dest) !== $modSettings['gamesDirectory'])
				{
					$gfiles = ArcadeAdminScanDir($dest, '');
					foreach ($gfiles as $delete)
						deleteArcadeArchives($delete);
				}
				$gfiles = ArcadeAdminScanDir($dest, '');
				if (empty($gfiles))
					rmdir($dest);
				elseif ((count($gfiles) == 1) && $gfiles[0] == 'master-info.xml')
				{
					unlink($dest . '/master-info.xml');
					rmdir($dest);
				}
			}

			if (is_dir($boarddir . '/arcade/gamedata/' . $game['internal_name']))
			{
				$gdfiles = ArcadeAdminScanDir($boarddir . '/arcade/gamedata/' . $game['internal_name'], '');
				foreach ($gdfiles as $file)
					unlink($file);

				deleteArcadeArchives($boarddir . '/arcade/gamedata/' . $game['internal_name']);
			}

			$game['error'] = array('arcade_install_general_fail', array('path', $game_directory));
		}
		else
		{
			$exists = true;
			$game['error'] = array('arcade_install_exists_fail', array('path', $game_directory));
		}

		if (empty($exists))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_files
				SET id_game = {int:game}, status = {int:status}, game_directory = {string:directory}
				WHERE id_file = {int:file}',
				array(
					'game' => empty($success) ? 0 : $id_game,
					'status' => empty($success) ? 10 : 1,
					'file' => $game['id_file'],
					'directory' => $game_directory,
				)
			);
		}

		$status[] = array(
			'id' => $id_game,
			'name' => $game['name'],
			'error' => isset($game['error']) ? $game['error'] : false,
		);
	}
	$smcFunc['db_free_result']($request);

	return $status;
}

function unpackGames($games, $move_games = false)
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $sourcedir, $smcFunc, $boarddir;

	if (!is_writable($modSettings['gamesDirectory']) && !chmod($modSettings['gamesDirectory'], 0755))
		fatal_lang_error('arcade_not_writable', false, array($modSettings['gamesDirectory']));

	require_once($sourcedir . '/Subs-Package.php');
	$smfVersion = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

	$request = $smcFunc['db_query']('', '
		SELECT f.id_file, f.game_file, f.game_directory
		FROM {db_prefix}arcade_files AS f
		WHERE id_file IN ({array_int:games})
			AND (f.status = 10)',
		array(
			'games' => $games,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$from = $modSettings['gamesDirectory'] . '/' . (!empty($row['game_directory']) ? $row['game_directory'] . '/' : '') . $row['game_file'];
		$target = substr($row['game_file'], 0, strpos($row['game_file'], '.'));
		$target = strlen(mb_substr($target, 6)) > 0 && mb_substr($target, 0, 5) == 'game_' ? mb_substr($target, 5) : $target;
		$target = strlen(mb_substr($target, 7)) > 0 && mb_substr($target, 0, 6) == 'html5_' ? mb_substr($target, 6) : $target;
		$targetb = $target;

		$i = 1;
		if (file_exists($modSettings['gamesDirectory'] . '/' . $target))
		{
			// if the directory is empty we can still use it otherwise abort the installation
			$dir_iterator = new RecursiveDirectoryIterator($modSettings['gamesDirectory'] . '/' . $target);
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
			$dirFiles = array();

			foreach ($iterator as $file)
				if (substr($file, -1) !== '.' && substr($file, -2) != '..')
					$dirFiles[] = $file;

			if (count($dirFiles) > 0)
				fatal_lang_error('arcade_directory_make_exists', false, $target);
		}

		if (substr($row['game_file'] , -3) == '.gz')
		{
			$buffer_size = 4096; // read 4kb at a time
			$output = substr($row['game_file'], 0, -3);
			$output = strlen(mb_substr($output, 6)) > 0 && mb_substr($output, 0, 5) == 'game_' ? mb_substr($output, 5) : $output;
			$output = strlen(mb_substr($output, 7)) > 0 && mb_substr($output, 0, 6) == 'html5_' ? mb_substr($output, 6) : $output;
			$file = gzopen($modSettings['gamesDirectory'] . '/' . $row['game_file'], 'rb');
			$out_file = fopen($modSettings['gamesDirectory'] . '/' . $output, 'wb');

			while(!gzeof($file))
				@fwrite($out_file, gzread($file, $buffer_size));

			fclose($out_file);
			gzclose($file);
			if (!@file_exists($modSettings['gamesDirectory'] . '/' . $output))
				fatal_lang_error('arcade_file_non_read', false);

			@unlink($modSettings['gamesDirectory'] . $row['game_file']);
			$row['game_file'] = $output;
		}

		if (substr($row['game_file'] , -3) == 'zip')
		{
			$target = strlen(mb_substr($target, 6)) > 0 && mb_substr($target, 0, 5) == 'game_' ? mb_substr($target, 5) : $target;
			$target = strlen(mb_substr($target, 7)) > 0 && mb_substr($target, 0, 6) == 'html5_' ? mb_substr($target, 6) : $target;
			if ($smfVersion === 'v2.1')
				$files = arcadeUnzip($from, $modSettings['gamesDirectory'] . '/' . $target . '/', true, false);
			else
				$files = read_tgz_file($from, $modSettings['gamesDirectory'] . '/' . $target);

			$data = gameCacheInsertGames(getAvailableGames($target, 'unpack'), true);
		}

		if(substr($row['game_file'] , -3) == 'tar')
		{
			require_once($sourcedir . '/Tar.php');
			$path = $modSettings['gamesDirectory'] . '/';
			$tar = new Archive_Tar($path . $row['game_file']);
			$data = $tar->listContent();

			if ($data == false)
				fatal_lang_error('arcade_file_non_read', false);
			else
			{
				$name = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2",  $row['game_file']);
				$folder = $modSettings['gamesDirectory'] . '/' . trim($name);

				if(!file_exists($folder))
					@mkdir($folder, 0755);

				$tar = new Archive_Tar($path . $row['game_file']);
				$tar->extract($folder);
			}
		}

		if (unlink($from))
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_files
				WHERE id_file = {int:file}',
				array(
					'file' => $row['id_file'],
				)
			);
	}
	$smcFunc['db_free_result']($request);

	return true;
}

function uninstallGames($games, $delete_files = false)
{
	global $smcFunc, $modSettings, $sourcedir, $boarddir;

	require_once($sourcedir . '/Subs-Package.php');
	require_once($sourcedir . '/RemoveTopic.php');

	$request = $smcFunc['db_query']('', '
		SELECT id_game, internal_name, game_name, game_file, thumbnail, thumbnail_small, game_directory, id_topic
		FROM {db_prefix}arcade_games
		WHERE id_game IN({array_int:games})',
		array(
			'games' => $games
		)
	);

	list($status, $topics) = array(array(), array());

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$directory = $modSettings['gamesDirectory'] . (!empty($row['game_directory']) ? '/' . $row['game_directory'] : '');
		$directory = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $directory)))));
		$gamedir = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $row['game_directory'])))));
		$internal_name = str_replace(array('/', '\\'), array('', ''), trim($row['internal_name'], '.'));

		if (!empty($row['id_topic']))
			$topics[] = $row['id_topic'];

		$files = array_unique(
			array(
				$row['game_file'],
				$row['thumbnail'],
				$row['thumbnail_small'],
				mb_substr($row['game_file'], 0, -4) . '.php',
				mb_substr($row['game_file'], 0, -4) . '-game-info.xml',
				mb_substr($row['game_file'], 0, -5) . '.html',
				mb_substr($row['game_file'], 0, -4) . '.xap',
				mb_substr($row['game_file'], 0, -4) . '.ini',
			)
		);
		if ($delete_files)
		{
			if (basename(dirname($directory)) !== basename($modSettings['gamesDirectory']) && basename($directory) !== basename($modSettings['gamesDirectory']))
			{
				if (is_dir($directory))
				{
					$files = ArcadeAdminScanDir($directory, '');
					foreach ($files as $file)
						unlink($file);
					rmdir($directory);
				}
				$check = ArcadeAdminScanDir(dirname($directory), '');
				if (empty($check))
					rmdir(dirname($directory));
				elseif (count($check) == 1 && $check[0] == dirname($directory) . '/master-info.xml')
				{
					unlink(dirname($directory) . '/master-info.xml');
					rmdir(dirname($directory));
				}

			}
			elseif (basename($gamedir) == $internal_name && $internal_name !== basename($modSettings['gamesDirectory']))
			{
				if (is_dir($directory) && basename($directory) !== $modSettings['gamesDirectory'])
				{
					$files = ArcadeAdminScanDir($directory, '');
					$dirs = array($directory);
					foreach ($files as $file)
					{
						unlink($file);
						if(!in_array(dirname($file), $dirs))
							$dirs[] = dirname($file);
					}
					foreach ($dirs as $dir)
					{
						if (is_dir($dir) && rtrim($dir, '/') !== rtrim($directory, '/'))
							rmdir($dir);
					}
					if (is_dir($directory))
						rmdir($directory);
				}
			}
			else
			{
				foreach ($files as $f)
				{
					if ((!empty($f)) && file_exists($directory . '/' . $f))
						unlink($directory . '/' . $f);
					/*
					if (!empty($row['game_directory']) && file_exists(dirname($modSettings['gamesDirectory'] . '/' . $row['game_directory']) . '/' . $f))
						unlink(dirname($modSettings['gamesDirectory'] . '/' . $row['game_directory']) . '/' . $f);
					*/
				}

				if (basename($directory) !== basename($modSettings['gamesDirectory']))
				{
					$check = ArcadeAdminScanDir($directory, '');
					if (empty($check))
						rmdir($directory);
					elseif (count($check) == 1 && $check[0] == $directory . '/master-info.xml')
					{
						unlink($directory . '/master-info.xml');
						rmdir($directory);
					}
				}
			}

			if (is_dir($boarddir . '/arcade/gamedata/' . $internal_name) || file_exists($boarddir . '/arcade/gamedata/' . $internal_name))
			{
				$gdfiles = ArcadeAdminScanDir($boarddir . '/arcade/gamedata/' . $internal_name, '');
				foreach ($gdfiles as $file)
					unlink($file);
				rmdir($boarddir . '/arcade/gamedata/' . $internal_name);
			}
		}

		deleteGame($row['id_game'], $delete_files);

		$status[] = array(
			'id' => $row['id_game'],
			'name' => $row['game_name'],
		);
	}

	$smcFunc['db_free_result']($request);

	// remove related topics if they exist
	if (!empty($topics))
		removeTopics($topics, false, false);

	return $status;
}

function moveGames()
{
	global $db_prefix, $modSettings, $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_game, internal_name, game_directory, game_file, thumbnail, thumbnail_small
		FROM {db_prefix}arcade_games');

	if (!is_writable($modSettings['gamesDirectory']))
		fatal_lang_error('arcade_not_writable', false, array($modSettings['gamesDirectory']));

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$from = $modSettings['gamesDirectory']  . '/' . (!empty($row['game_directory']) ? $row['game_directory'] . '/' : '');
		$to = $modSettings['gamesDirectory'] . '/' . str_replace(array('/', '\\'), array('', ''), trim($row['internal_name'], '.')) . '/';
		$to = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $to)))));

		if ($from == $to)
			continue;

		if ((file_exists($to) && !is_dir($to)) || !mkdir($to))
			fatal_lang_error('arcade_not_writable', false, array($to));

		chdir($from);

		// These should be at least there
		$files = array();
		$files[] = $row['game_file'];
		$files[] = $row['thumbnail'];
		$files[] = $row['thumbnail_small'];

		foreach ($files as $file)
		{
			if (file_exists($from . $file))
				if (!rename($from . $row['gameFile'], $to . $file))
					fatal_lang_error('arcade_unable_to_move', false, array($file, $from, $to));
		}

		updateGame($row['id_game'], array('game_directory' => $row['internal_name']));
	}
	$smcFunc['db_free_result']($request);
}

function updateCategoryStats()
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_categories
		SET num_games = {int:num_games}',
		array(
			'num_games' => 0,
		)
	);

	$request = $smcFunc['db_query']('', '
		SELECT id_cat, COUNT(*) as games
		FROM {db_prefix}arcade_games
		GROUP BY id_cat');

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_categories
			SET num_games = {int:num_games}
			WHERE id_cat = {int:category}',
			array(
				'category' => $row['id_cat'],
				'num_games' => $row['games'],
			)
		);
	$smcFunc['db_free_result']($request);

	return true;
}

function readGameInfo($file)
{
	if (!file_exists($file))
		return false;

	$gameinfo = new xmlArray(file_get_contents($file));
	$gameinfo = $gameinfo->path('game-info[0]');
	return $gameinfo->to_array();
}


function check_empty_folder ($folder)
{
	if(is_dir($folder))
	{
		$files = array();
		if ($handle = opendir($folder))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
					$files [] = $file;
			}
			closedir ($handle);
		}
	}

	return (count($files) > 0) ? FALSE : TRUE;
}

function getGameName($internal_name)
{
	global $smcFunc;

	$internal_name = str_replace(array('_', '-'), ' ', $internal_name);

	if (strtolower(substr($internal_name, -2)) == 'ch' || strtolower(substr($internal_name, -2)) == 'gc')
		$internal_name = substr($internal_name, 0, strlen($internal_name) - 2);
	elseif (strtolower(substr($internal_name, -2)) == 'v2')
		$internal_name = substr($internal_name, 0, strlen($internal_name) - 2) . ' v2';

	$internal_name = trim(str_replace(array('/', '\\'), array('', ''), trim($internal_name, '.')));
	return ucwords($internal_name);
}

function getInternalName($file, $directory)
{
	if (is_dir($directory . '/' . $file))
	{
		if (file_exists($directory . '/' . $file . '/game-info.xml'))
		{
			$gameinfo = readGameInfo($directory . '/' . $file . '/game-info.xml');
			return $gameinfo['id'];
		}
		else
			return $file;
	}

	$pos = strrpos($file, '.');

	if ($pos === false)
		return $file;

	return substr($file, 0, $pos);
}

function isGame($file, $directory)
{
	// Is single file which is game?
	if (!is_dir($directory . '/' . $file) && substr($file, -3) == 'swf')
		return array(true, $directory, array('file' => $file));

	// Is game directory?
	elseif (is_dir($directory . '/' . $file))
	{
		if (file_exists($directory . '/' . $file . '/' . $file . '.swf'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => $file . '.swf')
			);
		elseif (file_exists($directory . '/' . $file . '/' . $file . '.html'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => $file . '.html')
			);
		elseif (file_exists($directory . '/' . $file . '/' . $file . '.xap'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => $file . '.xap')
			);
		elseif (file_exists($directory . '/' . $file . '/' . $file . '.php'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => $file . '.php')
			);
		elseif (file_exists($directory . '/' . $file . '/game-info.xml'))
			return array(
				true,
				$directory . '/' . $file,
				readGameInfo($directory . '/' . $file . '/game-info.xml')
			);
	}

	return array(false, false, false);
}

function getAvailableGames($subpath = '', $recursive = true)
{
	global $modSettings;

	if (substr($subpath, -1) == '/')
		$subpath = substr($subpath, 0, -1);

	$directory = $modSettings['gamesDirectory'] . (!empty($subpath) ? '/' . $subpath : '');

	$games = array();

	if ($subpath != '' && $recursive == 'unpack')
	{
		list ($is_game, $gdir, $extra) = isGame(basename($subpath), dirname($directory));

		if ($is_game)
		{
			$gdir_rel = substr($gdir, strlen($modSettings['gamesDirectory']));

			if (substr($gdir_rel, 0, 1) == '/')
				$gdir_rel = substr($gdir_rel, 1);

			$games[] = array(
				'type' => 'game',
				'directory' => $gdir_rel,
				'filename' => $extra['file'],
			);

			return $games;
		}
	}

	$recursive = (bool) $recursive;

	$directoryHandle = opendir($directory);

	while ($file = readdir($directoryHandle))
	{
		if ($file == '.' || $file == '..')
			continue;

		list ($is_game, $gdir, $extra) = isGame($file, $directory);

		if ($is_game)
		{
			$gdir_rel = substr($gdir, strlen($modSettings['gamesDirectory']));

			if (substr($gdir_rel, 0, 1) == '/')
				$gdir_rel = substr($gdir_rel, 1);

			$games[] = array(
				'type' => 'game',
				'directory' => $gdir_rel,
				'filename' => $extra['file'],
			);
		}
		elseif (!is_dir($directory . '/' . $file))
		{
			if (in_array(substr($file, -3), array('zip', 'tar', '.gz')))
			{
				$gameinfo = read_tgz_data(file_get_contents($directory . '/' . $file), 'game-info.xml', true);

				if ($gameinfo)
				{
					$gameinfo = new xmlArray($gameinfo);
					$gameinfo = $gameinfo->path('game-info[0]');

					$games[] = array(
						'type' => 'gamepackage',
						'directory' => $subpath,
						'filename' => $file,
					);

					unset($gameinfo);
				}
				else
					$games[] = array(
						'type' => 'gamepackage-multi',
						'directory' => $subpath,
						'filename' => $file,
					);
			}
		}
		elseif ($recursive)
			$games = array_merge($games, getAvailableGames((!empty($subpath) ? $subpath . '/' : '') . $file, false));

		unset($is_game, $gdir, $extra);
	}
	return $games;
}

function updateGameCache()
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $sourcedir, $smcFunc, $boarddir;

	// Clear entries
	$smcFunc['db_query']('truncate_table', '
		TRUNCATE {db_prefix}arcade_files'
	);

	require_once($sourcedir . '/Subs-Package.php');
	loadClassFile('Class-Package.php');

	// Try to get more memory
	@ini_set('memory_limit', '128M');

	// Do actual update
	gameCacheInsertGames(getAvailableGames());

	updateSettings(array('arcadeDBUpdate' => time()));
}

function gameCacheInsertGames($games, $return = false)
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $sourcedir, $smcFunc, $boarddir, $boardurl;

	$filesAvailable = array();
	$filesKeys = array();

	foreach ($games as $id => $game)
	{
		if ($game['type'] == 'game')
		{
			if (!empty($game['directory']))
			{
				$game_directory = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $modSettings['gamesDirectory'] . '/' . $game['directory'])))));
				$game_file = $game_directory . '/' . $game['filename'];
			}
			else
			{
				$game_directory = $modSettings['gamesDirectory'];
				$game_file = $modSettings['gamesDirectory'] . '/' . $game['filename'];
			}

			$filesAvailable[$game_file] = $id;
			$filesKeys[$game_file] = $id;

			// Move gamedata file for IBP arcade games
			if (file_exists($game_directory . '/gamedata') && substr($game['filename'], -3) == 'swf')
			{
				$from = $game_directory . '/gamedata';
				$to = $boarddir . '/arcade/gamedata/' . substr($game['filename'], 0, -4);

				if (!file_exists($boarddir . '/arcade/') && !mkdir($boarddir . '/arcade/', 0755))
					fatal_lang_error('unable_to_make', false, array($boarddir . '/arcade/'));
				if (!file_exists($boarddir . '/arcade/gamedata/') && !mkdir($boarddir . '/arcade/gamedata/', 0755))
					fatal_lang_error('unable_to_make', false, array($boarddir . '/arcade/gamedata/'));
				if (!file_exists($to) && !mkdir($to, 0755))
					fatal_lang_error('unable_to_make', false, array($to));
				elseif (!is_dir($to))
					fatal_lang_error('unable_to_make', false, array($to));

				if (!is_writable($to))
					fatal_lang_error('unable_to_chmod', false, array($to));

				copyArcadeDirectory($from, $boarddir . '/arcade/gamedata');
				deleteArcadeArchives($from);

				if (!@file_exists($to))
					fatal_lang_error('unable_to_move', false, array($from, $to));


			}
		}
	}

	// Installed games
	$request = $smcFunc['db_query']('', '
		SELECT id_game, game_name, internal_name, game_file, game_directory
		FROM {db_prefix}arcade_games'
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($row['game_directory']))
			$game_file = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $modSettings['gamesDirectory'] . '/' . $row['game_directory']))))) . '/' . $row['game_file'];
		else
			$game_file = $modSettings['gamesDirectory'] . '/' . $row['game_file'];

		if (isset($filesKeys[$game_file]))
		{
			$games[$filesKeys[$game_file]] += array(
				'type' => 'game',
				'id' => $row['id_game'],
				'name' => $row['game_name'],
				'directory' => $row['game_directory'],
				'filename' => $row['game_file'],
				'internal_name' => $row['internal_name'],
				'installed' => true,
			);
		}
		else
		{
			$fileKeys[$game_file] = count($games);

			$games[] = array(
				'type' => 'game',
				'id' => $row['id_game'],
				'name' => $row['game_name'],
				'directory' => $row['game_directory'],
				'filename' => $row['game_file'],
				'internal_name' => $row['internal_name'],
				'missing_files' => true,
			);
		}
	}
	$smcFunc['db_free_result']($request);

	$rows = array();

	// Last step
	foreach ($games as $id => $game)
	{
		$masterInfo = array();
		if (!empty($game['directory']))
		{
			$game_directory = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $modSettings['gamesDirectory'] . '/' . $game['directory'])))));
			$game_file = $modSettings['gamesDirectory'] . '/' . $game['directory'] . '/' . $game['filename'];
		}
		else
		{
			$game_directory = $modSettings['gamesDirectory'];
			$game_file = $modSettings['gamesDirectory'] . '/' . $game['filename'];
		}

		// Regular game?
		if ($game['type'] == 'game')
		{
			// Use game info if possible
			if (file_exists($game_directory . '/game-info.xml') && !isset($game['gameinfo']))
				$gameinfo = readGameInfo($game_directory . '/game-info.xml');
		}
		// Single zipped game?
		elseif ($game['type'] == 'gamepackage')
		{
			$gameinfo = read_tgz_data(file_get_contents($game_file), 'game-info.xml', true);
			$gameinfo = new xmlArray($gameinfo);
			$gameinfo = $gameinfo->path('game-info[0]');
			$gameinfo = $gameinfo->to_array();
		}
		// Gamepackage
		elseif ($game['type'] == 'gamepackage-multi')
			$game['name'] = 'GamePack ' . substr($game['filename'], 0, strrpos($game['filename'], '.'));

		if (isset($gameinfo) && !isset($game['name']))
			$game['name'] = $gameinfo['name'];
		elseif (!isset($game['name']))
			$game['name'] = getGameName(getInternalName($game['filename'], $game_directory));

		// Status of game
		$status = 10;

		if (!empty($game['missing_files']))
			$status = 2;
		elseif (!empty($game['installed']))
			$status = 1;

		if (!isset($game['id']))
			$game['id'] = 0;

		if (!isset($game['directory']))
			$game['directory'] = '';

		if (!isset($game['filename']))
			$game['filename'] = '';

		// Escape data to be inserted into database
		$rows[] = array(
			$game['id'],
			$game['type'],
			$game['name'],
			$status,
			$game['filename'],
			$game['directory'],
		);

		unset($gameinfo);
	}

	if (!empty($rows))
		$smcFunc['db_insert']('insert',
			'{db_prefix}arcade_files',
			array(
				'id_game' => 'int',
				'file_type' => 'string-30',
				'game_name' => 'string-255',
				'status' => 'int',
				'game_file' => 'string-255',
				'game_directory' => 'string-255',
			),
			$rows,
			array('internal_name')
		);

	if ($return)
		return $rows;
}

function arcadeGetGroups($selected = array())
{
	global $smcFunc, $txt, $sourcedir;

	require_once($sourcedir . '/Subs-Members.php');

	$return = array();

	// Default membergroups.
	$return = array(
		-2 => array(
			'id' => '-2',
			'name' => $txt['arcade_group_arena'],
			'checked' => $selected == 'all' || in_array('-2', $selected),
			'is_post_group' => false,
		),
		-1 => array(
			'id' => '-1',
			'name' => $txt['guests'],
			'checked' => $selected == 'all' || in_array('-1', $selected),
			'is_post_group' => false,
		),
		0 => array(
			'id' => '0',
			'name' => $txt['regular_members'],
			'checked' => $selected == 'all' || in_array('0', $selected),
			'is_post_group' => false,
		)
	);

	$groups = groupsAllowedTo('arcade_view');

	if (!in_array(-1, $groups['allowed']))
		unset($context['groups'][-1]);
	if (!in_array(0, $groups['allowed']))
		unset($context['groups'][0]);

	// Load membergroups.
	$request = $smcFunc['db_query']('', '
		SELECT mg.group_name, mg.id_group, mg.min_posts
		FROM {db_prefix}membergroups AS mg
		WHERE mg.id_group > 3 OR mg.id_group = 2
			AND mg.id_group IN({array_int:groups})
		ORDER BY mg.min_posts, mg.id_group != 2, mg.group_name',
		array(
			'groups' => $groups['allowed'],
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[(int) $row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => trim($row['group_name']),
			'checked' => $selected == 'all' ||  in_array($row['id_group'], $selected),
			'is_post_group' => $row['min_posts'] != -1,
		);
	}
	$smcFunc['db_free_result']($request);

	return $return;
}

function postArcadeGames($game, $gameid)
{
	global $user_info, $arcSettings, $scripturl, $sourcedir, $modSettings, $boardurl, $txt, $settings, $smcFunc;

	loadLanguage('Arcade');
	$ranum = (RAND(1,1255));
	$gameid = !empty($gameid) ? (int)$gameid : 0;
	if (empty($modSettings['gamesBoard']) || empty($gameid))
		return 0;

	list($my_message, $board_id, $gamename, $game_width, $game_height, $id, $description, $thumbnail, $style) = array(
		!empty($modSettings['gamesMessage']) ? $modSettings['gamesMessage'] : '',
		$modSettings['gamesBoard'],
		$game['name'],
		200,
		200,
		'x' . $gameid.$ranum . 'x',
		'&nbsp;',
		$settings['default_theme_url'] . '/images/arc_icons/popup_play_btn.gif',
		'width: 50px; height: 14px;'
	);

	$dimension = !empty($game['extra_data']) ? $game['extra_data'] : array();
	$gamefile_name = !empty($game['game_file']) ? $game['game_file'] : '';
	$gamename_name = !empty($game['name']) ? $game['name'] : '';
	$internal = !empty($game['internal_name']) ? $game['internal_name'] : '';
	$gamedirectory = !empty($game['game_directory']) ? $game['game_directory'] : '';
	$game_pic = !empty($game['thumbnail']) ? $game['thumbnail'] : '';
	$game_width =  !empty($dimension['width']) ? (int)$dimension['width'] : 400;
	$game_height = !empty($dimension['height']) ? (int)$dimension['height'] : 400;
	$help = !empty($game['help']) ? $txt['arcade_post_help'] . wordwrap($game['help'], 140, "<br />") : '&nbsp;';
	$description = !empty($game['description']) ? $txt['arcade_post_description'] . wordwrap($game['description'], 140, "<br />") : '&nbsp;';
	$directory = $modSettings['gamesDirectory'] . (!empty($gamedirectory) ? '/' . $gamedirectory : '');

	if (!empty($game_pic) && @file_exists($directory . '/' . $game_pic))
	{
		$thumbnail = $modSettings['gamesUrl'] . '/' . (!empty($gamedirectory) ? $gamedirectory . '/' : '') . $game_pic;
		$style = "width: 50px;height: 50px;";
	}

	$popup = '<a href="javascript:void(0)" onclick="return myGamePopupArcade(smf_prepareScriptUrl(smf_scripturl)+\'action=arcade;sa=play;game=' . $gameid . ';pop=1\',' . $game_width . ',' . $game_height . ',3);"><img style="' . $style . '" src="' . $thumbnail . '" alt="' . $txt['pdl_popplay'] . '" title="' . $txt['pdl_popplay'] . '" /></a>';
	require_once($sourcedir . '/Subs-Post.php');
	$topicTalk = '[center][b][url='.$scripturl.'?action=arcade;sa=play;game='.$gameid.']' . str_replace('%#@$', ' [i]' . $gamename . '[/i]', $txt['arcade_post']) . '[/url][/b][/center]';

	if (empty($modSettings['arcadeEnableDownload']))
		$modSettings['arcadeEnableDownload'] = false;

	if (empty($modSettings['arcadeEnableIframe']))
		$modSettings['arcadeEnableIframe'] = false;

	if (empty($modSettings['arcadePosterid']))
		$modSettings['arcadePosterid'] = $user_info['id'];

	if ($modSettings['arcadePosterid'] < 1)
		$modSettings['arcadePosterid'] = 0;

	if ($modSettings['arcadeEnableDownload'] == true)
		$topicTalk .= '<br /><center><a style="color: red;text-decoration: none;" href="' . $scripturl . '?action=arcade;sa=download;game=' . $gameid . '"><img src="' . $settings['default_theme_url'] . '/images/arc_icons/dl_btn_popup.png" alt="' . $txt['arcade_download_game'] . '" title="' . $txt['arcade_download_game'] . '" /></a></center>';

	if ($modSettings['arcadeEnableIframe'] == true)
	{
		$topicTalk .= '
		[center]' . $popup . '[/center]
			<center>
				<p id="' . $id . '">' . $help . '<br /><br />' . $description . '</p>
			</center>
			<div style="margin-top: 3px; text-align: center" class="smalltext">'.$txt['pdl_arcade_copyright'].'</div>';
	}

	$topicTalk .= $my_message;

	$msgOptions = array(
		'id' => 0,
		'subject' => $gamename,
		'body' => $topicTalk,
		'icon' => "xx",
		'smileys_enabled' => true,
		'attachments' => array(),
	);
	$topicOptions = array(
		'id' => 0,
		'board' => $board_id,
		'poll' => null,
		'lock_mode' => null,
		'sticky_mode' => null,
		'mark_as_read' => true,
	);
	$posterOptions = array(
		'id' => $modSettings['arcadePosterid'],
		'name' => "Arcade",
		'email' => "arcade@here",
	);

	createPost($msgOptions, $topicOptions, $posterOptions);

	if (isset($topicOptions['id']))
	{
		$topicid = $topicOptions['id'];
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_games
			SET id_topic = {int:id_topic}
			WHERE id_game = {int:gameid}',
			array(
				'gameid' => $gameid,
				'id_topic' => $topicid,
			)
		);
	}

	return $topicid;
}

function copyArcadeDirectory($source, $destination)
{
	if (is_dir($source))
	{
		if (!is_dir($destination))
			@mkdir($destination);

		$directory = @dir($source);
		while (FALSE !== ($readdirectory = $directory->read()))
		{
			if ($readdirectory == '.' || $readdirectory == '..')
				continue;

			$PathDir = $source . '/' . $readdirectory;

			if (is_dir($PathDir))
			{
				copyArcadeDirectory($PathDir, $destination . '/' . $readdirectory);
				continue;
			}
			copy($PathDir, $destination . '/' . $readdirectory);
		}

		$directory->close();
	}
	elseif (@file_exists($source) && !@file_exists($destination))
		@copy($source, $destination);
	else
		return false;

	return true;
}

function deleteArcadeArchives($directory)
{
	global $boardurl, $boarddir;
	$directory = substr($directory,-1) == "/" ? substr($directory,0,-1) : $directory;

	if (is_dir($directory))
	{
		$directoryHandle = opendir($directory);
		while ($contents = readdir($directoryHandle))
		{
			if($contents != '.' && $contents != '..')
			{
				$path = $directory . "/" . $contents;
				if (is_dir($path))
					deleteArcadeArchives($path);
				elseif (file_exists($path))
					unlink($path);
			}
		}
		closedir($directoryHandle);
		rmdir($directory);
	}
	elseif (file_exists($directory))
		unlink($directory);
	else
		return false;

	return true;
}

function ArcadeAdminScanDir($dir, $ignore = '')
{
	$arrfiles = array();
	if (is_dir($dir))
	{
		if ($handle = opendir($dir))
		{
			chdir($dir);
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
				{
					if (is_dir($file))
					{
						$arr = ArcadeAdminScanDir($file, '');
						foreach ($arr as $value)
							$arrfiles[] = $dir . '/' . $value;
                    }
					elseif ((!empty($ignore)) && basename($file) == $ignore)
                        continue;
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

function ArcadeAdminCategoryDropdown()
{
	// Admin Category drop down menu
	global $scripturl, $smcFunc, $txt, $modSettings;
	$count = 0;
	$current = !empty($modSettings['arcadeDefaultCategory']) ? (int)$modSettings['arcadeDefaultCategory'] : 0;
	$selected = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? ' selected="selected"' : ' selected';
	$display = '
		<select name="cat_default" style="font-size: 100%;" onchange="JavaScript:submit()">
			<option value="">' . $txt['arcade_admin_opt_cat'] . '</option>';

	$request = $smcFunc['db_query']('', '
		SELECT id_cat, cat_name, num_games, cat_order
		FROM {db_prefix}arcade_categories
		ORDER BY cat_order',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$count = $row['id_cat'];
		$cat_name[$count] = $row['cat_name'];

		$display .= '
			<option value="' . $count . '"'. ($current == $count ? $selected : '') . '>' . $cat_name[$count] . '</option>';
	}

	$smcFunc['db_free_result']($request);

	$display .= '
			<option value="all"'. ($current == 0 ? $selected : '') . '>' . $txt['arcade_all'] . '</option>
		</select>';

	return $display;
}

function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val{strlen($val) - 1});
    switch($last)
	{
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

// unpack zipped game archives
function arcadeUnzip($src_file, $dest_dir = false, $create_zip_name_dir = true, $overwrite = true)
{
	if ($zip = zip_open($src_file))
	{
		if ($zip)
		{
			$splitter = ($create_zip_name_dir === true) ? '.' : '/';
			if ($dest_dir === false)
			{
				$dest_dir = substr($src_file, 0, strrpos($src_file, $splitter)) . '/';
				$dest_dir = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2",  $dest_dir);
			}

			arcadeCreateDirs($dest_dir);

			while ($zip_entry = zip_read($zip))
			{
				$pos_last_slash = strrpos(zip_entry_name($zip_entry), '/');
				if ($pos_last_slash !== false)
					arcadeCreateDirs($dest_dir . substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1));

				if (zip_entry_open($zip,$zip_entry, 'r'))
				{
					$file_name = $dest_dir . zip_entry_name($zip_entry);
					$dir_name = dirname($file_name);

						if ($overwrite === true || ($overwrite === false && !is_file($file_name)))
						{
							$fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
							@file_put_contents($file_name, $fstream);
							@chmod($file_name, 0755);
						}

					zip_entry_close($zip_entry);
				}
			}

			zip_close($zip);
		}
	}
	else
		return false;

  return true;
}

// create necessary recursive directories
function arcadeCreateDirs($path)
{
	if (!is_dir($path))
	{
		$directory_path = "";
		$directories = explode("/", $path);
		array_pop($directories);

		foreach($directories as $directory)
		{
			$directory_path .= $directory . '/';
			if (!is_dir($directory_path))
			{
				@mkdir($directory_path);
				@chmod($directory_path, 0755);
			}
		}
	}
}
?>