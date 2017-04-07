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

function arcade_get_url($params = array())
{
	global $scripturl, $modSettings;

	// Running in "standalone" mode WITH rewrite
	if (!empty($modSettings['arcadeStandalone']) && $modSettings['arcadeStandalone'] == 2)
	{
		// Main Page? Too easy
		if (empty($params))
			return $modSettings['arcadeStandaloneUrl'] . '/';

		$query = '';

		foreach ($params as $p => $value)
		{
			if ($value === null)
				continue;

			if (!empty($query))
				$query .= ';';
			else
				$query .= '?';

			if (is_int($p))
				$query .= $value;
			else
				$query .= $p . '=' . $value;
		}

		return $modSettings['arcadeStandaloneUrl'] . '/' . $query;
	}
	// Running in "standalone" mode without rewrite or standard mode
	else
	{
		$return = '';

		if (empty($params) && empty($modSettings['arcadeStandaloneUrl']))
			$params['action'] = 'arcade';

		foreach ($params as $p => $value)
		{
			if ($value === null)
				continue;

			if (!empty($return))
				$return .= ';';
			else
				$return .= '?';

			if (is_int($p))
				$return .= $value;
			else
				$return .= $p . '=' . $value;
		}

		if (!empty($modSettings['arcadeStandaloneUrl']))
			return $modSettings['arcadeStandaloneUrl'] . $return;
		else
			return $scripturl . $return;
	}
}

function arcadePermissionQuery()
{
	global $scripturl, $modSettings, $context, $user_info;

	// No need to check for admins
	if (allowedTo('arcade_admin'))
	{
		$see_game = '1=1';
		$see_category = '1=1';
	}
	// Build permission query
	else
	{
		if (!isset($modSettings['arcadePermissionMode']))
			$modSettings['arcadePermissionMode'] = 1;

		if ($modSettings['arcadePermissionMode'] >= 2)
		{
			// Can see game?
			if ($user_info['is_guest'])
				$see_game = '(game.id_cat = 0 AND ' . (allowedTo('arcade_view') ? 1 : 0) . ' = 1) OR (game.local_permissions = 0 OR FIND_IN_SET(-1, game.member_groups))';
			// Registered user.... just the groups in $user_info['groups'].
			else
				$see_game = '(game.local_permissions = 0 OR (FIND_IN_SET(' . implode(', game.member_groups) OR FIND_IN_SET(', $user_info['groups']) . ', game.member_groups)))';
		}

		if ($modSettings['arcadePermissionMode'] == 1 || $modSettings['arcadePermissionMode'] >= 3)
		{
			// Can see category?
			if ($user_info['is_guest'])
				$see_category = '(game.id_cat = 0 AND ' . (allowedTo('arcade_view') ? 1 : 0) . ' = 1) OR (FIND_IN_SET(-1, category.member_groups))';
			// Registered user.... just the groups in $user_info['groups'].
			else
				$see_category = '(FIND_IN_SET(' . implode(', category.member_groups) OR FIND_IN_SET(', $user_info['groups']) . ', category.member_groups) OR ISNULL(category.member_groups))';
		}
	}

	$arena_category = '(FIND_IN_SET(-2, category.member_groups) OR ISNULL(category.member_groups))';
	$arena_game = '(game.local_permissions = 0 OR FIND_IN_SET(-2, game.member_groups))';

	// Build final query
	// No game/category permissions used
	if (empty($modSettings['arcadePermissionMode']))
	{
		$user_info['query_see_game'] = 'enabled = 1';
		$user_info['query_arena_game'] = 'enabled = 1';
	}
	// Only category used
	elseif ($modSettings['arcadePermissionMode'] == 1)
	{
		$user_info['query_see_game'] = "(enabled = 1 AND $see_category)";
		$user_info['query_arena_game'] = "(enabled = 1 AND $arena_category)";
	}
	// Only category used
	elseif ($modSettings['arcadePermissionMode'] == 2)
	{
		$user_info['query_see_game'] = "(enabled = 1 AND $see_game)";
		$user_info['query_arena_game'] = "(enabled = 1 AND $arena_game)";
	}
	// Required to have permssion to game and category
	elseif ($modSettings['arcadePermissionMode'] == 3)
	{
		$user_info['query_see_game'] = "(enabled = 1 AND ($see_category AND $see_game))";
		$user_info['query_arena_game'] = "(enabled = 1 AND ($arena_category AND $arena_game))";
	}
	// Required to have permssion to game OR category
	elseif ($modSettings['arcadePermissionMode'] == 4)
	{
		$user_info['query_see_game'] = "(enabled = 1 AND ($see_category OR $see_game))";
		$user_info['query_arena_game'] = "(enabled = 1 AND ($arena_category OR $arena_game))";
	}

	$user_info['query_see_match'] = "(private_game = 0 OR me.id_member = $user_info[id])";
}

