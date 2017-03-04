<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

/*
	void ManageGames()
		- ???

	void ManageGamesMain()
		- ???

	void ManageGamesQuickEdit()
		- ???

	void EditGame()
		- ???

	void EditGame2()
		- ???

	void gzArcadeCompressFile
		- ???

*/

function ManageGames()
{
	global $scripturl, $txt, $context, $sourcedir, $smcFunc, $modSettings, $settings, $smfVersion;

	require_once($sourcedir . '/Arcade.php');
	require_once($sourcedir . '/Subs-ArcadeAdmin.php');
	$context['html_headers'] .= '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade_upload.css?rc4" />';

	// Templates
	loadTemplate('ManageGames');
	loadArcade('admin', 'manage_games');

	loadClassFile('Class-Package.php');

	// Need to update files in database?
	if (!empty($modSettings['arcadeGamecacheUpdate']) && (empty($modSettings['arcadeDBUpdate']) || $modSettings['arcadeDBUpdate'] < max(filemtime($modSettings['gamesDirectory']), filemtime(__FILE__))))
		updateGameCache();

	if (isset($_REQUEST['uninstall_submit']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'uninstall';

	if (isset($_REQUEST['done']) && !empty($_SESSION['qaction']))
	{
		$context['show_done'] = true;

		if ($_SESSION['qaction'] == 'install')
		{
			$context['qaction_title'] = $txt['arcade_install_complete'];
			$context['qaction_text'] = $txt['arcade_install_following_games'];
		}
		elseif ($_SESSION['qaction'] == 'uninstall')
		{
			$context['qaction_title'] = $txt['arcade_uninstall_complete'];
			$context['qaction_text'] = $txt['arcade_uninstall_following_games'];
		}

		if (isset($_SESSION['qaction_data']) && is_array($_SESSION['qaction_data']))
			$context['qaction_data'] = $_SESSION['qaction_data'];

		unset($_SESSION['qaction_data']);
		unset($_SESSION['qaction']);
	}

	$subActions = array(
		'list' => array('ManageGamesList', 'arcade_admin'),
		'uninstall' => array('ManageGamesUninstall', 'arcade_admin'),
		'uninstall2' => array('ManageGamesUninstall2', 'arcade_admin'),
		'install' => array('ManageGamesInstall', 'arcade_admin'),
		'install2' => array('ManageGamesInstall2', 'arcade_admin'),
		'upload' => array('ManageGamesUpload', 'arcade_admin'),
		'upload2' => array('ManageGamesUpload2', 'arcade_admin'),
		'quickedit' => array('ManageGamesQuickEdit', 'arcade_admin'),
		'edit' => array('EditGame', 'arcade_admin'),
		'edit2' => array('EditGame2', 'arcade_admin'),
		'export' => array('ExportGameinfo', 'arcade_admin'),
	);

	// What user wants to do?
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	// Do we have reason to allow him/her to do it?
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_games_desc'];

	$smfVersion = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$subActions[$_REQUEST['sa']][0]();
}

function ManageGamesList()
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $sourcedir, $smcFunc;

	if (!isset($context['arcade_category']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_cat, cat_name
			FROM {db_prefix}arcade_categories'
		);

		$context['arcade_category'] = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['arcade_category'][$row['id_cat']] = array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name']
			);
		$smcFunc['db_free_result']($request);
	}

	$category_data = '';

	foreach ($context['arcade_category'] as $id => $cat)
		$category_data .= '
	<option value="' . $id . '">' . $cat['name'] . '</option>';

	if (isset($_REQUEST['category_submit']))
	{
		foreach ($_REQUEST['game'] as $id_game)
			updateGame($id_game, array('category' => (int) $_REQUEST['category']), true);

		redirectexit('action=admin;area=managegames');
	}

	$filter = 'all';

	if (isset($_REQUEST['filter']) && in_array($_REQUEST['filter'], array('enabled', 'disabled')))
		$filter = $_REQUEST['filter'];

	$listOptions = array(
		'id' => 'games_list',
		'title' => '',
		'items_per_page' => $modSettings['gamesPerPage'],
		'base_href' => $scripturl . '?action=admin;area=managegames' . ($filter !== 'all' ? ';filter=' . $filter : ''),
		'default_sort_col' => 'name',
		'no_items_label' => sprintf($filter == 'all' ? $txt['arcade_no_games_installed'] : $txt['arcade_no_games_filter'], $scripturl . '?action=admin;area=managegames;sa=install'),
		'use_tabs' => true,
		'list_menu' => array(
			'style' => 'buttons',
			'position' => 'right',
			'columns' => 3,
			'show_on' => 'both',
			'links' => array(
				'show_all' => array(
					'href' => $scripturl . '?action=admin;area=managegames',
					'label' => $txt['manage_games_filter_all'],
					'is_selected' => $filter == 'all',
				),
				'enabled' => array(
					'href' => $scripturl . '?action=admin;area=managegames;filter=enabled',
					'label' => $txt['manage_games_filter_enabled'],
					'is_selected' => $filter == 'enabled',
				),
				'disabled' => array(
					'href' => $scripturl . '?action=admin;area=managegames;filter=disabled',
					'label' => $txt['manage_games_filter_disabled'],
					'is_selected' => $filter == 'disabled',
				),
			),
		),
		'get_items' => array(
			'function' => 'list_getGamesInstalled',
			'params' => array($filter),
		),
		'get_count' => array(
			'function' => 'list_getNumGamesInstalled',
			'params' => array($filter),
		),
		'columns' => array(
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" class="check" onclick="invertAll(this, this.form, \'game[]\');" />',
					'style' => 'width: 10px;'
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="game[]" value="%d" class="check" />',
						'params' => array('id' => false),
					),
					'style' => 'text-align: center;',
				),
			),
			'name' => array(
				'header' => array(
					'value' => $txt['arcade_game_name'],
					'style' => 'text-align: left;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						$link = \'<a href="\' . $rowData[\'href\'] . \'">\' . $rowData[\'name\'] . \'</a>\';

						if (!empty($rowData[\'error\']))
							$link .= \'<div class="alert smalltext">\' . $rowData[\'error\'] . \'</div>\';

						return $link;
					'),
				),
				'sort' => array(
					'default' => 'g.game_name',
					'reverse' => 'g.game_name DESC',
				),
			),
			'category' => array(
				'header' => array(
					'value' => $txt['arcade_category'],
					'style' => 'text-align: left;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						$link = $rowData[\'category\'][\'name\'];

						return $link;
					'),
				),
				'sort' => array(
					'default' => 'cat.cat_name',
					'reverse' => 'cat.cat_name DESC',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=managegames' . ($filter !== 'all' ? ';filter=' . $filter : ''),
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<select name="category">' . $category_data . '</select> <input class="button_submit" type="submit" name="category_submit" value="' . $txt['quickmod_change_category'] . '" />
				<input class="button_submit" type="submit" name="uninstall_submit" value="' . $txt['quickmod_uninstall_selected'] . '" />',
				'class' => 'titlebg',
				'style' => 'text-align: right;',
			),
		),
	);

	// Create the list.
	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'manage_games_list';
}

function ManageGamesInstall()
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $sourcedir, $smcFunc, $settings, $smfVersion;

	isAllowedTo('arcade_admin');

	if ($smfVersion === 'v2.1')
		createToken('admin', 'post');

	$context['sub_template'] = 'manage_games_list';

	$listOptions = array(
		'id' => 'games_list',
		'title' => '',
		'items_per_page' => $modSettings['gamesPerPage'],
		'base_href' => $scripturl . '?action=admin;area=managegames;sa=install',
		'default_sort_col' => 'name',
		'no_items_label' => sprintf($txt['arcade_no_games_available_for_install'], $scripturl . '?action=admin;area=managegames;sa=upload'),
		'get_items' => array(
			'function' => 'list_getGamesInstall',
			'params' => array(
			),
		),
		'get_count' => array(
			'function' => 'list_getNumGamesInstall',
			'params' => array(
			),
		),
		'columns' => array(
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" class="check" onclick="invertAll(this, this.form, \'file[]\');" />',
					'style' => 'width: 10px;'
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="file[]" value="%d" class="check" />',
						'params' => array('id_file' => false),
					),
					'style' => 'text-align: center;',
				),
			),
			'name' => array(
				'header' => array(
					'value' => $txt['arcade_game_name'],
					'style' => 'text-align: left;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						$link = $rowData[\'name\'];

						return $link;
					'),
				),
				'sort' => array(
					'default' => 'f.game_name',
					'reverse' => 'f.game_name DESC',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=managegames;sa=install2;sesc=' . $context['session_id'],
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<input onclick="return arcadeDelClick()" id="quick_del" type="submit" name="delete_submit" value="' . $txt['quickmod_delete_selected'] . '" />',
				'class' => 'arcade_install_button',
				'style' => 'display: inline;float: left;',
			),
			array(
				'position' => 'below_table_data',
				'value' => '<input type="submit" name="install_submit" value="' . $txt['quickmod_install_selected'] . '" />',
				'class' => 'arcade_install_button',
				'style' => 'display: inline;float: right;',
			),
		),
	);

	$context['html_headers'] .= '
	<script type="text/javascript">
		function arcadeDelClick(val) {
			var myConf = confirm("' . $txt['arcade_are_you_sure_delete'] . '");
			return myConf;
		}
	</script>
	<link href="' . $settings['default_theme_url'] . '/css/arcade-upload.css?rc4" rel="stylesheet" type="text/css" />';

	// Create the list.
	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);
}

