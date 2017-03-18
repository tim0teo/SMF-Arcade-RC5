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
	void ArcadeAdmin()
		- ???

	void/array ArcadeAdminSettings()
		- ???

	void/array ArcadeAdminPermission()
		- ???

	void ArcadeAdminCategory()
		- ???

	void ArcadeCategoryList()
		- ???

	void ArcadeCategoryEdit()
		- ???

	void ArcadeCategorySave()
		- ???
*/

function ArcadeAdmin()
{
	global $sourcedir, $scripturl, $txt, $modSettings, $context, $settings, $arcade_server;

	require_once($sourcedir . '/Arcade.php');
	require_once($sourcedir . '/Subs-ArcadeAdmin.php');
	require_once($sourcedir . '/ManageServer.php');

	isAllowedTo('arcade_admin');
	loadArcade('admin', 'arcadesettings');

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_admin_settings'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_general_desc'];
	$context['arcade_smf_version'] = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$_SESSION['arcade_cat_message'] = '';

	$subActions = array(
		'main' => array('ArcadeAdminMain'),
		'settings' => array('ArcadeAdminSettings'),
		'permission' => array('ArcadeAdminPermission'),
		'pdl_settings' => array('ArcadePdlSettings'),
		'pdl_reports' => array('ArcadePdlReports'),
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	if (isset($subActions[$_REQUEST['sa']][1]))
		isAllowedTo($subActions[$_REQUEST['sa']][1]);

	$subActions[$_REQUEST['sa']][0]();
}

function ArcadeAdminMain()
{
	global $scripturl, $txt, $modSettings, $context, $settings;
	$context['arcade_smf_version'] = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$context['sub_template'] = 'arcade_admin_main';
}