function PostPermissionCheck()
{
	global $txt, $modSettings, $context, $user_info, $user_profile, $smcFunc;

	// Is Post permissions enabled or is user all-migty admin?
	if ((allowedTo('arcade_admin') && empty($_REQUEST['pcheck'])) || empty($modSettings['arcadePostPermission']) || !$context['arcade']['can_play'])
		return;

	// Guests cannot ever pass
	elseif ($user_info['is_guest'])
	{
		$context['arcade']['can_play'] = false;
		$context['arcade']['notice'] = $txt['arcade_notice_post_requirement'];

		return;
	}

	// We don't want to load this data on every page load
	if (isset($_SESSION['arcade_posts']) && time() - $_SESSION['arcade_posts']['time'] < 360 && empty($_REQUEST['pcheck']))
		$context['arcade']['posts'] = &$_SESSION['arcade_posts'];

	// But now we have to...
	else
	{
		loadMemberData($user_info['id'], false, 'minimal');

		$days = ceil(time() - $user_profile[$user_info['id']]['date_registered'] / 86400);

		// At should be always at least one day
		if ($days < 1)
			$days = 1;

		$context['arcade']['posts'] = array(
			'cumulative' => $user_profile[$user_info['id']]['posts'],
			'average' => $user_profile[$user_info['id']]['posts'] / $days,
			'last_day' => 0,
			'time' => time(),
		);

		if (!empty($modSettings['arcadePostsPlayPerDay']))
		{
			$result = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}messages AS m
					LEFT JOIN {db_prefix}boards AS b ON (m.id_board = b.id_board)
				WHERE b.count_posts != 1
					AND m.id_member = {int:member}
					AND m.poster_time >= {int:from}',
				array(
					'member' => $user_info['id'],
					'from' => time() - 86400
				)
			);

			list ($context['arcade']['posts']['last_day']) = $smcFunc['db_fetch_row']($result);
			$smcFunc['db_free_result']($result);
		}
		else
		{
			$context['arcade']['posts']['last_day'] = 0;
		}

		$_SESSION['arcade_posts'] = $context['arcade']['posts'];
	}

	$cumulativePosts = true;
	$averagePosts = true;
	$postsLastDay = true;

	// Enough post to play?
	if (!empty($modSettings['arcadePostsPlay']))
		$cumulativePosts = $context['arcade']['posts']['cumulative'] >= $modSettings['arcadePostsPlay'];

	// Enough average posts to play?
	if (!empty($modSettings['arcadePostsPlayAverage']))
		$averagePosts = $context['arcade']['posts']['average'] >= $modSettings['arcadePostsPlayAverage'];

	// Enough post today to play?
	if (!empty($modSettings['arcadePostsPlayPerDay']))
		$postsLastDay = $context['arcade']['posts']['last_day'] >= $modSettings['arcadePostsLastDay'];

	// Result is
	$context['arcade']['can_play'] = $cumulativePosts && $averagePosts && $postsLastDay;

	// Should we display notice?
	if (!$cumulativePosts || !$averagePosts || !$postsLastDay)
		$context['arcade']['notice'] = $txt['arcade_notice_post_requirement'];
}

function loadArcadeSettings($memID = 0)
{
	global $smcFunc, $user_info, $modSettings;

	if ($memID == 0 && $user_info['is_guest'])
		return array();

	// Default
	$arcadeSettings = array();

	$request = $smcFunc['db_query']('', '
			SELECT id_member, arena_invite, arena_match_end, arena_new_round, champion_email, champion_pm,
			games_per_page, new_champion_any, new_champion_own, scores_per_page, skin, list
			FROM {db_prefix}arcade_members
			WHERE id_member = {int:member}
			LIMIT 1',
			array(
				'member' => $memID == 0 ? $user_info['id'] : $memID,
			)
		);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$arcadeSettings = array(
			'id_member' => $row['id_member'],
			'arena_invite' => $row['arena_invite'],
			'arena_match_end' => $row['arena_match_end'],
			'arena_new_round' => $row['arena_new_round'],
			'champion_email' => $row['champion_email'],
			'champion_pm' => $row['champion_pm'],
			'games_per_page' => $row['games_per_page'],
			'new_champion_any' => $row['new_champion_any'],
			'new_champion_own' => $row['new_champion_own'],
			'scores_per_page' => $row['scores_per_page'],
			'skin' => $row['skin'],
			'list' => $row['list'],
		);
	}
	$smcFunc['db_free_result']($request);

	return $arcadeSettings;
}

function getSubmitSystem()
{
	global $context;

	$ibp = isset($_REQUEST['autocom']) && $_REQUEST['autocom'] == 'arcade';

	if (!empty($context['playing_custom']))
		return 'custom_game';
	elseif (isset($_POST['mochi']))
		return 'mochi';
	elseif (isset($_REQUEST['act']) && strtolower($_REQUEST['act']) == 'arcade')
		return 'ibp';
	elseif ($ibp && !isset($_REQUEST['arcadegid']))
		return 'ibp3';
	elseif ($ibp && isset($_REQUEST['arcadegid']))
		return 'ibp32';
	/*elseif ($ibp && isset($_REQUEST['p']) && $_REQUEST['p'] == 'sngtour')
		return 'ibp_sng';
	elseif (false)
		return 'pnflash';*/
	elseif (isset($_POST['html5']) && isset($_POST['game_name']))
		return 'html5';
	elseif (isset($_POST['phpbb']) && isset($_POST['game_name']))
		return 'phpbb';
	elseif ((isset($_POST['v3arcade']) || $_REQUEST['sa'] == 'vbBurn') && (isset($_POST['game_name']) || isset($_POST['id'])))
		return 'v3arcade';
	elseif (isset($_REQUEST['sa']) && substr($_REQUEST['sa'], 0, 3) == 'v2S')
		return 'v2game';
	elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'submit')
		return 'v1game';
	else
		return false;
}