function ManageGamesInstall2()
{
	global $smcFunc, $context, $smfVersion, $modSettings;

	isAllowedTo('arcade_admin');
	checkSession('post');

	if ($smfVersion === 'v2.1')
		validateToken('admin', 'post', false);

	if (!isset($_REQUEST['file']))
		fatal_lang_error('arcade_no_games_selected', false);

	if (!is_array($_REQUEST['file']))
		$games = array($_REQUEST['file']);
	else
		$games = $_REQUEST['file'];

	foreach ($games as $id => $game)
		$games[$id] = (int) $game;
	$games = array_unique($games);

	if (count($games) == 0)
		fatal_lang_error('arcade_no_games_selected', false);

	if (isset($_REQUEST['install_submit']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_file, file_type, status
			FROM {db_prefix}arcade_files
			WHERE id_file IN ({array_int:games})
				AND status = 10',
			array(
				'games' => $games,
			)
		);

		$unpack = array();
		$install = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			// Needs uncompression?
			if ($row['file_type'] !== 'game')
				$unpack[] = $row['id_file'];
			else
				$install[] = $row['id_file'];
		}
		$smcFunc['db_free_result']($request);

		// Unpack games first
		if (!empty($unpack))
			unpackGames($unpack);

		if (!empty($install))
		{
			$_SESSION['qaction'] = 'install';
			$_SESSION['qaction_data'] = installGames($install);
		}

		redirectexit('action=admin;area=managegames;sa=install;done;sesc=' . $context['session_id']);
	}
	else
	{
		$location = rtrim($modSettings['gamesDirectory'], '/');
		list($id_games, $id_files, $id_game_files) = array(array(), array(), array());

		foreach ($games as $game)
			$id_files[] = $game;

		if (!empty($id_files))
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_file, id_game, game_file, game_directory, status
				FROM {db_prefix}arcade_files
				WHERE id_file IN({array_int:games})
					AND status = 10',
				array(
					'games' => $id_files
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$id_games[] = !empty($row['id_game']) ? (int)$row['id_game'] : 0;
				$id_game_files[] = !empty($row['id_file']) ? $row['id_file'] : 0;
				$files = array_unique(
					array(
						$row['game_file'],
						mb_substr($row['game_file'], 0, -4) . '.png',
						mb_substr($row['game_file'], 0, -4) . '.gif',
						mb_substr($row['game_file'], 0, -4) . '.jpg',
						mb_substr($row['game_file'], 0, -4) . '.php',
						mb_substr($row['game_file'], 0, -4) . '-game-info.xml',
						mb_substr($row['game_file'], 0, -4) . '.xap',
						mb_substr($row['game_file'], 0, -4) . '.ini',
					)
				);
				$dest = $location . '/' . basename($row['game_directory']);
				$ibp = (strlen($row['game_file']) > 4) && mb_substr($row['game_file'], -4) == '.php' ? basename($game['game_file']) : '';
				if ((!empty($row['game_directory'])) && $dest !== $location)
				{
					foreach ($files as $file)
						if (file_exists($dest . '/' . $file))
							unlink($dest . '/' . $file);
					$gfiles = ArcadeAdminScanDir($dest, '');
					if (empty($gfiles))
						rmdir($dest);
					elseif ((count($gfiles) == 1) && $gfiles[0] == 'master-info.xml')
					{
						unlink($dest . '/master-info.xml');
						rmdir($dest);
					}
				}
				if((!empty($row['game_file'])))
				{
					foreach ($files as $file)
						if (file_exists($location . '/' . $file))
							unlink($location . '/' . $file);
				}

				if ((!empty($ibp)) && is_dir($boarddir . '/arcade/gamedata/' . $ibp))
				{
					$gdfiles = ArcadeAdminScanDir($boarddir . '/arcade/gamedata/' . $ibp, '');
					foreach ($gdfiles as $file)
						unlink($file);
					rmdir($boarddir . '/arcade/gamedata/' . $ibp);
				}
			}

			$smcFunc['db_free_result']($request);

			if (!empty($id_games))
				uninstallGames($id_games, true);

			foreach ($id_game_files as $id)
			{
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_files
					WHERE id_file = {int:file}',
					array(
						'file' => $id,
					)
				);
			}
		}

		redirectexit('action=admin;area=managegames;sa=install;sesc=' . $context['session_id']);
	}
}

