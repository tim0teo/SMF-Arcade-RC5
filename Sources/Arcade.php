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

/*	This file handles Arcade and loads required files.

	void Arcade()
		- Loads correct file based on subaction

	void loadArcade([mode = normal])
		- Initializes Arcade
		- Called by Arcade() and Arcade Admin functions

	void arcadeLogin()
		- Shows a login prompt for guests
		- Redirects to arcade with specific action and/or subActions

*/

function Arcade()
{
	global $context, $scripturl, $txt, $sourcedir, $modSettings;

	// Do we have permission?
	isAllowedTo('arcade_view');

	// Load Arcade
	loadArcade('normal');

	// Fatal error if Arcade is disabled
	if (empty($modSettings['arcadeEnabled']))
		fatal_lang_error('arcade_disabled', false);

	// Information for actions (file, function, [permission])
	$subActions = array(
		// ArcadeArena.php
		'arena' => array('ArcadeArena.php', 'ArcadeMatchList'),
		'newMatch' => array('ArcadeArena.php', 'ArcadeNewMatch', 'arcade_create_match'),
		'newMatch2' => array('ArcadeArena.php', 'ArcadeNewMatch2', 'arcade_create_match'),
		'viewMatch' => array('ArcadeArena.php', 'ArcadeViewMatch'),
		// ArcadeList.php
		'list' => array('ArcadeList.php', 'ArcadeList'),
		'suggest' => array('ArcadeList.php', 'ArcadeXMLSuggest'),
		'search' => array('ArcadeList.php', 'ArcadeList'),
		'rate' => array('ArcadeList.php', 'ArcadeRate'),
		'favorite' => array('ArcadeList.php', 'ArcadeFavorite'),
		// ArcadeGame.php
		/* Game Popup in Iframe  */
		'popup' => array('ArcadePopup-smf2.php', 'ArcadePopup'),
		'play' => array('ArcadeGame.php', 'ArcadePlay', 'arcade_play'),
		'highscore' => array('ArcadeGame.php', 'ArcadeHighscore'),
		'save' => array('ArcadeGame.php', 'ArcadeSave_Guest'),
		// ArcadeStats.php
		'stats' => array('ArcadeStats.php', 'ArcadeStatistics'),
		'submit' => array('ArcadeGame.php', 'ArcadeSubmit'),
		// Arcade Online
		'online' => array('ArcadeOnline.php', 'ArcadeOnline'),
		// Advanced
		'download' => array('ArcadeDownload.php', 'ArcadeDownload'),
		'report' => array('ArcadeReport.php', 'ArcadeReport'),
		'shout' => array('Subs-ArcadeSkinB.php', 'ArcadeShout'),
		// IBP Submit
		'ibpverify' => array('Submit-ibp.php', 'ArcadeVerifyIBP'),
		'ibpsubmit2' => array('ArcadeGame.php', 'ArcadeSubmit'),
		'ibpsubmit3' => array('ArcadeGame.php', 'ArcadeSubmit'),
		// v2 Submit
		'v2Start' => array('Submit-v2game.php', 'ArcadeV2Start'),
		'v2Hash' => array('Submit-v2game.php', 'ArcadeV2Hash'),
		'v2Score' => array('Submit-v2game.php', 'ArcadeV2Score'),
		'v2Submit' => array('ArcadeGame.php', 'ArcadeSubmit'),
		// v3Arcade
		'vbSessionStart' => array('Submit-v3arcade.php', 'ArcadeVbStart'),
		'vbPermRequest' => array('Submit-v3arcade.php', 'ArcadeVbPermRequest'),
		'vbBurn' => array('ArcadeGame.php', 'ArcadeSubmit'),
	);

	if (empty($modSettings['arcadeArenaEnabled']))
		unset($subActions['arena'], $subActions['newMatch'], $subActions['newMatch2'], $subActions['viewMatch']);

	// Fix for broken games which do not send sa/do=submit
	if (isset($_POST['game']) && isset($_POST['score']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'submit';
	// Short urls like index.php?game=1 or index.php/game,1.html
	elseif (isset($_REQUEST['game']) && is_numeric($_REQUEST['game']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'play';
	elseif (isset($_REQUEST['match']) && is_numeric($_REQUEST['match']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'viewMatch';
	// Let Custom ("php games") do ajax/etc magic
	elseif (isset($_REQUEST['game']) && isset($_REQUEST['xml']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'custData';

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	$context['curved'] = strpos($modSettings['smfVersion'], '2.1') !== false ? true : $context['curved'];
	$context['arcade_smf_version'] = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$context['current_arcade_sa'] = !empty($_REQUEST['sa']) ? $_REQUEST['sa'] : 'list';
	$_SESSION['current_cat'] = !empty($_SESSION['current_cat']) ? $_SESSION['current_cat'] : 'all';
	$_SESSION['arcade_sortby'] = !empty($_SESSION['arcade_sortby']) ? $_SESSION['arcade_sortby'] : 'a2z';
	$sort = ($_SESSION['current_cat'] == 0 || $_SESSION['current_cat'] == 'all') && $_SESSION['arcade_sortby'] == 'a2z' ? '' : ';sortby=reset';

	$context['arcade_tabs']['arcade'] = array(
		'text' => 'arcade',
		'image' => 'arcade.gif',
		'url' => $scripturl . '?action=arcade' . $sort,
		'active' => in_array($context['current_arcade_sa'], array('list', 'online', 'highscore', 'play')) ? true : null,
		'lang' => true
	);

	if (!empty($modSettings['arcadeArenaEnabled']))
		$context['arcade_tabs']['arcade_arena'] = array(
			'text' => 'arcade_arena',
			'image' => 'arcade_arena.gif',
			'url' => $scripturl . '?action=arcade;sa=arena',
			'active' => in_array($context['current_arcade_sa'], array('arena', 'newMatch', 'newMatch2', 'viewMatch')) ? true : null,
			'lang' => true
		);

	$context['arcade_tabs']['arcade_stats'] = array(
		'text' => 'arcade_stats',
		'image' => 'arcade_stats.gif',
		'url' => $scripturl . '?action=arcade;sa=stats',
		'active' => in_array($context['current_arcade_sa'], array('stats')) ? true : null,
		'lang' => true
	);

	if (allowedTo('arcade_admin'))
		$context['arcade_tabs']['arcade_administrator'] = array(
			'text' => 'arcade_administrator',
			'image' => 'arcade_administrator.gif',
			'url' => $scripturl . '?action=admin;area=arcade',
			'lang' => true
		);

	if (!in_array($_REQUEST['sa'], array('highscore', 'comment')) && isset($_SESSION['arcade']['highscore']))
		unset($_SESSION['arcade']['highscore']);

	// Check permission if needed
	if (isset($subActions[$_REQUEST['sa']][2]))
		isAllowedTo($subActions[$_REQUEST['sa']][2]);

	require_once($sourcedir . '/' . $subActions[$_REQUEST['sa']][0]);
	!isset($_SESSION['current_cat']) ? $_SESSION['current_cat'] = 'all' : '';
    isset($_REQUEST['category']) ? $_SESSION['current_cat'] = $_REQUEST['category'] : $_REQUEST['category'] = $_SESSION['current_cat'];
	$_REQUEST['category'] = !empty($_REQUEST['current_cat']) ? ArcadeSpecialChars($_REQUEST['current_cat'], 'cat'): $_SESSION['current_cat'];
	arcade_log_online();
	$subActions[$_REQUEST['sa']][1]();
}


function template_main()
{
	/*
	** This patch function stops the template error for some games
	** Just leave it empty
	*/
}


function loadArcade($mode = 'normal', $index = '')
{
	global $db_prefix, $scripturl, $txt, $modSettings, $context, $settings, $sourcedir, $user_info, $smcFunc, $boarddir, $arcade_version, $arcade_lang_version;

	if(!function_exists('loadClassFile'))
		require_once($sourcedir . '/Subs-ArcadeClass.php');

    /* Are we using the curve or curve type theme?  */
    file_exists($settings['actual_theme_dir'] . '/images/theme/main_block.png') ? $context['curved'] = true : $context['curved'] = false;


	if (!empty($arcade_version))
		return;

	$arcade_version = $modSettings['arcadeVersion'];
	$_SESSION['arcade_sortby'] = !empty($_SESSION['arcade_sortby']) ? $_SESSION['arcade_sortby'] : '';
	$arcade_lang_version = '2.5';
	$context['arcade'] = array();
	$context['arcade_smf_version'] = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$user_info['arcade_settings'] = loadMyArcadeSettings($user_info['id']);
	$arcadeSkin = $user_info['arcade_settings']['skin'];
	require_once($sourcedir . '/Subs-ArcadePlus.php');
	require_once($sourcedir . '/Subs-Arcade.php');

	if (($context['arcade']['stats'] = cache_get_data('arcade-stats', 180)) == null)
	{
		$context['arcade']['stats'] = array();
		// How many games?
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS games
			FROM {db_prefix}arcade_games
			WHERE enabled = 1',
			array()
		);
		$context['arcade']['stats'] += $smcFunc['db_fetch_assoc']($result);
		$smcFunc['db_free_result']($result);

		if (!empty($modSettings['arcadeShowInfoCenter']))
		{
			require_once($sourcedir . '/ArcadeStats.php');
			$context['arcade']['stats']['best_player'] = ArcadeStats_BestPlayers(1);
			$context['arcade']['stats']['longest_champion'] = ArcadeStats_LongestChampions(1, null, 'current');
			$context['arcade']['stats']['most_played'] = ArcadeStats_MostPlayed(1);
		}

		cache_put_data('arcade-stats', $context['arcade']['stats'], 180);
	}

	switch ($arcadeSkin)
	{
		case 2:
			require_once($sourcedir . '/Subs-ArcadeSkinB.php');
			require_once($sourcedir . '/ArcadeStats.php');
			$context['html_headers'] .= '<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade-skin-b.js?rc4"></script>';
			if ($mode == 'normal' || $mode == 'arena')
			{
				$context['arcade_defiant']['per_line'] = 4;
				$context['arcade_defiant']['cat_width'] = 20;
				$context['arcade_defiant']['cat_height'] = 20;
				$context['page_title'] = $txt['arcade_game_list'];
				$width = !empty($modSettings['skin_avatar_size_width']) && (int)$modSettings['skin_avatar_size_width'] > 0 ? (int)$modSettings['skin_avatar_size_width'] : 50;
				$height = !empty($modSettings['skin_avatar_size_height']) && (int)$modSettings['skin_avatar_size_height'] > 0 ? (int)$modSettings['skin_avatar_size_height'] : 50;
				$context['arcade_user_avatar'] = (!empty($context['user']['avatar']['href'])) ? ArcadeSizer($context['user']['avatar']['href'], $width, $height) : array($width, $height);
				loadTemplate('ArcadeSkinB');
			}
			break;
		case 1:
			require_once($sourcedir . '/Subs-ArcadeSkinA.php');
			if ($mode == 'normal' || $mode == 'arena')
				loadTemplate('ArcadeSkinA');
			break;
		default:
			require_once($sourcedir . '/Subs-ArcadeSkinA.php');
			if ($mode == 'normal' || $mode == 'arena')
				loadTemplate('Arcade');
	}

	// Load language
	loadLanguage('Arcade');
	loadLanguage('ArcadeSkinA');

	// Permission query
	arcadePermissionQuery();

	// Normal mode
	if ($mode == 'normal' || $mode == 'arena')
	{
		if (empty($modSettings['arcadeEnabled']))
			return false;

		$context['games_per_page'] = !empty($user_info['arcade_settings']['games_per_page']) ? $user_info['arcade_settings']['games_per_page'] : $modSettings['gamesPerPage'];
		$context['scores_per_page'] = !empty($user_info['arcade_settings']['scores_per_page']) ? $user_info['arcade_settings']['scores_per_page'] : $modSettings['scoresPerPage'];

		// Arcade javascript & css
		$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade.js?rc4"></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade-skin-a.js?rc4"></script>
		<link href="' . $settings['default_theme_url'] . '/css/arcade-skin-b.css?rc4" rel="stylesheet" type="text/css" />';

		// Add Arcade to link tree
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=arcade',
			'name' => $txt['arcade'],
		);

		// What I can do?
		$context['arcade']['can_play'] = allowedTo('arcade_play');
		$context['arcade']['can_favorite'] = !empty($modSettings['arcadeEnableFavorites']) && !$user_info['is_guest'];
		$context['arcade']['can_rate'] = !empty($modSettings['arcadeEnableRatings']) && !$user_info['is_guest'];
		$context['arcade']['can_submit'] = allowedTo('arcade_submit');
		$context['arcade']['can_comment_own'] = allowedTo('arcade_comment_own');
		$context['arcade']['can_comment_any'] = allowedTo('arcade_comment_any');
		$context['arcade']['can_admin_arcade'] = allowedTo('arcade_admin');
		$context['arcade']['can_create_match'] = allowedTo('arcade_create_match');
		$context['arcade']['can_join_match'] = allowedTo('arcade_join_match');

		// Or can I? (do I have enought posts etc.)
		PostPermissionCheck();

		// Finally load Arcade Settings
		LoadArcadeSettings();

		if (!isset($_REQUEST['xml']))
			$context['template_layers'][] = 'Arcade';
	}
	elseif ($mode == 'profile')
		loadTemplate('ArcadeProfile', array('arcade', 'forum'));

	// Admin mode
	elseif ($mode == 'admin')
	{
		loadTemplate('ArcadeAdmin');
		loadLanguage('ArcadeAdmin');
		isAllowedTo('arcade_admin');

		$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade.js?rc4"></script>';

		// Update games database?
		if (file_exists($boarddir . '/Games.xml'))
		{
			loadClassFile('Class-Package.php');

			$games = new xmlArray(file_get_contents($boarddir . '/Games.xml'));
			$database = $games->path('smf/database');
			$database = $database->to_array();
			$xmlGames = $games->set('smf/game');

			if (!empty($modSettings['arcadeGameDatabaseVersion']) && $modSettings['arcadeGameDatabaseVersion'] > $database['version'])
				break;

			$games = array();

			foreach ($xmlGames as $game)
				$games[] = array(
					$game->fetch('id'),
					$game->fetch('name'),
					$game->fetch('description'),
					$game->fetch('url/info'),
					$game->fetch('url/download'),
				);

			$smcFunc['db_insert']('replace',
				'{db_prefix}arcade_game_info',
				array(
					  'internal_name' => 'string',
					  'game_name' => 'string',
					  'description' => 'string',
					  'info_url' => 'string',
					  'download_url' => 'string',
				),
				$games,
				array('internal_name')
			);

			updateSettings(array(
				'arcadeGameDatabaseVersion' => $database['version'],
				'arcadeGameDatabaseUpdate' => $database['update'],
			));

			@unlink($boarddir . '/Games.xml');
		}

		$context['template_layers'][] = 'ArcadeAdmin';
		$context['page_title'] = $txt['arcade_admin_title'];
	}
}

function arcadeLogin()
{
	global $scripturl, $txt, $user_info, $context, $modSettings;

	$sub_actions = array(
		'arena',
		'newMatch',
		'newMatch2',
		'viewMatch',
		'highscore',
		'play',
	);

	$_REQUEST['sa'] = (!empty($_REQUEST['sa'])) ? trim($_REQUEST['sa']) : '';
	$sa = (!empty($_REQUEST['sa'])) && in_array($_REQUEST['sa'], $sub_actions) ? $_REQUEST['sa'] : '';
	$game = isset($_REQUEST['game']) ? 'game=' . abs((int)$_REQUEST['game']) . ';' : '';
	$match = isset($_REQUEST['match']) ? 'match=' . abs((int)$_REQUEST['match']) . ';' : '';
	$subaction = 'sa=' . $sa . ';' . $match . $game;
	$anchor = in_array($sa, array('play', 'highscore')) && $sa == 'play' ? ';#playgame' : in_array($sa, array('play', 'highscore')) && $sa == 'highscore' ? ';#highscore' : '';
	$_SESSION['old_url'] = $scripturl . '?action=arcade;' . $subaction;
	$context['arcade_sub'] = (isset($_REQUEST['hs'])) ? 'score' : 'play';
	$context['arcade_smf_version'] = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

	if (empty($match) && empty($game) && empty($sa))
		redirectexit();

	if (!$user_info['is_guest'])
		redirectexit('action=arcade;' . $subaction . $anchor);

	// Create a login token for SMF 2.1.x
	if ($context['arcade_smf_version'] == 'v2.1')
		createToken('login');

	$context['page_title'] = $txt['arcade_login_title'];
	$context['sub_template'] = 'arcade_login';
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=ingressarcade;' . $subaction,
		'name' => $txt['arcade_login_top'],
	);
	loadTemplate('Arcade');
}

function arcade_log_online()
{
	global $smcFunc, $user_info, $context;
	$time = time();
	$checkIp = !empty($user_info['ip']) ? trim($user_info['ip']) : !empty($user_info['ip2']) ? trim($user_info['ip2']) : 0;
	list($guests, $users, $action, $userIp) = array(0, 0, 0, array());
	$game = isset($_REQUEST['game']) ? (int)$_REQUEST['game'] : 0;
	$sa = !empty($_REQUEST['sa']) ? trim($_REQUEST['sa']) : 'index';

	switch ($sa)
	{
		case 'play':
			$action = 1;
			break;
		case 'highscore':
			$action = 2;
			break;
		case 'arena':
			$action = 3;
			break;
		case 'online':
			$action = 4;
			break;
		case 'viewMatch':
			$action = 5;
			break;
		case 'newMatch':
			$action = 6;
			break;
		case 'newMatch2':
			$action = 6;
			break;
		default:
			$action = 0;
	}

	// count guests online for user comparison
	$request = $smcFunc['db_query']('', '
		SELECT online_ip, online_time
		FROM {db_prefix}arcade_guest_data
		WHERE online_ip = {string:ip} AND {int:now} - online_time < 600',
		array(
			'ip' => (string)$checkIp,
			'now' => $time
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$userIp[] = $row['online_ip'];
	$smcFunc['db_free_result']($request);

	// remove user & guest values that refresh the page or are gone over 10 minutes
	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_member_data
		WHERE id_member = {int:member} OR {int:now} - online_time >= 600',
		array(
			'member' => $user_info['id'],
			'now' => $time
		)
	);

	if (!$user_info['is_guest'])
		$request = $smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_guest_data
			WHERE online_ip = {string:ip} OR {int:now} - online_time >= 600',
			array(
				'ip' => !in_array($checkIp, $userIp) ? $checkIp : '256.0.0.0',
				'now' => $time
			)
		);

	// insert user or guest into the online log
	if ($user_info['is_guest'] && !empty($checkIp))
	{
		$request = $smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_guest_data
			WHERE online_ip = {string:ip} OR {int:now} - online_time >= 600',
			array(
				'ip' => $checkIp,
				'now' => $time
			)
		);

		list($time, $show, $ip) = array(time(), '0', $checkIp);

		$smcFunc['db_insert']('insert',
			'{db_prefix}arcade_guest_data',
			array(
				'online_ip' => 'string',
				'online_time' => 'int',
				'show_online' => 'int',
				'current_action' => 'int',
				'current_game' => 'int',
			),
			array(
				$ip,
				$time,
				$show,
				$action,
				$game,
			),
			array()
		);
	}
	else
	{
		list($userid, $time, $show, $name, $color) = array($user_info['id'], time(), '0', $user_info['name'], '');

		$request = $smcFunc['db_query']('', '
			SELECT
				mem.id_member, mem.real_name, mem.member_name, mem.show_online,
				mg.online_color, mg.id_group, mg.group_name
			FROM {db_prefix}members AS mem
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = 0 THEN mem.id_post_group ELSE mem.id_group END)
			WHERE mem.id_member = {int:member}',
			array(
				'member' => $user_info['id'],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$color = !empty($row['online_color']) ? $row['online_color'] : '';
			$show = !empty($row['show_online']) ? 1 : 0;
		}
		$smcFunc['db_free_result']($request);

		$smcFunc['db_insert']('insert',
			'{db_prefix}arcade_member_data',
			array(
				'id_member' => 'int',
				'online_ip' => 'string',
				'online_time' => 'int',
				'show_online' => 'int',
				'online_name' => 'string',
				'online_color' => 'string',
				'current_action' => 'int',
				'current_game' => 'int',
			),
			array(
				$userid,
				$checkIp,
				$time,
				$show,
				$name,
				$color,
				$action,
				$game,
			),
			array()
		);
	}
}

function loadMyArcadeSettings($memID = 0)
{
	global $smcFunc, $user_info, $modSettings;

	$modSettings['arcadeSkin'] = !empty($modSettings['arcadeSkin']) ? (int)$modSettings['arcadeSkin'] : 0;
	$modSettings['arcadeList'] = !empty($modSettings['arcadeList']) ? (int)$modSettings['arcadeList'] : 0;
	if ($memID == 0 || $user_info['is_guest'])
		return array('skin' => $modSettings['arcadeSkin'], 'list' => $modSettings['arcadeList']);

	// Default
	$arcadeSettings = array();

	$request = $smcFunc['db_query']('', '
			SELECT id_member, arena_invite, arena_match_end, arena_new_round, champion_email, champion_pm,
			games_per_page, new_champion_any, new_champion_own, scores_per_page, skin, list
			FROM {db_prefix}arcade_members
			WHERE id_member = {int:member}
			LIMIT 1',
			array(
				'member' => $memID,
			)
		);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$arcadeSettings = array(
			'id_member' => $row['id_member'],
			'arena_invite' => !empty($row['arena_invite']) ? $row['arena_invite'] : 0,
			'arena_match_end' => !empty($row['arena_match_end']) ? $row['arena_match_end'] : 0,
			'arena_new_round' => !empty($row['arena_new_round']) ? $row['arena_new_round'] : 0,
			'champion_email' => !empty($row['champion_email']) ? $row['champion_email'] : 0,
			'champion_pm' => !empty($row['champion_pm']) ? $row['champion_pm'] : 0,
			'games_per_page' => !empty($row['games_per_page']) ? $row['games_per_page'] : $modSettings['gamesPerPage'],
			'new_champion_any' => !empty($row['new_champion_any']) ? $row['new_champion_any'] : 0,
			'new_champion_own' => !empty($row['new_champion_own']) ? $row['new_champion_own'] : 0,
			'scores_per_page' => !empty($row['scores_per_page']) ? $row['scores_per_page'] : $modSettings['scoresPerPage'],
			'skin' => !empty($row['skin']) && allowedTo('arcade_skin') ? $row['skin'] - 1 : $modSettings['arcadeSkin'],
			'list' => !empty($row['list']) && allowedTo('arcade_list') ? $row['list'] - 1 : $modSettings['arcadeList'],
		);
	}
	$smcFunc['db_free_result']($request);

	if (empty($arcadeSettings))
		return array(
			'id_member' => $memID,
			'arena_invite' => 0,
			'arena_match_end' => 0,
			'arena_new_round' => 0,
			'champion_email' => 0,
			'champion_pm' => 0,
			'games_per_page' => $modSettings['gamesPerPage'],
			'new_champion_any' => 0,
			'new_champion_own' => 0,
			'scores_per_page' => $modSettings['scoresPerPage'],
			'skin' => $modSettings['arcadeSkin'],
			'list' => $modSettings['arcadeList'],
		);



	return $arcadeSettings;
}
?>