function submitSystemInfo($system = '')
{
	global $arcadeFunc, $context, $sourcedir;

	if (empty($system))
		$system = getSubmitSystem();

	if ($system == false)
		$system = 'v1game';

	static $systems = array(
		'v2game' => array(
			'system' => 'v2game',
			'name' => 'SMF Arcade v2 (Actionscript 2)',
			'file' => 'Submit-v2game.php',
			'get_game' => 'ArcadeV2GetGame',
			'info' => 'ArcadeV2Submit',
			'play' => 'ArcadeV2Play',
			'xml_play' => 'ArcadeV2XMLPlay',
			'html' => 'ArcadeV2Html',
		),
		'v1game' => array(
			'system' => 'v1game',
			'name' => 'SMF Arcade v1',
			'file' => 'Submit-v1game.php',
			'get_game' => 'ArcadeV1GetGame',
			'info' => 'ArcadeV1Submit',
			'play' => 'ArcadeV1Play',
			'xml_play' => 'ArcadeV1XMLPlay',
			'html' => 'ArcadeV1Html',
		),
		'custom_game' => array(
			'system' => 'custom_game',
			'name' => 'Custom Game (PHP)',
			'file' => 'Submit-custom.php',
			'get_game' => false,
			'info' => 'ArcadeCustomSubmit',
			'play' => 'ArcadeCustomPlay',
			'xml_play' => 'ArcadeCustomXMLPlay',
			'html' => 'ArcadeCustomHtml',
		),
		'ibp' => array(
			'system' => 'ibp',
			'name' => 'IBP Arcade',
			'file' => 'Submit-ibp.php',
			'get_game' => 'ArcadeIBPGetGame',
			'info' => 'ArcadeIBPSubmit',
			'play' => 'ArcadeIBPPlay',
			'xml_play' => 'ArcadeIBPXMLPlay',
			'html' => 'ArcadeIBPHtml',
		),
		'ibp3' => array(
			'system' => 'ibp3',
			'name' => 'IBP Arcade v3',
			'file' => 'Submit-ibp.php',
			'get_game' => 'ArcadeIBP3GetGame',
			'info' => 'ArcadeIBP3Submit',
			'play' => 'ArcadeIBP3Play',
			'xml_play' => 'ArcadeIBP3XMLPlay',
			'html' => 'ArcadeIBPHtml',
		),
		'ibp32' => array(
			'system' => 'ibp32',
			'name' => 'IBP Arcade v3.2',
			'file' => 'Submit-ibp.php',
			'get_game' => 'ArcadeIBP32GetGame',
			'info' => 'ArcadeIBP32Submit',
			'play' => 'ArcadeIBP3Play',
			'xml_play' => 'ArcadeIBP3XMLPlay',
			'html' => 'ArcadeIBPHtml',
		),
		'v3arcade' => array(
			'system' => 'v3arcade',
			'name' => 'v3Arcade',
			'file' => 'Submit-v3arcade.php',
			'get_game' => 'ArcadevbGetGame',
			'info' => 'ArcadeVbSubmit',
			'play' => 'ArcadeVbPlay',
			'xml_play' => 'ArcadeVbXMLPlay',
			'html' => 'ArcadeVbHtml',
		),
		'phpbb' => array(
			'system' => 'phpbb',
			'name' => 'PhpBB (activity mod)',
			'file' => 'Submit-phpbb.php',
			'get_game' => 'ArcadePHPBBGetGame',
			'info' => 'ArcadePHPBBSubmit',
			'play' => 'ArcadePHPBBPlay',
			'xml_play' => 'ArcadePHPBBXMLPlay',
			'html' => 'ArcadePHPBBHtml',
		),
		'html5' => array(
			'system' => 'html5',
			'name' => 'HTML5 (requires SMF Arcade custom)',
			'file' => 'Submit-HTML5.php',
			'get_game' => 'ArcadeHTML5GetGame',
			'info' => 'ArcadeHTML5Submit',
			'play' => 'ArcadeHTML5Play',
			'xml_play' => 'ArcadeHTML5XMLPlay',
			'html' => 'ArcadeHTML5Html',
		),
		'mochi' => array(
			'system' => 'mochi',
			'name' => 'MochiAds (requires external module)',
			'file' => 'Submit-mochi.php',
			'get_game' => 'ArcadeMochiGetGame',
			'get_settings' => 'ArcadeMochiGetSettings',
			'info' => 'ArcadeMochiSubmit',
			'play' => 'ArcadeMochiPlay',
			'xml_play' => 'ArcadeMochiXMLPlay',
			'html' => 'ArcadeMochiHtml',
		),
	);
	static $submit_system_check_done = false;

	// Remove non-installed systems
	if (!$submit_system_check_done)
	{
		foreach ($systems as $id => $temp)
		{
			if (!file_exists($sourcedir . '/' . $temp['file']))
				unset($systems[$id]);
		}

		$submit_system_check_done = true;
	}

	if ($system == '*')
		return $systems;
	elseif (isset($systems[$system]))
		return $systems[$system];
	else
		return false;
}