function ManageGamesUninstall()
{
	global $smcFunc, $context, $txt, $scripturl, $smfVersion;

	isAllowedTo('arcade_admin');

	if ($smfVersion === 'v2.1')
		createToken('admin', 'post');

	if (!isset($_REQUEST['game']))
		fatal_lang_error('arcade_no_games_selected', false);

	if (!is_array($_REQUEST['game']))
		$games = array($_REQUEST['game']);
	else
		$games = $_REQUEST['game'];

	foreach ($games as $id => $game)
		$games[$id] = (int) $game;
	$games = array_unique($games);

	if (count($games) == 0)
		fatal_lang_error('arcade_no_games_selected', false);

	$request = $smcFunc['db_query']('', '
		SELECT f.id_file, f.id_game, f.file_type, f.status, g.game_name
		FROM {db_prefix}arcade_files AS f
			INNER JOIN {db_prefix}arcade_games AS g ON (g.id_game = f.id_game)
		WHERE f.id_game IN ({array_int:games})
			AND f.status < 10',
		array(
			'games' => $games,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('arcade_no_games_selected', false);

	$context['games'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['games'][$row['id_game']] = array(
			'id' => $row['id_game'],
			'id_file' => $row['id_file'],
			'name' => $row['game_name'],
		);
	$smcFunc['db_free_result']($request);

	$context['confirm_url'] = $scripturl . '?action=admin;area=managegames;sa=uninstall2;sesc=' . $context['session_id'];
	$context['confirm_title'] = $txt['arcade_uninstall_games'];
	$context['confirm_text'] = $txt['arcade_following_games_uninstall'];
	$context['confirm_button'] = $txt['arcade_uninstall_games'];

	// Template
	$context['sub_template'] = 'manage_games_uninstall_confirm';
}

function ManageGamesUninstall2()
{
	global $smcFunc, $context, $smfVersion;

	isAllowedTo('arcade_admin');
	checkSession('request');

	if ($smfVersion === 'v2.1')
		validateToken('admin', 'post', false);

	if (!isset($_REQUEST['game']))
		fatal_lang_error('arcade_no_games_selected', false);

	if (!is_array($_REQUEST['game']))
		$games = array($_REQUEST['game']);
	else
		$games = $_REQUEST['game'];

	foreach ($games as $id => $game)
		$games[$id] = (int) $game;
	$games = array_unique($games);

	if (count($games) == 0)
		fatal_lang_error('arcade_no_games_selected', false);

	$request = $smcFunc['db_query']('', '
		SELECT f.id_file, f.id_game, f.file_type, f.status, g.game_name
		FROM {db_prefix}arcade_files AS f
			INNER JOIN {db_prefix}arcade_games AS g ON (g.id_game = f.id_game)
		WHERE f.id_game IN ({array_int:games})
			AND f.status < 10',
		array(
			'games' => $games,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('arcade_no_games_selected', false);

	$context['games'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['games'][$row['id_game']] = array(
			'id_game' => $row['id_game'],
			'id_file' => $row['id_file'],
			'name' => $row['game_name']
		);
	$smcFunc['db_free_result']($request);

	$id_game = array();
	foreach ($context['games'] as $game)
		$id_game[] = $game['id_game'];

	$_SESSION['qaction'] = 'uninstall';
	$_SESSION['qaction_data'] = uninstallGames($id_game, isset($_REQUEST['remove_files']));

	redirectexit('action=admin;area=managegames;sa=main;done;sesc=' . $context['session_id']);
}

function ManageGamesUpload()
{
	global $scripturl, $txt, $modSettings, $context, $sourcedir, $smcFunc, $settings, $user_settings, $cookiename, $user_info, $smfVersion;

	isAllowedTo('arcade_admin');

	if ($smfVersion == 'v2.1')
	{
		$modSettings['cookieTime'] = 3153600;
		createToken('admin', 'post');
	}
	else
		require_once($sourcedir . '/Subs-Auth.php');

	// this is done so we are not logged-out whilst using the container
	if (!empty($modSettings['arcadeUploadSystem']))
	{
		if ($smfVersion !== 'v2.1')
		{
			$cookie_state = (empty($modSettings['localCookies']) ? 0 : 1) | (empty($modSettings['globalCookies']) ? 0 : 2);
			$data = serialize(array($user_info['id'], sha1($user_settings['passwd'] . $user_settings['password_salt']), time() + (60 * $modSettings['cookieTime']), $cookie_state));
			$_SESSION['login_' . $cookiename] = $data;
			setLoginCookie(60 * $modSettings['cookieTime'], $user_info['id'], sha1($user_settings['passwd'] . $user_settings['password_salt']));
		}

		$update = array('member_ip' => $user_info['ip'], 'member_ip2' => $_SERVER['BAN_CHECK_IP'], 'passwd_flood' => '');
		$user_info['is_guest'] = false;
		$user_settings['additional_groups'] = explode(',', $user_settings['additional_groups']);
		$user_info['is_admin'] = $user_settings['id_group'] == 1 || in_array(1, $user_settings['additional_groups']);
		$update['last_login'] = time();
		updateMemberData($user_info['id'], $update);
	}

	if (!is_writable($modSettings['gamesDirectory']) && !chmod($modSettings['gamesDirectory'], 0755))
		fatal_lang_error('arcade_not_writable', false, array($modSettings['gamesDirectory']));

	$context['post_max_size'] = return_bytes(ini_get('post_max_size')) / 1048576;

	// css & js implementation
	if (!empty($modSettings['arcadeUploadSystem']))
		$context['html_headers'] .= '
	<link href="' . $settings['default_theme_url'] . '/css/arcade-upload.css?rc4" rel="stylesheet" type="text/css" />' . ($smfVersion !== 'v2.1' ? '
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>' : '') . '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade-uploader-html5.js?ac55"></script>
	<script type="text/javascript">
		// common variables for arcade upload
		var uploadScript = "' . $scripturl . '?action=admin;area=managegames;sa=upload2";
		var uploadError1 = "' . $txt['arcade_supported_filetypes'] . '";
		var uploadMessage1 = "' . $txt['arcade_upload_msg1'] . '";
		var uploadMessage2 = "' . $txt['arcade_upload_msg2'] . '";
		var iBytesUploaded = 0;
		var iBytesTotal = 0;
		var iPreviousBytesLoaded = 0;
		var iMaxFilesize = ' . (return_bytes(ini_get('post_max_size'))) . ';
		var oTimer = 0;
		var sResultFileSize = "";
	</script>';

	// Template
	$context['sub_template'] = 'manage_games_upload';
}

function ManageGamesUpload2()
{
	global $txt, $modSettings, $context, $cookiename, $smfVersion;

	$postVar = !empty($_FILES['attachment']) ? $_FILES['attachment'] : (!empty($_FILES['Filedata']) ? $_FILES['Filedata'] : array());
	list($fileExists, $newname) = array(0, '');

	isAllowedTo('arcade_admin');
	checkSession('post');

	if ($smfVersion === 'v2.1')
		validateToken('admin', 'post', false);

	if (empty($postVar) && empty($modSettings['arcadeUploadSystem']))
		redirectexit('action=admin;area=managegames;sa=install');
	elseif (empty($postVar))
		die($txt['arcade_upload_nofile']);

	foreach ($postVar['tmp_name'] as $n => $dummy)
	{
		if ($postVar['name'][$n] == '')
			continue;

		$postVar['name'][$n] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $postVar['name'][$n]);
		$newname = trim(strtolower(basename($postVar['name'][$n])));
		$target = $modSettings['gamesDirectory'];
		$tmp_name = $postVar['tmp_name'][$n];

		if (substr($newname, -2) !== 'gz' && substr($newname, -3) !== 'tar' && substr($newname, -3) !== 'zip')
			continue;

		if ($target != $modSettings['gamesDirectory'])
		{
			if (!file_exists($target) && !mkdir($target, 0755) && empty($modSettings['arcadeUploadSystem']))
				fatal_lang_error('arcade_not_writable', false, array($target));
			elseif (!file_exists($target) && !mkdir($target, 0755))
				die($txt['arcade_not_writable'] . ' ~ ' . $target);

			if (!is_writable($target) && !chmod($target, 0755) && empty($modSettings['arcadeUploadSystem']))
				fatal_lang_error('arcade_not_writable', false, array($target));
			elseif (!is_writable($target) && !chmod($target, 0755))
				die($txt['arcade_not_writable'] . ' ~ ' . $target);
		}

		if (!file_exists($target . '/' . $newname))
		{
			$fileExists = 0;
			$com = fopen($target . '/' . $newname, "ab");
			$in = fopen($tmp_name, "rb");
			if ($in)
			{
				// pause on every MB
				while ($buff = fread($in, 1048576))
				{
					fwrite($com, $buff);
					sleep(2);
				}
				fclose($in);
			}
			fclose($com);

			//move_uploaded_file($postVar['tmp_name'][$n], $target . '/' . $newname);

			if (!file_exists($target . '/' . $newname) && empty($modSettings['arcadeUploadSystem']))
				fatal_lang_error('arcade_upload_file', false);
			elseif (!file_exists($target . '/' . $newname))
				die($txt['arcade_upload_file']);

			@chmod($target . '/' . $newname, 0755);
		}
		else
			$fileExists = 1;
	}

	if (empty($modSettings['arcadeUploadSystem']))
		redirectexit('action=admin;area=managegames;sa=install');
	elseif (!empty($newname) && empty($fileExists))
		die(sprintf($txt['arcade_upload_complete'] ,$newname));
	elseif (!empty($fileExists))
		die(sprintf($txt['arcade_upload_exists'], $newname));
	else
		die($txt['arcade_upload_nofile']);

}

function EditGame()
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $sourcedir, $smcFunc, $boarddir, $smfVersion;

	$context['game_permissions'] = $modSettings['arcadePermissionMode'] > 2;
	$context['edit_page'] = !isset($_REQUEST['advanced']) ? 'basic' : 'advanced';

	isAllowedTo('arcade_admin');

	if ($smfVersion === 'v2.1')
		createToken('admin', 'post');

	// Load game data unless it has been loaded by EditGame2
	if (!isset($context['game']))
	{
		$id = loadGame((int) $_REQUEST['game'], true);

		if ($id === false)
			fatal_lang_error('arcade_game_not_found', false);

		$game = &$context['arcade']['game_data'][$id];

		$context['game'] = array(
			'id' => $game['id_game'],
			'internal_name' => $game['internal_name'],
			'category' => $game['id_cat'],
			'name' => htmlspecialchars($game['game_name']),
			'thumbnail' => htmlspecialchars($game['thumbnail']),
			'thumbnail_small' => htmlspecialchars($game['thumbnail_small']),
			'description' => htmlspecialchars($game['description']),
			'help' => htmlspecialchars($game['help']),
			'game_file' => $game['game_file'],
			'game_directory' => $game['game_directory'],
			'submit_system' => $game['submit_system'],
			'score_type' => $game['score_type'],
			'member_groups' => explode(',', $game['member_groups']),
			'extra_data' => unserialize($game['extra_data']),
			'enabled' => !empty($game['enabled']),
		);

		if (!is_array($context['game']['extra_data']) || isset($_REQUEST['detect']))
		{
			require_once($sourcedir . '/SWFReader.php');
			$swf = new SWFReader();

			if (substr($game['game_file'], -3) == 'swf')
			{
				$swf->open($modSettings['gamesDirectory'] . '/' . $game['game_directory'] . '/' . $game['game_file']);

				$context['game']['extra_data'] = array(
					'width' => $swf->header['width'],
					'height' => $swf->header['height'],
					'flash_version' => $swf->header['version'],
					'background_color' => $swf->header['background'],
				);

				$swf->close();
			}
			else
			{
				$context['game']['extra_data'] = array(
					'width' => '',
					'height' => '',
					'flash_version' => '',
					'background_color' => array('', '', ''),
				);
			}
		}
	}

	if ($context['game_permissions'])
		$context['groups'] = arcadeGetGroups($context['game']['member_groups']);

	// Load categories
	if (!isset($context['arcade_category']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_cat, cat_name
			FROM {db_prefix}arcade_categories'
		);

		$context['arcade_category'] = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['arcade_category'][] = array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name']
			);
		$smcFunc['db_free_result']($request);
	}

	// Load Sumbit Systems
	if (!isset($context['submit_systems']))
		$context['submit_systems'] = SubmitSystemInfo('*');

	$context['template_layers'][] = 'edit_game';

	if (!isset($_REQUEST['advanced']))
		$context['sub_template'] = 'edit_game_basic';
	else
		$context['sub_template'] = 'edit_game_advanced';
}

function EditGame2()
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $sourcedir, $smcFunc, $boarddir, $smfVersion;

	$context['game_permissions'] = $modSettings['arcadePermissionMode'] > 2;
	$context['edit_page'] = !isset($_REQUEST['advanced']) ? 'basic' : 'advanced';

	isAllowedTo('arcade_admin');

	checkSession('request');

	if ($smfVersion === 'v2.1')
		validateToken('admin', 'post', false);

	if (!isset($context['game']))
	{
		$id = loadGame((int) $_REQUEST['game'], true);

		if ($id === false)
			fatal_lang_error('arcade_game_not_found', false);

		$game = &$context['arcade']['game_data'][$id];

		$context['game'] = array(
			'id' => $game['id_game'],
			'internal_name' => $game['internal_name'],
			'category' => $game['id_cat'],
			'name' => htmlspecialchars($game['game_name']),
			'thumbnail' => htmlspecialchars($game['thumbnail']),
			'thumbnail_small' => htmlspecialchars($game['thumbnail_small']),
			'description' => htmlspecialchars($game['description']),
			'help' => htmlspecialchars($game['help']),
			'game_file' => $game['game_file'],
			'game_directory' => $game['game_directory'],
			'submit_system' => $game['submit_system'],
			'score_type' => $game['score_type'],
			'member_groups' => explode(',', $game['member_groups']),
			'extra_data' => unserialize($game['extra_data']),
			'enabled' => !empty($game['enabled']),
		);

		if (!is_array($context['game']['extra_data']) || isset($_REQUEST['detect']))
		{
			require_once($sourcedir . '/SWFReader.php');
			$swf = new SWFReader();

			if (substr($game['game_file'], -3) == 'swf')
			{
				$swf->open($modSettings['gamesDirectory'] . '/' . $game['game_directory'] . '/' . $game['game_file']);

				$context['game']['extra_data'] = array(
					'width' => $swf->header['width'],
					'height' => $swf->header['height'],
					'flash_version' => $swf->header['version'],
					'background_color' => $swf->header['background'],
				);

				$swf->close();
			}
			else
			{
				$context['game']['extra_data'] = array(
					'width' => '',
					'height' => '',
					'flash_version' => '',
					'background_color' => array('', '', ''),
				);
			}
		}
	}

	$context['game_permissions'] = $modSettings['arcadePermissionMode'] > 2;

	// Load categories
	if (!isset($context['arcade_category']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_cat, cat_name
			FROM {db_prefix}arcade_categories'
		);

		$context['arcade_category'] = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['arcade_category'][] = array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name']
			);
		$smcFunc['db_free_result']($request);
	}

	// Load Sumbit Systems
	if (!isset($context['submit_systems']))
		$context['submit_systems'] = SubmitSystemInfo('*');

	$gameOptions = array();
	$errors = array();

	if (checkSession('request', '', false) !== '')
		$errors['session'] = 'session_timeout';

	// Basic
	if (empty($_REQUEST['edit_page']) || $_REQUEST['edit_page'] == 'basic')
	{
		if (isset($_POST['game_name']) && trim($_POST['game_name']) == '')
			$errors['game_name'] = 'invalid';

		$gameOptions['name'] = $_POST['game_name'];
		$gameOptions['description'] = $_POST['description'];
		$gameOptions['thumbnail'] = $_POST['thumbnail'];
		$gameOptions['thumbanil_small'] = $_POST['thumbnail_small'];
		$gameOptions['help'] = $_POST['help'];

		$gameOptions['enabled'] = !empty($_POST['game_enabled']);

		if ($context['game_permissions'])
		{
			$gameOptions['member_groups'] = array();

			if (!empty($_POST['groups']))
				foreach ($_POST['groups'] as $id)
					$gameOptions['member_groups'][] = (int) $id;
		}

		$gameOptions['category'] = (int) $_POST['category'];
	}
	// Advanced
	else
	{
		if (trim($_POST['internal_name']) == '')
			$errors['internal_name'] = 'invalid';

		if (trim($_POST['game_file']) == '')
			$errors['game_file'] = 'invalid';

		if (!isset($context['submit_systems'][$_POST['submit_system']]))
			$errors['submit_system'] = 'invalid';

		$extra_data = $context['game']['extra_data'];

		if (isset($_POST['extra_data']))
		{
			foreach ($_POST['extra_data'] as $item => $value)
				$extra_data[$item] = $value;
		}

		$gameOptions['internal_name'] = $_POST['internal_name'];
		$gameOptions['submit_system'] = $_POST['submit_system'];
		$gameOptions['game_directory'] = $_POST['game_directory'];
		$gameOptions['game_file'] = $_POST['game_file'];
		$gameOptions['score_type'] = (int) $_POST['score_type'];
		$gameOptions['extra_data'] = $extra_data;
	}

	if (!empty($errors))
	{
		$context['errors'] = $errors;
		return EditGame();
	}

	updateGame($context['game']['id'], $gameOptions, true);

	redirectexit('action=admin;area=managegames;' . $context['session_var'] . '=' . $context['session_id']);
}

function ExportGameInfo()
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $sourcedir, $smcFunc, $boarddir, $arcade_version;

	$id = loadGame((int) $_REQUEST['game'], true);

	if ($id === false)
		fatal_lang_error('arcade_game_not_found', false);

	$game = &$context['arcade']['game_data'][$id];

	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	$game['extra_data'] = unserialize($game['extra_data']);
	$extra = '';

	if (isset($game['extra_data']['flash_version']))
	{
		$extra = '
	<flash>
		<version>' . $game['extra_data']['flash_version'] . '</version>
		<width>' . $game['extra_data']['width'] . '</width>
		<height>' . $game['extra_data']['height'] . '</height>
		<bgcolor>' . strtoupper(implode('', array_map('dechex', $game['extra_data']['background_color'])))  . '</bgcolor>
	</flash>';
	}

	header('Content-Type: text/xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

	printf('<?xml version="1.0"?>
<!-- Generated with SMF Arcade %s -->
<game-info>
	<id>%s</id>
	<name><![CDATA[%s]]></name>
	<description><![CDATA[%s]]></description>
	<help><![CDATA[%s]]></help>
	<thumbnail>%s</thumbnail>
	<thumbnail-small>%s</thumbnail-small>
	<file>%s</file>
	<scoring>%d</scoring>
	<submit>%s</submit>%s
</game-info>',
	$arcade_version,
	$game['internal_name'], htmlspecialchars($game['game_name']), htmlspecialchars($game['description']),
	htmlspecialchars($game['help']), htmlspecialchars($game['thumbnail']), htmlspecialchars($game['thumbnail_small']),
	$game['game_file'], $game['score_type'], $game['submit_system'], $extra
);

	obExit(false);
}

function arcadeBytesToSize1024($bytes, $precision = 2)
{
    $unit = array('B','KB','MB');
    return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision).' '.$unit[$i];
}

?>