function ArcadeAdminSettings($return_config = false)
{
	global $scripturl, $txt, $modSettings, $context, $settings, $sourcedir, $boardurl, $boarddir;

	$context['html_headers'] .= '<script type="text/javascript">
		window.onload = function() {
			document.getElementById("changeUrl").onclick = function(valid, value){document.getElementById(valid).innerHTML="\' + value + \'"; return false;};
			document.getElementById("changePath").onclick = function(valid, value){document.getElementById(valid).innerHTML="\' + value + \'"; return false;};
			document.getElementById("arcadeSkin").onchange = function(){document.getElementById("arcadeSkin").form.submit(); return false;};
		};
	</script>';

	if ($return_config)
		require_once($sourcedir . '/Subs-Arcade.php');
	else
		$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_settings_desc'];

	$config_vars = array(
			array('check', 'arcadeArenaEnabled'),
			array('check', 'arcadeEnableFavorites'),
			array('check', 'arcadeEnableRatings'),
		'',
			array('text', 'gamesUrl', 'subtext' => '<a id="changeUrl" href="" onclick="changeVal(\'gamesUrl\', \'' .  sprintf($txt['arcade_rec_val'], $boardurl . '/Games') . '\'); return false;">' .  sprintf($txt['arcade_rec_val'], $boardurl . '/Games') . '</a>'),
			array('text', 'gamesDirectory', 'subtext' => '<a id="changePath" href="" onclick="changeVal(\'gamesDirectory\', \'' .  sprintf($txt['arcade_rec_val'], $boarddir . '/Games') . '\'); return false;">' .  sprintf($txt['arcade_rec_val'], $boarddir . '/Games') . '</a>'),
			array('check', 'arcadeGamecacheUpdate'),
			array('check', 'arcadeUploadSystem'),
		'',
			array('int', 'arcadeCommentLen', 'subtext' => $txt['arcadeCommentLen_subtext']),
		'',
			array('int', 'gamesPerPage'),
			array('int', 'matchesPerPage'),
			array('int', 'scoresPerPage'),
		'',
			array('select', 'arcadeCheckLevel',
				array($txt['arcade_check_level0'], $txt['arcade_check_level1'], $txt['arcade_check_level2'])
			),
		'',
			array('int', 'arcadeMaxScores'),
		'',
			array('select', 'arcadeList',
				array($txt['arcade_list0'], $txt['arcade_list1'], $txt['arcade_list2'])
			),
		'',
			array('check', 'arcadeShowIC'),
			array('check', 'arcadeShowOnline'),
		'',
			array('int', 'arcade_catWidth'),
			array('int', 'arcade_catHeight'),
		'',
			array('select', 'arcadeSkin',
				array(&$txt['arcade_default'], &$txt['arcade_skin_a'], &$txt['arcade_skin_b'])
			),
		'',
	);

	if(isset($modSettings['arcadeSkin']) && $modSettings['arcadeSkin'] == 1)
	{
		// Skin A (Enterprise)
		$a_skin_vars = array(
			array('check', 'arcadeDropCat'),
			array('int', 'arcade_decimal', 'subtext' => $txt['arcade_decimal_recommend']),
			array('check', 'skin_showcatchamps'),
			array('int', 'skin_latest_scores'),
			array('int', 'skin_latest_champs'),
			array('int', 'skin_latest_games'),
			array('int', 'skin_most_popular'),
			array('int', 'skin_avatar_size_width', 'subtext' => $txt['avsize_recommend']),
			array('int', 'skin_avatar_size_height', 'subtext' => $txt['avsize_recommend']),
		'',
			);
		$config_vars = array_merge((array)$config_vars,(array)$a_skin_vars);
	}
	elseif(isset($modSettings['arcadeSkin']) && $modSettings['arcadeSkin'] == 2)
	{
		// Skin B (Defiant)
		$b_skin_vars = array(
			array('check', 'arcadeTabs'),
			array('int', 'arcade_decimal', 'subtext' => $txt['arcade_decimal_recommend']),
			array('check', 'skin_showcatchamps'),
			array('int', 'skin_latest_scores'),
			array('int', 'skin_latest_champs'),
			array('int', 'skin_latest_games'),
			array('int', 'skin_most_popular'),
			array('int', 'skin_avatar_sizeb_width', 'subtext' => $txt['avsize_recommend']),
			array('int', 'skin_avatar_sizeb_height', 'subtext' => $txt['avsize_recommend']),
		'',
			);
		$config_vars = array_merge((array)$config_vars,(array)$b_skin_vars);
	}

	foreach (submitSystemInfo('*') as $id => $system)
	{
		if (!isset($system['get_settings']))
			continue;

		// Load file
		require_once($sourcedir . '/' . $system['file']);

		// Add settings to page
		$config_vars[] = $system['name'];
		$config_vars = array_merge($config_vars, $system['get_settings']());
	}

	if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		checkSession('post');
		$maxScores = !empty($modSettings['arcadeMaxScores']) ? $modSettings['arcadeMaxScores'] : 0;
		saveDBSettings($config_vars);
		writeLog();
		if (!empty($modSettings['arcadeMaxScores']) && (empty($maxScores) || $maxScores > $modSettings['arcadeMaxScores']))
			redirectexit('action=admin;area=arcademaintenance;sa=fixScores');

		redirectexit('action=admin;area=arcade;sa=settings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=arcade;sa=settings;save';
	$context['settings_title'] = $txt['arcade_admin_settings'];
	$context['sub_template'] = 'show_settings';

	prepareDBSettingContext($config_vars);
}

function ArcadeAdminPermission($return_config = false)
{
	global $scripturl, $txt, $modSettings, $context, $settings;

	$config_vars = array(
		array('select', 'arcadePermissionMode',
			array(
				$txt['arcade_permission_mode_none'], $txt['arcade_permission_mode_category'],
				$txt['arcade_permission_mode_game'], $txt['arcade_permission_mode_and_both'],
				$txt['arcade_permission_mode_or_both']
			)
		),
		'',
		array('check', 'arcadePostPermission'),
		array('int', 'arcadePostsPlay'),
		array('int', 'arcadePostsLastDay'),
		array('int', 'arcadePostsPlayAverage'),
		'',
		array('permissions', 'arcade_view', 0, $txt['perm_arcade_view']),
		array('permissions', 'arcade_play', 0, $txt['perm_arcade_play']),
		array('permissions', 'arcade_submit', 0, $txt['perm_arcade_submit']),
		array('permissions', 'arcade_download', 0, $txt['perm_arcade_download']),
		array('permissions', 'arcade_report', 0, $txt['perm_arcade_report']),

	);

	if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		checkSession('post');

		saveDBSettings($config_vars);

		writeLog();

		redirectexit('action=admin;area=arcade;sa=permission');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=arcade;sa=permission;save';
	$context['settings_title'] = $txt['arcade_general_permissions'];
	$context['sub_template'] = 'show_settings';

	prepareDBSettingContext($config_vars);
}

function ArcadeAdminCategory()
{
	global $context, $sourcedir, $txt;

	require_once($sourcedir . '/Arcade.php');
	require_once($sourcedir . '/Subs-ArcadeAdmin.php');

	loadArcade('admin', 'managecategory');

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_category'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_category_desc'];

	$subActions = array(
		'list' => array('ArcadeCategoryList', 'arcade_admin'),
		'edit' => array('ArcadeCategoryEdit', 'arcade_admin'),
		'new' => array('ArcadeCategoryEdit', 'arcade_admin'),
		'save' => array('ArcadeCategorySave', 'arcade_admin'),
		'upload' => array('ArcadeCategoryUpload', 'arcade_admin'),
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	$subActions[$_REQUEST['sa']][0]();
}

function ArcadeCategoryList()
{
	global $db_prefix, $modSettings, $context, $sourcedir, $scripturl, $smcFunc;

	if (isset($_REQUEST['save']))
	{
		checkSession('post');

		asort($_REQUEST['category_order'], SORT_NUMERIC);

		$i = 1;

		if (!empty($_REQUEST['category']))
		{
			$ids = array();
			foreach ($_REQUEST['category'] as $id)
			{
				$ids[] = $id;
				unset($_REQUEST['category_order'][$id]);
			}

			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_categories
				WHERE id_cat IN({array_int:category})',
				array(
					'category' => $ids,
				)
			);
		}

		foreach ($_REQUEST['category_order'] as $id => $dummy)
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_categories
				SET cat_order = {int:order}
				WHERE id_cat = {int:category}',
				array(
					'order' => $i++,
					'category' => $id,
				)
			);
	}

	$request = $smcFunc['db_query']('', '
		SELECT id_cat, cat_name, num_games, cat_order, cat_icon
		FROM {db_prefix}arcade_categories
		ORDER BY cat_order',
		array()
	);

	$context['arcade_category'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['arcade_category'][] = array(
			'id' => $row['id_cat'],
			'name' => $row['cat_name'],
			'href' => $scripturl . '?action=admin;area=arcadecategory;sa=edit;category=' . $row['id_cat'],
			'games' => $row['num_games'],
			'order' => $row['cat_order'],
			'cat_icon' => $row['cat_icon'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Template
	$context['sub_template'] = 'arcade_admin_category_list';
}

function ArcadeCategoryEdit()
{
	global $db_prefix, $modSettings, $context, $sourcedir, $smcFunc, $settings, $user_info, $user_settings;

	$new = false;
	$context['arcade_smf_version'] = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$context['post_max_size'] = return_bytes(ini_get('post_max_size')) / 1048576;

	if ($context['arcade_smf_version'] == 'v2.1')
	{
		$modSettings['cookieTime'] = 3153600;
		createToken('admin', 'post');
	}
	else
		require_once($sourcedir . '/Subs-Auth.php');

	if (isset($_REQUEST['category']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_cat, cat_name, num_games, cat_order, member_groups, cat_icon
			FROM {db_prefix}arcade_categories
			WHERE id_Cat = {int:category}',
			array(
				'category' => $_REQUEST['category'],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['category'] = array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name'],
				'member_groups' => explode(',', $row['member_groups']),
				'cat_icon' => !empty($row['cat_icon']) ? $row['cat_icon'] : '',
			);
		$smcFunc['db_free_result']($request);
	}
	else
	{
		$new = true;

		$context['category'] = array(
			'id' => 'new',
			'name' => '',
			'member_groups' => array(),
			'cat_icon' => '',
		);
	}

	$context['arcade_cat_message'] = !empty($_SESSION['arcade_cat_message']) ? $_SESSION['arcade_cat_message'] : '';
	$context['arcade_cat_file'] = !empty($_SESSION['arcade_cat_file']) ? trim($_SESSION['arcade_cat_file']) : '';
	list ($_SESSION['arcade_cat_message'], $_SESSION['arcade_cat_file'], $_SESSION['arcade_cat_icon']) = array('', '', '');
	$context['category']['cat_icon'] = !empty($context['category']['cat_icon']) ? $context['category']['cat_icon'] : '';
	$context['groups'] = arcadeGetGroups($new ? 'all' : $context['category']['member_groups']);
	if ((!empty($context['arcade_cat_file'])) && in_array(substr($context['arcade_cat_file'], -4), array('.gif', '.png', '.jpg')))
	{
		if (file_exists($settings['default_theme_dir'] . '/images/arc_icons/' . $context['arcade_cat_file']))
			$_SESSION['arcade_cat_icon'] = $context['arcade_cat_file'];
		$context['arcade_cat_file'] = '';
	}
	elseif ((!empty($context['category']['cat_icon'])) && in_array(substr($context['category']['cat_icon'], -4), array('.gif', '.png', '.jpg')))
	{
		$_SESSION['arcade_cat_icon'] = '';
		if (file_exists($settings['default_theme_dir'] . '/images/arc_icons/' . $context['category']['cat_icon']))
			$_SESSION['arcade_cat_icon'] = $context['category']['cat_icon'];
		$context['category']['cat_icon'] = '';
	}

	// Template
	$context['sub_template'] = 'arcade_admin_category_edit';
}


function ArcadePdlSettings($return_config = false)
{
	global $scripturl, $txt, $modSettings, $context, $settings, $sourcedir;
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['pdl_settings_desc'];

	$config_vars = array(
		array('check', 'arcadeEnablePosting'),
		array('int', 'gamesBoard'),
		array('int', 'arcadePosterid'),
		array('check', 'arcadeEnableIframe'),
		array('large_text', 'gamesMessage', 8),
		'',
		array('check', 'arcadeEnableDownload'),
		array('check', 'arcadeDisableArchive'),
		array('text', 'arcadeDownPass'),
		array('int', 'arcadeDownPost'),
		array('int', 'pdl_DownMax'),
		'',
		array('check', 'arcadeEnableReport'),
		array('check', 'arcadeEnableGameDisable'),
		'',
		array('check', 'arcadeAdjustType'),
	);

if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		checkSession('post');
		saveDBSettings($config_vars);
		writeLog();
		redirectexit('action=admin;area=arcade;sa=pdl_settings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=arcade;sa=pdl_settings;save';
	$context['settings_title'] = $txt['pdl_admin_settings'];
	$context['sub_template'] = 'show_settings';

	prepareDBSettingContext($config_vars);
}

function ArcadePdlReports($return_config = false)
{
	global $scripturl, $txt, $modSettings, $context, $settings, $sourcedir, $db_prefix, $smcFunc;
	$context['arcade']['game_reports'] = array();
	$game = !empty($_REQUEST['game']) ? (int)$_REQUEST['game'] : 0;
	$where = !empty($game) ? 'pdl.pdl_gameid = {int:gameid}' . ' AND ': '';

	/* Reported Games */
	$request = $smcFunc['db_query']('', '
		SELECT pdl.pdl_gameid, pdl.game_name, pdl.report_day, pdl.report_year, pdl.report_id, pdl.user_id, pdl.download_count, pdl.download_disable, mem.member_name,
			game.enabled, game.game_file
		FROM {db_prefix}arcade_pdl2 AS pdl
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = pdl.user_id)
		LEFT JOIN {db_prefix}arcade_games AS game ON (game.id_game = pdl.pdl_gameid)
		WHERE ' . $where . '(pdl.report_id > 0 OR pdl.download_disable > 0)
		ORDER BY pdl.pdl_gameid',
		array('gameid' => $game)
	);

	$i = 0;
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$checkgame = false;
		if (empty($row['enabled'])) {$row['enabled'] = 0;}
		if (empty($row['game_file'])) {$row['game_file'] = false;}
		if ($row['enabled'] == 0 && $row['game_file'] == false) {$checkgame = 'DELETE';}
		if (empty($row['game_name'])) {$row['game_name'] = false;}
		if (empty($row['report_day'])) {$row['report_day'] = 0;}
		if (empty($row['report_year'])) {$row['report_year'] = 0;}
		if (empty($row['report_id'])) {$row['report_id'] = 0;}
		if (empty($row['member_name'])) {$row['member_name'] = false;}
		if (empty($row['user_id'])) {$row['user_id'] = 0;}
		if (empty($row['download_count'])) {$row['download_count'] = 0;}
		if (empty($row['download_disable'])) {$row['download_disable'] = 0;}
		if (empty($row['pdl_gameid'])) {$row['pdl_gameid'] = 0;}

		$context['arcade']['game_reports'][$row['pdl_gameid']] = array(
			'check' => $checkgame,
			'gameid' => $row['pdl_gameid'],
			'name' => $row['game_name'],
			'day' => $row['report_day'],
			'year' => $row['report_year'],
			'user' => $row['user_id'],
			'count' => $row['download_count'],
			'disable' => $row['download_disable'],
			'report' => $row['report_id'],
			'edit_game' => $scripturl . '?action=admin;area=managegames;sa=edit;game=' . $row['pdl_gameid'],
			'user_profile' => $scripturl . '?action=profile;u=' . $row['user_id'],
			'user_id' => $row['user_id'],
			'user_name' => $row['member_name'],
		);
	}
	$smcFunc['db_free_result']($request);

	if (isset($_REQUEST['save']))
	{
		/*  Are there reports to remove from the list?  */
		if (isset($_POST['delete']) && is_array($_POST['delete']))
		{
			$delete_reports = $_POST['delete'];
			foreach ($context['arcade']['game_reports'] as $games)
			{
				$i = (int)$games['gameid'];
				/* Remove the flag if it was opted */
				foreach ($delete_reports as $check_report)
				if ((int)$check_report == $i)
					{
						$request = $smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_pdl2
						SET report_id = 0, report_day = 0, report_year = 0
						WHERE pdl_gameid = {int:game}',
						array('game' => $i,));

					}
			}
		}
		/*  Are there download settings to toggle?  */
		elseif (isset($_POST['toggle']) && is_array($_POST['toggle']))
		{
			$toggle_dls = $_POST['toggle'];
			foreach ($context['arcade']['game_reports'] as $games)
			{
				$toggle = 1;
				if ((int)$games['disable'] > 0) {$toggle = 0;}
				$i = (int)$games['gameid'];
				/* Change the flag if it was opted */
				foreach ($toggle_dls as $check_dl)
				if ((int)$check_dl == $i)
					{
						$request = $smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_pdl2
						SET download_disable = {int:change}
						WHERE pdl_gameid = {int:game}',
						array('game' => $i,
							   'change' => $toggle,));

					}
			}
		}
		/*   Are there -disabled/no file data- games to remove from the list? (also removes download stats)  */
		elseif (isset($_POST['maintain']))
		{
			foreach ($context['arcade']['game_reports'] as $games)
			{
				/* Drop the entry if it isn't in the arcade_games table */
				if ($games['check'] == 'DELETE')
				{
					$i = (int)$games['gameid'];
					$tableName = 'arcade_pdl2';
					$request = $smcFunc['db_query']('', "DELETE FROM `{db_prefix}$tableName` WHERE `{db_prefix}$tableName`.`pdl_gameid` = '$i'");

				}
			}
		}
		else
			{$delete_reports = array(); $toggle_dls = array();}
		redirectexit('action=admin;area=arcade;sa=pdl_reports');
	}
	$context['template_layers'][] = 'arcade_reports';
	$context['sub_template'] = 'arcade_reports';
	$context['page_title'] = sprintf($txt['pdl_admin_reports']);
	$context['post_url'] = $scripturl . '?action=admin;area=arcade;sa=pdl_reports;save';
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=arcade;action=admin;area=arcade;sa=pdl_reports;sesc=' . $context['session_id'],
		'name' => $txt['pdl_admin_reports'],
	);
	loadTemplate('ArcadeReports');
	return;
}
function ArcadeCategorySave()
{
	global $db_prefix, $modSettings, $context, $sourcedir, $smcFunc;
	$smfVersion = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	checkSession('post');

	if ($smfVersion == 'v2.1')
		validateToken('admin', 'post', false);

	$memberGroups = array();

	if (!empty($_REQUEST['groups']))
		foreach ($_REQUEST['groups'] as $k => $id)
			$memberGroups[] = (int) $id;

	$_REQUEST['category_icon'] = !empty($_SESSION['arcade_cat_icon']) ? $_SESSION['arcade_cat_icon'] : '';
	$_SESSION['arcade_cat_file'] = '';


	if ($_REQUEST['category'] == 'new')
	{
		$request = $smcFunc['db_query']('', '
			SELECT MAX(cat_order)
			FROM {db_prefix}arcade_categories'
		);

		list ($max) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$smcFunc['db_insert']('insert',
			'{db_prefix}arcade_categories',
			array('cat_name' => 'string', 'member_groups' => 'string', 'cat_order' => 'int', 'cat_icon' => 'string',),
			array($_REQUEST['category_name'], implode(',', $memberGroups), ++$max, $_REQUEST['category_icon']),
			array('id_cat')
		);
	}
	else
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_categories
			SET
				cat_name = {string:name},
				member_groups = {string:groups},
				cat_icon = {string:icon}
			WHERE id_cat = {int:category}',
			array(
				'name' => $_REQUEST['category_name'],
				'groups' => implode(',', $memberGroups),
				'category' => $_REQUEST['category'],
				'icon' => $_REQUEST['category_icon'],
			)
		);
	}

	redirectexit('action=admin;area=arcadecategory');
}