function CheatingCheck()
{
	global $scripturl, $modSettings;

	$error = '';

	// Default check level is 1
	if (!isset($modSettings['arcadeCheckLevel']))
		$modSettings['arcadeCheckLevel'] = 1;

	if (!empty($_SERVER['HTTP_REFERER']))
		$referer = parse_url($_SERVER['HTTP_REFERER']);

	$real = parse_url($scripturl);

	// Level 1 Check
	// Checks also HTTP_REFERER if it not is empty
	if ($modSettings['arcadeCheckLevel'] == 1)
	{
		if (isset($referer) && ($real['host'] != $referer['host'] || $real['scheme'] != $referer['scheme']))
			$error = 'invalid_referer';
	}
	// Level 2 Check
	// Doesn't allow HTTP_REFERER to be empty
	elseif ($modSettings['arcadeCheckLevel'] == 2)
	{
		if (!isset($referer) || (isset($referer) && ($real['host'] != $referer['host'] || $real['scheme'] != $referer['scheme'])))
			$error = 'invalid_referer';

	}
	// Level 0 check
	else
		$error = '';

	return $error;
}

function getGameInfo($id_game = 0, $raw = false)
{
	global $scripturl, $txt, $db_prefix, $user_info, $smcFunc, $modSettings, $context, $settings;

	$id_game = loadGame($id_game);

	if ($id_game === false)
		return false;

	if ($raw)
		return $context['arcade']['game_data'][$id_game];

	$game = &$context['arcade']['game_data'][$id_game];

	// Is game installed in subdirectory
	if ($game['game_directory'] != '')
		$gameurl = $modSettings['gamesUrl'] . '/' . $game['game_directory'] . '/';
	// It is in main directory
	else
		$gameurl = $modSettings['gamesUrl'] . '/';

	$description = parse_bbc($game['description']);
	$help = parse_bbc($game['help']);
	$extra = !empty($game['extra_data']) ? unserialize($game['extra_data']) : array('width' => 400, 'height' => 600);
	$version = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

	if (!empty($game['real_name']))
	{
		$player_name = $game['real_name'];
		$guest = empty($game['id_member']);
	}
	else
	{
		$player_name = $txt['guest'];
		$guest = true;
	}

	return array(
		'id' => $game['id_game'],
		'url' => array(
			'play' => $scripturl . '?action=arcade;sa=play;game=' . $game['id_game'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
			'base_url' => $gameurl,
			'highscore' => $scripturl . '?action=arcade;sa=highscore;game=' . $game['id_game'] . ';reload=' . mt_rand(1, 9999) . ';#commentform3',
			'flash' => $gameurl . $game['game_file'],
			'favorite' => $context['arcade']['can_favorite'] ? $game['is_favorite'] == 0 ? $scripturl . '?action=arcade;sa=favorite;game=' . $game['id_game'] : $scripturl . '?action=arcade;sa=favorite;remove;game=' . $game['id_game'] : '#',
		),
		'extra_data' => !empty($game['extra_data']) ? unserialize($game['extra_data']) : array(),
		'category' => array(
			'id' => $game['id_cat'],
			'name' => $game['cat_name'],
			'link' => $scripturl . '?action=arcade;category=' . $game['id_cat'],
			'cat_icon' => !empty($game['cat_icon']) ? $settings['default_images_url'] . '/arc_icons/' . $game['cat_icon'] : '',
		),
		'submit_system' => $game['submit_system'],
		'internal_name' => $game['internal_name'],
		'name' => $game['game_name'],
		'directory' => $game['game_directory'],
		'file' => $game['game_file'],
		'description' => $description,
		'help' =>  $help,
		'rating' => $game['game_rating'],
		'rating2' => round($game['game_rating']),
		'thumbnail' => !empty($game['thumbnail']) ? $gameurl . $game['thumbnail'] : '',
		'thumbnail_small' => !empty($game['thumbnail_small']) ? $gameurl . $game['thumbnail_small'] : '',
		'is_champion' => $game['id_score'] > 0,
		'champion' => array(
			'id' => $game['id_member'],
			'name' => $player_name,
			'score_id' => $game['id_score'],
			'link' =>  !$guest ? '<a href="' . $scripturl . '?action=profile;u=' . $game['id_member'] . '">' . $player_name . '</a>' : $player_name,
			'score' => comma_format((float) $game['champ_score']),
			'time' => $game['champion_time'],
		),
		'is_personal_best' => !$user_info['is_guest'] && $game['id_pb'] > 0,
		'personal_best' => !$user_info['is_guest'] ? comma_format((float) $game['personal_best']) : 0,
		'personal_best_score' => !$user_info['is_guest'] ? $game['personal_best'] : 0,
		'score_type' => $game['score_type'],
		'highscore_support' => $game['score_type'] != 2,
		'is_favorite' => $context['arcade']['can_favorite'] ? $game['is_favorite'] > 0 : false,
		'favorite' => $game['num_favorites'],
		'member_groups' => isset($game['member_groups']) ? explode(',', $game['member_groups']) : array(),
		'width' => !empty($extra['width']) ? (int) $extra['width'] : 400,
		'height' => !empty($extra['height']) ? (int) $extra['height'] :600,
		'type' => !empty($extra['type']) ? trim($extra['type']) : '',
		'smf_version' => $version,
	);
}

// Return game of day
function getGameOfDay()
{
	global $db_prefix, $modSettings;

	// Return 'Game of day'

	if (!isset($modSettings['game_of_day']) || !is_numeric($modSettings['game_of_day']) || !isset($modSettings['game_time']) || $modSettings['game_time'] != date('ymd'))
		return newGameOfDay();

	if (!($game = cache_get_data('game_of_day', 360)))
	{
		if (!($game = GetGameInfo($modSettings['game_of_day'])))
			return newGameOfDay();

		cache_put_data('game_of_day', $game, 360);
	}

	return $game;
}

// Generates new game of day
function newGameOfDay()
{
	global $db_prefix, $modSettings;

	$game = getGameInfo('random');

	if (!$game)
		return false;

	updateSettings(array(
		'game_time' => date('ymd'),
		'game_of_day' => $game['id']
	));

	cache_put_data('game_of_day', $game, 360);

	return $game;
}

function getRecommendedGames($id_game)
{
	global $db_prefix, $user_info, $smcFunc;

	if (!is_array($id_game))
		$id_game = array($id_game);

	$request = $smcFunc['db_query']('', '
		SELECT sc.id_member, COUNT(*) as plays
		FROM {db_prefix}arcade_scores AS sc
		WHERE sc.id_game IN({array_int:games})
		GROUP BY sc.id_member
		ORDER BY plays DESC
		LIMIT 50',
		array(
			'games' => $id_game,
		)
	);

	$players = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$players[] = $row['id_member'];
	$smcFunc['db_free_result']($request);

	if (empty($players))
		return false;

	$request = $smcFunc['db_query']('', '
		SELECT sc.id_game, COUNT(*) AS plays, game.id_cat
		FROM {db_prefix}arcade_scores AS sc
			LEFT JOIN {db_prefix}arcade_games AS game ON (game.id_game = sc.id_game)
			LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)
		WHERE {string:query_see_game}
			AND sc.id_member IN({array_int:players})
			AND game.id_game NOT IN({array_int:games})
		GROUP BY sc.id_game
		ORDER BY plays DESC
		LIMIT 3',
		array(
			'players' => $players,
			'games' => $id_game,
			'query_see_game' => $user_info['query_see_game']
		)
	);

	$recommended = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($id_game == $row['id_game'])
			continue;

		$recommended[] = getGameInfo($row['id_game']);
	}
	$smcFunc['db_free_result']($request);

	return $recommended;
}

// Return Latest scores
function ArcadeLatestScores($count = 5, $start = 0)
{
	global $scripturl, $txt, $db_prefix, $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, score.score, score.position,
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, score.player_name) AS real_name, score.end_time
		FROM {db_prefix}arcade_scores AS score
			INNER JOIN {db_prefix}arcade_games AS game ON (game.id_game = score.id_game)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
		ORDER BY end_time DESC
		LIMIT {int:start}, {int:count}',
		array(
			'start' => $start,
			'count' => $count,
			'empty' => '',
		)
	);

	$data = array();

	while ($score = $smcFunc['db_fetch_assoc']($request))
		$data[] = array(
			'game_id' => $score['id_game'],
			'name' => $score['game_name'],
			'score' => comma_format($score['score']),
			'id' => $score['id_member'],
			'member' => $score['real_name'],
			'memberLink' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' . $score['real_name'] . '</a>' : $txt['guest'],
			'time' => timeformat($score['end_time']),
		);
	$smcFunc['db_free_result']($request);

	return $data;
}

// Output
function ArcadeXMLOutput($data, $name = null, $elements = array())
{
	global $context, $modSettings;

	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	header('Content-Type: text/xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	', Array2XML($data, $name, $elements), '
</smf>';

	obExit(false);
}

function Array2XML($data, $name = null, $elements = array(), $indent = 1)
{
	if (!is_array($data))
		return;

	$output = array();

	$ind = str_repeat("\t", $indent);

	foreach ($data as $k => $data)
	{
		if (is_numeric($k) && $name != null)
		{
			if (is_array($data) && isset($data[0]) && $data[0] = 'call')
				$output [] = '<' . $name . '><![CDATA[' . call_user_func_array($data[1], $data[2]) . ']]></' . $name . '>';
			else
			{
				$output[] = '<' . $name . '>';
				$output[] = '	' . Array2XML($data, null, $elements, $indent++);
				$output[] = '</' . $name . '>';
			}
		}
		elseif (is_numeric($k))
			fatal_lang_error('arcade_internal_error', false);
		else
		{
			if (!empty($elements) && !((in_array($k, $elements) && !is_array($data)) || (isset($elements[$k]) && is_array($data))))
				continue;

			if (is_array($data))
			{
				$output[] = '<' . $k . '>';
				$output[] = '	' . Array2XML($data, null, $elements[$k], $indent++);
				$output[] = '</' . $k . '>';
			}
			else
			{
				if ($data === false)
					$data = 0;
				elseif ($data === true)
					$data = 1;

				if (!is_numeric($data))
					$output [] = '<' . $k . '><![CDATA[' . $data . ']]></' . $k . '>';
				else
					$output [] = '<' . $k . '>' . $data . '</' . $k . '>';
			}
		}
	}

	return implode("\n", $output);
}

function memberAllowedTo($permission, $memID)
{
	if (!is_array($permission))
		$permission = array($permission);

	if (!is_array($memID))
	{
		foreach ($permission as $perm)
		{
			if (in_array($memID, membersAllowedTo($perm)))
				return true;
		}

		return false;
	}

	$allowed = array();

	foreach ($permission as $perm)
	{
		$members = membersAllowedTo($perm);

		foreach ($memID as $i => $id)
		{
			if (in_array($id, $members))
			{
				$allowed[] = $id;

				unset($memID[$i]);

				if (empty($memID))
					return $allowed;
			}
		}
	}

	return $allowed;
}

function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return (float) $usec + (float) $sec;
}