function ArcadeCategoryUpload()
{
	global $smcFunc, $modSettings, $settings, $sourcedir, $txt, $context;

	isAllowedTo('arcade_admin');
	$smfVersion = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$context['post_max_size'] = return_bytes(ini_get('post_max_size')) / 1048576;
	$_REQUEST['category'] = isset($_REQUEST['upcat']) ? (int)$_REQUEST['upcat'] : 0;
	$_SESSION['arcade_cat_file'] = '';
	$width = (!empty($modSettings['arcade_catWidth'])) && (int)$modSettings['arcade_catWidth'] > 0 ? (int)$modSettings['arcade_catWidth'] : 20;
	$height = (!empty($modSettings['arcade_catHeight'])) && (int)$modSettings['arcade_catHeight'] > 0 ? (int)$modSettings['arcade_catHeight'] : 23;
	checkSession('post');

	if ($smfVersion == 'v2.1')
	{
		$modSettings['cookieTime'] = 3153600;
		createToken('admin', 'post');
	}

	$postVar = !empty($_FILES['attachment']) ? $_FILES['attachment'] : (!empty($_FILES['Filedata']) ? $_FILES['Filedata'] : array());
	list($fileExists, $newname) = array(0, '');

	if (empty($postVar) && isset($_REQUEST['category']))
		ArcadeCategoryEdit();
	elseif (empty($postVar))
		redirectexit('action=admin;area=arcadecategory');

	foreach ($postVar['tmp_name'] as $n => $dummy)
	{
		if ($postVar['name'][$n] == '')
			continue;

		$postVar['name'][$n] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $postVar['name'][$n]);
		$newname = trim(basename($postVar['name'][$n]));
		$target = $settings['default_theme_dir'] . '/images/arc_icons';
		$tmp_name = $postVar['tmp_name'][$n];

		if (mb_substr($newname, -4) !== '.jpg' && mb_substr($newname, -4) !== '.png' && mb_substr($newname, -4) !== '.gif')
			continue;

		if (!is_writable($target))
			if (!chmod($target, 0755))
				$_SESSION['arcade_cat_message'] = (sprintf($txt['arcade_not_writable'], $target));

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
					sleep(3);
				}
				fclose($in);
			}
			fclose($com);

			if (!file_exists($target . '/' . $newname))
				$_SESSION['arcade_cat_message'] = ($txt['arcade_upload_file']);

			@chmod($target . '/' . $newname, 0666);
			ArcadeImageResize($target . '/' . $newname, $target . '/' . $newname, $width, $height, 1);
			$_SESSION['arcade_cat_file'] = $newname;
		}
		else
		{
			$fileExists = 1;
			ArcadeImageResize($target . '/' . $newname, $target . '/' . $newname, $width, $height, 1);
			$_SESSION['arcade_cat_file'] = $newname;
		}
	}

	if (!empty($newname) && empty($fileExists))
		$_SESSION['arcade_cat_message'] = sprintf($txt['arcade_upload_complete'] ,$newname);
	elseif (!empty($fileExists))
		$_SESSION['arcade_cat_message'] = sprintf($txt['arcade_upload_exists'], $newname);
	else
		$_SESSION['arcade_cat_message'] = $txt['arcade_upload_nofile'];

	if(isset($_REQUEST['category']) && !empty($_SESSION['arcade_cat_file']))
		ArcadeCategoryEdit();
	else
		redirectexit('action=admin;area=arcadecategory');
}