function duration_format($seconds, $max = 2)
{
	global $txt;

	if ($seconds < 1)
		return $txt['arcade_unknown'];

	// max: 0 = weeks, 1 = days, 2 = hours, 3 = minutes, 4 = seconds

	if ($max >= 4)
		$max = 3;
	else
		$max--;

	$units = array(
		array(604800, $txt['arcade_weeks']), // Seconds in week
		array(86400, $txt['arcade_days']), // Seconds in day
		array(3600, $txt['arcade_hours']), // Seconds in hour
		array(60, $txt['arcade_mins']), // Seconds in minute
		array(1, $txt['arcade_secs']), // Seconds in minute
	);

	$out = array();

	foreach ($units as $i => $unit)
	{
		if ($max > $i || $seconds < $unit[0])
			continue;

		list ($secs, $text) = $unit;

		$s = floor($seconds / $secs);
		$seconds -= $s * $secs;

		$out[] = $s . ' ' . $text;
	}

	return implode(' ', $out);
}

function arcade_online()
{
	global $smcFunc;

	// count users online
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}arcade_member_data',
		array()
	);
	$users = $smcFunc['db_num_rows']($request);
	$smcFunc['db_free_result']($request);

	// count guests online
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}arcade_guest_data',
		array()
	);
	$guests = $smcFunc['db_num_rows']($request);
	$smcFunc['db_free_result']($request);

	return array(time(), $guests, $users);
}

function Arcade_DoToolBarStrip($area = 'index', $direction = 'bottom', $content = '')
{
	global $modSettings, $txt, $context, $scripturl, $db_count;
	list($content, $context['arcade']['buttons_set'], $context['current_arcade_sa'], $context['arcade']['tour']['show']) = array('', array(), !empty($_REQUEST['sa']) ? $_REQUEST['sa'] : 'list', !empty($context['arcade']['tour']['show']) ? (int)$context['arcade']['tour']['show'] : 0);
	$_SESSION['current_cat'] = !empty($_SESSION['current_cat']) ? $_SESSION['current_cat'] : 'all';
	$_SESSION['arcade_sortby'] = !empty($_SESSION['arcade_sortby']) ? $_SESSION['arcade_sortby'] : 'a2z';
	$sort = ($_SESSION['current_cat'] == 0 || $_SESSION['current_cat'] == 'all') && $_SESSION['arcade_sortby'] == 'a2z' ? '' : ';sortby=reset';

	if ($context['arcade']['tour']['show'] != 0)
        $context['arcadetour']['buttons_set']['newtour'] =  array(
			'text' => 'arcade_tour_new_tour',
			'url' => $scripturl . '?action=arcade;sa=tour;ta=new',
			'lang' => true
		);

    if ($context['arcade']['tour']['show'] != 2)
        $context['arcadetour']['buttons_set']['activetour'] =  array(
			'text' => 'arcade_tour_show_active',
    		'url' => $scripturl . '?action=arcade;sa=tour',
    		'lang' => true,
    	);

    if ($context['arcade']['tour']['show'] != 1)
	    $context['arcadetour']['buttons_set']['finishedtour'] =  array(
    		'text' => 'arcade_tour_show_finished',
    		'url' => $scripturl . '?action=arcade;sa=tour;show=1',
    		'lang' => true,
    	);

	$context['arcade']['buttons_set']['arcade'] =  array(
    	'text' => 'arcade',
		'image' => 'arcade.gif',
    	'url' => $scripturl . '?action=arcade' . $sort,
		'active' => in_array($context['current_arcade_sa'], array('list', 'highscore', 'online', 'play')) ? true : null,
    	'lang' => true,
    );

	if (!empty($modSettings['arcadeArenaEnabled']))
		$context['arcade']['buttons_set']['tour'] =  array(
			'text' => 'arcade_arena',
			'image' => 'arcade_arena.gif',
			'url' => $scripturl . '?action=arcade;sa=arena',
			'active' => in_array($context['current_arcade_sa'], array('arena', 'newMatch', 'newMatch2', 'viewMatch')) ? true : null,
			'lang' => true,
		);

	$context['arcade']['buttons_set']['stats'] =  array(
    	'text' => 'arcade_stats',
		'image' => 'arcade_stats.gif',
    	'url' => $scripturl . '?action=arcade;sa=stats',
		'active' => in_array($context['current_arcade_sa'], array('stats')) ? true : null,
    	'lang' => true,
    );

    if (allowedTo('admin_arcade'))
       	$context['arcade']['buttons_set']['arcadeadmin'] =  array(
    		'text' => 'arcade_administrator',
			'image' => 'arcade_administrator.gif',
    		'url' => $scripturl . '?action=admin;area=arcade',
    		'lang' => true,
			'is_last' => true,
    	);

	$context['arcade']['queries_temp'] = !empty($db_count) ? $db_count : 0;
	$button_strip = (!empty($area)) && $area == 'arena' ? $context['arcadetour']['buttons_set'] : $context['arcade']['buttons_set'];

	if (!empty($modSettings['arcadeTabs']))
		template_button_strip($button_strip, $direction);
	else
	{
		foreach ($button_strip as $tab)
		{
			$content .= '
			<a href="' . $tab['url'] . '">' . $txt[$tab['text']] . '</a>';

			if (empty($tab['is_last']))
				$content .= '&nbsp;|&nbsp;';
		}
	}

	return $content;
}

function ArcadeSpecialChars($var, $type = 'name')
{
	$pattern = '/&(#)?[a-zA-Z0-9]{0,};/';
	if (is_array($var))
	{
		$out = array();
	    foreach ($var as $key => $v)
			$out[$key] = ArcadeSpecialChars($v, '');
    }
	else
	{
	    $out = $var;
	    while (preg_match($pattern, $out) > 0)
			$out = htmlspecialchars_decode($out, ENT_QUOTES);

	    $out = htmlspecialchars(stripslashes(trim($out)), ENT_QUOTES, 'UTF-8', true);
	}

	if ($type == 'image')
		return str_replace(' ', '_', $out);

	return $out;
}

function ArcadeSizer($file='', $maxwidth = 50, $maxheight = 50)
{
	global $modSettings, $sourcedir;

	if (!empty($file))
	{
		$file = str_replace(' ', '%20',$file);
		if(list ($width, $height) = url_image_size($file))
		{
			if($height > $maxheight)
			{
				$percentage = ($maxheight / $height);
				$height = round($height * $percentage);
			}

			if($width > $maxwidth)
			{
				$percentage = ($maxwidth / $width);
				$width = round($width * $percentage);
			}

			return array($width, $height);
		}
	}

	return array();
}

function ArcadeCategoryDropdown()
{
	// Game Category drop down menu
	global $scripturl, $smcFunc, $txt, $settings, $context;
	$count = 0;
	$where1 = $scripturl . '?action=arcade;category=';
	$display = '
	<form action="' . $scripturl . '?action=arcade" method="post">
		<select name="category" style="font-size: 100%; color: black;" onchange="JavaScript:submit()">
			<option value="">&nbsp;' . $txt['view_cat'] . '</option>';

	$request = $smcFunc['db_query']('', '
		SELECT id_cat, cat_name, num_games, cat_order, cat_icon
		FROM {db_prefix}arcade_categories
		ORDER BY cat_order',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['categories'][$row['id_cat']] = array(
			'id' => $row['id_cat'],
			'name' => $row['cat_name'],
			'link' => $scripturl . '?action=arcade;category=' . $row['id_cat'],
			'drop' => '<a href="' . $scripturl . '?action=arcade;category=' . $row['id_cat'] . '">' . $row['cat_name'] . '</a>',
			'icon' => !empty($row['cat_icon']) ? $settings['default_images_url'] . '/arc_icons/' . $row['cat_icon'] : '',
		);

		$display .= '
			<option value="' . $row['id_cat'] . '">&nbsp;' . $row['cat_name'] . '</option>';
	}

	$smcFunc['db_free_result']($request);

	$display .= '
			<option value="all">&nbsp;' . $txt['arcade_all'] . '</option>
		</select>
	</form>';

	return $display;
}

function small_game_query($condition)
{
	global $scripturl, $smcFunc, $modSettings, $txt, $user_info, $boardurl, $settings;

	$games = array();
	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.game_rating, game.game_directory, game.thumbnail, game.member_groups, game.thumbnail_small, game.id_cat,
		IFNULL(score.id_score,0) AS id_score, IFNULL(score.score,0) AS champScore,IFNULL(mem.id_member,0) AS id_member, category.cat_icon,
		IFNULL(mem.real_name,0) AS real_name
		FROM {db_prefix}arcade_games AS game
		  LEFT JOIN {db_prefix}arcade_scores AS score ON (score.id_score = game.id_champion_score)
		  LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
		  LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)
		WHERE '.$user_info['query_see_game'].' AND enabled = {int:enabled} '. $condition,
		array(
		  'enabled' => 1,
		)
	);
	while ($game = $smcFunc['db_fetch_assoc']($request))
	{
		//sort the paths for the thumbnail
		$gameUrl = $boardurl . '/' . basename($modSettings['gamesUrl']);
		$gameico = empty($game['game_directory']) ? $gameUrl . '/' . $game['thumbnail'] : $gameUrl . '/' . $game['game_directory'] . '/' . $game['thumbnail'];
		$gameicosmall = empty($game['thumbnail_small']) ? $gameico : (empty($game['game_directory']) ? $gameUrl . '/' . $game['thumbnail_small'] : $gameUrl . '/' . $game['game_directory'] . '/' . $game['thumbnail_small']);

		//build and return an arry of whats needed
		$games[$game['game_name']] = array(
			'id' => $game['id_game'],
			'url' => array(
				'play' => $scripturl . '?action=arcade;sa=play;game=' . $game['id_game'] . ';#playgame',
				),
			'name' => $game['game_name'],
			'cat_icon' => !empty($row['cat_icon']) ? $settings['default_theme_url'] . '/arc_icons/' . $row['cat_icon'] : '',
			'rating' => $game['game_rating'],
			'rating2' => round($game['game_rating']),
			'thumbnail' => $gameico,
			'thumbnail_small' => $gameicosmall,
			'isChampion' => $game['id_score'] > 0 ? true : false,
			'champion' => array(
				'member_id' => $game['id_member'],
				'memberLink' =>  $game['real_name'] != '' ? '<a href="' . $scripturl . '?action=profile;u=' . $game['id_member'] . ';sa=statPanel">' . $game['real_name'] . '</a>' : $txt['arcade_guest'],
				'score' => round($game['champScore'],3),
			),
		);
	}

	$smcFunc['db_free_result']($request);

	return $games;
}