function ArcadeImageResize($src, $dst, $width, $height, $crop=0)
{
	if(!list($w, $h) = getimagesize($src))
		return false;

	$type = strtolower(substr(strrchr($src,"."),1));
	if($type == 'jpeg')
		$type = 'jpg';

	switch($type)
	{
		case 'gif':
			$img = imagecreatefromgif($src);
			break;
		case 'jpg':
			$img = imagecreatefromjpeg($src);
			break;
		case 'png':
			$img = imagecreatefrompng($src);
			break;
		default:
			return false;
	}

	if($crop)
	{
		if($w < $width or $h < $height)
			return false;
		$ratio = max($width/$w, $height/$h);
		$h = $height / $ratio;
		$x = ($w - $width / $ratio) / 2;
		$w = $width / $ratio;
	}
	else
	{
		if($w < $width and $h < $height)
			return false;
		$ratio = min($width/$w, $height/$h);
		$width = $w * $ratio;
		$height = $h * $ratio;
		$x = 0;
	}

	$new = imagecreatetruecolor($width, $height);

	if($type == "gif" || $type == "png")
	{
		imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
		imagealphablending($new, false);
		imagesavealpha($new, true);
	}

	imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);

	switch($type)
	{
		case 'gif':
			imagegif($new, $dst);
			break;
		case 'png':
			imagepng($new, $dst);
			break;
	}

	return true;
}
?>