function ArcadeCats($highlight='')
{
	global $smcFunc, $db_prefix, $context, $scripturl, $txt, $boardurl, $modSettings, $settings;

	$kittens = '';
	/* These are your adjustable variables */
	if (empty($modSettings['arcade_catWidth']))
		$modSettings['arcade_catWidth'] = 23;

	if (empty($modSettings['arcade_catHeight']))
		$modSettings['arcade_catHeight'] = 20;

	$icon_folder = $settings['default_images_url'] . '/arc_icons/';
	$icon_width = (int)$modSettings['arcade_catWidth'];
	$icon_height = (int)$modSettings['arcade_catHeight'];
	$var = array();
	$filter = array();
	$result = $smcFunc['db_query']('', '
		SELECT cat_name, id_cat, num_games, cat_icon
		FROM {db_prefix}arcade_categories
		ORDER BY cat_order'
	);

   $top = array();

	while ($categories = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['cats'][] = array($categories['id_cat'], $categories['cat_name'], $categories['num_games'], $categories['cat_icon']);

   $smcFunc['db_free_result']($result);

   $num = $smcFunc['db_query']('', '
      SELECT count(*)
      FROM {db_prefix}arcade_games
      WHERE id_cat = 0'
	);

	list ($no_cat) = $smcFunc['db_fetch_row']($num);

	$smcFunc['db_free_result']($num);

	list ($lines, $B_start, $Bstop, $kittens) = array(1, '', '', '');
	$context['cat_name'] = '';

	if($no_cat)
	{
		if ($highlight == '0')
		{
			$context['cat_name'] = $txt['arcade_no_category'];
			$B_start = '<strong>';
			$B_stop = '</strong>';
		}
		else
		{
			$B_start = '';
			$B_stop = '';
		}

		$gamepic_name = 'Unassigned';
		$category_pic = '<a href="' . $scripturl . '?action=arcade;category=0"><img src="' . $icon_folder . $gamepic_name . '.gif" alt="&nbsp;" title="' . $gamepic_name . '" style="border: 0px;width: ' . $icon_width . 'px;height: ' . $icon_height . 'px;" /></a>';
		$kittens .= '<div style="display: table;width: 100%;"><span style="display: table-cell;vertical-align: bottom;padding-top: 15px;border: 0px;width: 20%;" class="centertext windowbg2 smalltext">' . $category_pic . '<br />';
		$kittens .= '<a href="' . $scripturl . '?action=arcade;category=0" title="' . $txt['alt_no_cats'] . '" >' . $B_start . sprintf($txt['arcade_no_cats'], $no_cat) . $B_stop . '</a></span>';
	}
	else
		$lines = 0;

	if (!empty($context['arcade']['cats']))
	{
		foreach ($context['arcade']['cats'] as $cat)
		{
			$gamepic_name = ArcadeSpecialChars($cat[1], 'image');
			$filter = array(' ','--','&quot;','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=', "&#039;");
			$gamepic_name = !empty($cat[3]) ? $cat[3] : str_replace($filter, "_", $gamepic_name);
			$category_pic = '<a href="' . $scripturl.'?action=arcade;category=' . $cat[0] . '"><img src="' . $icon_folder . $gamepic_name . '.gif" alt="&nbsp;" title="' . $cat[1] . '" style="border: 0px;width: ' . $icon_width . 'px;height: ' . $icon_height . 'px;" /></a><br />';

			if ($highlight == $cat[0] )
			{
				$context['cat_name'] = $cat[1];
				$B_start = '<b>';
				$B_stop = '</b>';
			}
			else
			{
				$B_start = '';
				$B_stop = '';
			}

			$lines++;
			if ($lines == 6)
				$lines = 1;

			$lines == 1 ? $open = '<div style="display: table;width: 100%;"><span style="display: table-cell;vertical-align: bottom;padding-top: 15px;border: 0px;width: 20%;" class="windowbg2 smalltext centertext">' : $open = '<span style="display: table-cell;vertical-align: bottom;padding-top: 15px;border: 0px;width: 20%;" class="centertext windowbg2 smalltext">';
			$lines == 5 ? $close = '</span></div>' : $close = '</span>';
			$kittens .= $open . $category_pic . '<a href="' . $scripturl . '?action=arcade;category=' . $cat[0] . '">' . $B_start . $cat[1] . '(' . $cat[2] . ')' . $B_stop . '</a>' . $close;
		}

		if ($lines > 0 && $lines < 5)
		{
			$loop = 5-$lines;
			for ($j=1; $j <= $loop; $j++)
				$kittens .= '<span style="display: table-cell;vertical-align: bottom;padding-top: 15px;border: 0px;width: 20%;" class="windowbg2">&nbsp;</span>';
		}

		if ($lines %5 != 0)
			$kittens .= '</div>';
	}

	return $kittens;
}
?>