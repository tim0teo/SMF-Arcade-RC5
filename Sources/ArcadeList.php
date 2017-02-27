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

	void ArcadeList()
		- ???

	void ArcadeSearch()
		- ???

	void ArcadeRate()
		- ???

	void ArcadeFavorite()
		- ???

*/

function ArcadeList()
{
 	global $scripturl, $txt, $db_prefix, $modSettings, $context, $user_info, $smcFunc, $sourcedir;


	$_SESSION['arcade']['gamepopup'] = false;
	$_SESSION['arcade']['pop'] = false;
	$context['arcade_category'] = !empty($_REQUEST['category']) ? ArcadeSpecialChars($_REQUEST['category']) : (!empty($_SESSION['current_cat']) ? ArcadeSpecialChars($_SESSION['current_cat']) : 'all');
	/*
	if ($context['arcade_category'] == 1)
		$_REQUEST['sortby'] = 'age';
	*/

	// Sorting methods
	$sort_methods = array(
		'age' => 'game.id_game',
		'a2z' => 'game.game_name',
		'z2a' => 'game.game_name',
		'plays' => 'game.num_plays',
		'plays_reverse' => 'game.num_plays',
		'champion' => 'mem.member_name',
		'myscore' => 'IFNULL(pb.score, 0)',
		'rating' => 'game.game_rating',
		'champs' => 'score.champion_from',
		'favorites' => 'favorite.id_game'
	);

	$sort_direction = array(
		'age' => 'desc',
		'a2z' => 'asc',
		'z2a' => 'desc',
		'plays' => 'desc',
		'plays_reverse' => 'asc',
		'champion' => 'desc',
		'myscore' => 'desc',
		'rating' => 'desc',
		'champs' => 'desc',
		'favorites' => 'desc',
	);

	$_REQUEST['sortby'] = !empty($_REQUEST['sortby']) ? $_REQUEST['sortby'] : (!empty($_SESSION['arcade_sortby']) ? $_SESSION['arcade_sortby'] :'a2z');
	$context['sort_by'] = !empty($sort_methods[$_REQUEST['sortby']]) ? $_REQUEST['sortby'] : (!empty($_SESSION['arcade_sortby']) ? $_SESSION['arcade_sortby'] :'a2z');
	$context['sort_direction'] = !empty($sort_direction[$context['sort_by']]) ? $sort_direction[$context['sort_by']] : 'asc';
	$_SESSION['arcade_sortby'] = $context['sort_by'];
	$_SESSION['current_cat'] = $context['arcade_category'];
	$sortby = $sort_methods[$context['sort_by']];
	$ascending = $sort_direction[$context['sort_by']];
	$select_rows = '';
	$select_tables = '';
	$where = '';

	if (!$user_info['is_guest'])
	{
		$select_rows = ',
			IFNULL(pb.id_score, 0) AS id_pb, IFNULL(pb.score, 0) AS personal_best, IFNULL(favorite.id_favorite, 0) AS is_favorite';

		$select_tables = (isset($_REQUEST['favorites']) ? 'INNER JOIN' : 'LEFT JOIN') . ' {db_prefix}arcade_favorite AS favorite ON (favorite.id_game = game.id_game
				AND favorite.id_member = {int:member})
			LEFT JOIN {db_prefix}arcade_scores AS pb ON (pb.id_game = game.id_game
				AND pb.id_member = {int:member} AND pb.personal_best = 1)';
	}

	$baseurl = $scripturl . '?action=arcade';

	$context['arcade_search'] = array();

	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'search')
	{
		$baseurl .= ';sa=search';

		if (!empty($_REQUEST['name']))
		{
			$baseurl .= ';name=' . urlencode($_REQUEST['name']);
			$context['arcade_search']['name'] = $_REQUEST['name'];

			$where .= ' AND game.game_name LIKE {string:name}';
		}
	}

	if ($context['arcade_category'] !== 'all')
		$where .= ' AND game.id_cat = {int:category}';
	else
		$where .= " AND game.game_name LIKE '%'";

	if (isset($_REQUEST['sortby']))
		$baseurl .=  ';sort=' . $context['sort_by'];
	if (isset($_REQUEST['desc']))
		$baseurl .=  ';dir=desc';
	if (isset($_REQUEST['favorites']))
	{
		$baseurl .=  ';favorites';
		$context['arcade_search']['favorites'] = true;
	}
	if (isset($_REQUEST['category']))
		$baseurl .= ';category=' . $_REQUEST['category'];

	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)'. (isset($_REQUEST['favorites']) ? '
			INNER JOIN {db_prefix}arcade_favorite AS favorite ON (favorite.id_game = game.id_game
				AND favorite.id_member = {int:member})' : '') . '
		WHERE {raw:query_see_game}' . $where,
		array(
			'name' => isset($_REQUEST['name']) ? '%' . $_REQUEST['name'] . '%' : '',
			'member' => $user_info['id'],
			'category' => $context['arcade_category'],
			'query_see_game' => $user_info['query_see_game'],
		)
	);

	list ($gameCount) = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);

	$context['page_index'] = constructPageIndex($baseurl, $_REQUEST['start'], $gameCount , $context['games_per_page'], false);

	$request = $smcFunc['db_query']('', '
		SELECT
			game.id_game, game.game_name, game.description, game.game_rating, game.num_plays, pdl.download_count, pdl.report_id, game.extra_data,
			game.score_type, game.thumbnail, game.game_directory, score.champion_from,
			game.thumbnail_small, game.help, game.extra_data,
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(score.id_score, 0) AS id_score,
			IFNULL(score.score, 0) AS champ_score, IFNULL(mem.real_name, score.player_name) AS real_name,
			IFNULL(score.end_time, 0) AS champion_time, IFNULL(category.id_cat, 0) AS id_cat,
			IFNULL(category.cat_name, {string:empty_string}) AS cat_name' . $select_rows . '
		FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_scores AS score ON (score.id_score = game.id_champion_score)
			LEFT JOIN {db_prefix}arcade_pdl2 AS pdl ON (pdl.pdl_gameid = game.id_game)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
			LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)' . $select_tables . '
		WHERE {raw:query_see_game}' . $where . '
		ORDER BY {raw:sort}
		LIMIT {int:limit}, {int:games_per_page}',
		array(
			'empty_string' => '',
			'name' => isset($_REQUEST['name']) ? $_REQUEST['name'] . '%' : '',
			'sort' => $sortby . ($ascending == 'asc' ? ' ASC' : ' DESC'),
			'limit' => $_REQUEST['start'],
			'games_per_page' => $context['games_per_page'],
			'member' => $user_info['id'],
			'category' => $context['arcade_category'],
			'query_see_game' => $user_info['query_see_game']
		)
	);

	$context['arcade']['games'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{

		$extra = unserialize($row['extra_data']);
		$extra['height'] = !empty($extra['height']) ? (int)$extra['height'] : 600;
		$extra['width'] = !empty($extra['width']) ? (int)$extra['width'] : 800;

		if (empty($row['real_name']))
			$row['real_name'] = $txt['guest'];

		$extra = unserialize($row['extra_data']);

		// Is game installed in subdirectory
		if (!empty($row['game_directory']))
			$gameurl = $modSettings['gamesUrl'] . '/' . $row['game_directory'] . '/';

		// It is in main directory
		else
			$gameurl = $modSettings['gamesUrl'] . '/';

		$context['arcade']['games'][] = array(
			'id' => $row['id_game'],
			'url' => array(
				'play' => $scripturl . '?action=arcade;sa=play;game=' . $row['id_game'],
				'highscore' => $scripturl . '?action=arcade;sa=highscore;game=' . $row['id_game'],
				'edit' => $scripturl . '?action=managegames;sa=edit;game=' . $row['id_game'],
				'favorite' => $context['arcade']['can_favorite'] ? $row['is_favorite'] == 0 ? $scripturl . '?action=arcade;sa=favorite;game=' . $row['id_game'] : $scripturl . '?action=arcade;sa=favorite;remove;game=' . $row['id_game'] : '#',
			),
			'category' => array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name'],
				'link' => $scripturl . '?action=arcade;category=' . $row['id_cat'],
			),
			'name' => $row['game_name'],
			'description' => parse_bbc($row['description']),
			'plays' => $row['num_plays'],
			'width' => $extra['width'],
			'height' => $extra['height'],
			'is_champion' => $row['id_score'] > 0,
			'champion' => array(
				'member_id' => $row['id_member'],
				'score_id' => $row['id_score'],
				'member_link' =>  !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : $row['real_name'],
				'score' => comma_format($row['champ_score']),
				'time' => $row['champion_time'],
			),
			'is_personal_best' => !$user_info['is_guest'] && $row['id_pb'] > 0,
			'personal_best' => !$user_info['is_guest'] ? comma_format($row['personal_best']) : 0,
			'personal_best_score' => !$user_info['is_guest'] ? $row['personal_best'] : 0,
			'highscore_support' => $row['score_type'] != 2,
			'is_favorite' => $context['arcade']['can_favorite'] ? $row['is_favorite'] > 0 : false,
			'rating' => $row['game_rating'],
			'width' => !empty($extra['width']) ? (int) $extra['width'] : 400,
			'height' => !empty($extra['height']) ? (int) $extra['height'] :600,
			'pdl_count' => $row['download_count'],
			'report_id' => $row['report_id'],
			'rating2' => round($row['game_rating']),
			'thumbnail' => !empty($row['thumbnail']) ? $gameurl . $row['thumbnail'] : '',
			'thumbnail_small' => !empty($row['thumbnail_small']) ? $gameurl . $row['thumbnail_small'] : '',
		);
	}
	$smcFunc['db_free_result']($request);

	if (!empty($modSettings['arcadeShowInfoCenter']))
	{
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

			require_once($sourcedir . '/ArcadeStats.php');

			$context['arcade']['stats']['best_player'] = ArcadeStats_BestPlayers(1);
			$context['arcade']['stats']['longest_champion'] = ArcadeStats_LongestChampions(1, null, 'current');
			$context['arcade']['stats']['most_played'] = ArcadeStats_MostPlayed(1);

			cache_put_data('arcade-stats', $context['arcade']['stats'], 180);
		}

		$context['arcade']['latest_scores'] = ArcadeLatestScores(5, 0);


		$context['arcade_viewing'] = array();
		$context['arcade_num_viewing'] = array('member' => 0, 'guest' => 0, 'hidden' => 0);

		// log the current user to the online list & then search for members in the arcade within 10 minutes
		$log_online = arcade_online();
		$context['arcade_online'] = array($log_online[1], $log_online[2]);

		$request = $smcFunc['db_query']('', '
			SELECT
				id_member, online_time, show_online, online_name, online_color
			FROM {db_prefix}arcade_member_data
			WHERE {int:now} - online_time < 600',
			array(
				'now' => $log_online[0],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!empty($row['online_color']))
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['online_name'] . '</a>';
			else
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['online_name'] . '</a>';

			$is_buddy = in_array($row['id_member'], $user_info['buddies']);
			if ($is_buddy)
				$link = '<b>' . $link . '</b>';

			// Add them both to the list and to the more detailed list.
			if (!empty($row['show_online']) || allowedTo('moderate_forum'))
			{
				$context['arcade_num_viewing']['member']++;
				$context['arcade_viewing'][$row['online_time'] . $row['online_name']] = empty($row['show_online']) ? '<i>' . $link . '</i>' : $link;
			}

			if (empty($row['show_online']))
				$context['arcade_num_viewing']['hidden']++;
		}
		$smcFunc['db_free_result']($request);

		krsort($context['arcade_viewing']);
	}

	// Layout
	$modSettings['arcadeSkin'] = !empty($modSettings['arcadeSkin']) ? (int)$modSettings['arcadeSkin'] : 0;
	$modSettings['arcadeList'] = !empty($modSettings['arcadeList']) ? (int)$modSettings['arcadeList'] : 0;
	if (allowedTo('arcade_online'))
		$context['arcade_online_link'] = '<a href="' . $scripturl . '?index.php;action=arcade;sa=online">' . sprintf($txt['arcade_info_who'], $context['arcade_online'][0], $context['arcade_online'][1], empty($context['arcade_online'][0]) || $context['arcade_online'][0] > 1 ? 's' : '', empty($context['arcade_online'][1]) || $context['arcade_online'][1] > 1 ? 's' : '') . '</a>';
	else
		$context['arcade_online_link'] = sprintf($txt['arcade_info_who'], $context['arcade_online'][0], $context['arcade_online'][1], empty($context['arcade_online'][0]) || $context['arcade_online'][0] > 1 ? 's' : '', empty($context['arcade_online'][1]) || $context['arcade_online'][1] > 1 ? 's' : '');

	if ($modSettings['arcadeList'] == 0)
	{
		loadTemplate('ArcadeSkinListA');
		$context['sub_template'] = 'arcade_list';
		$context['page_title'] = $txt['arcade_game_list'];
	}
	elseif ($modSettings['arcadeList'] == 2)
	{
		loadTemplate('ArcadeSkinListB');
		$context['sub_template'] = 'arcade_list';
		$context['page_title'] = $txt['arcade_game_list'];
	}
	else
	{
		loadTemplate('ArcadeList');
		$context['sub_template'] = 'arcade_list';
		$context['page_title'] = $txt['arcade_game_list'];
	}

	return;
}

function ArcadeXMLSuggest()
{
	global $context, $sourcedir, $user_info, $txt, $smcFunc;

	$_REQUEST['name'] = trim($smcFunc['strtolower']($_REQUEST['name'])) . '*';
	$_REQUEST['name'] = strtr($_REQUEST['name'], array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '&#038;' => '&amp;'));

	// Find the Game
	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name
		FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)
		WHERE ' . (isset($_REQUEST['textid']) && $_REQUEST['textid'] == 'arenagame' ? '{string:query_arena_game}' : '{string:query_see_game}') . '
			AND game.game_name LIKE {string:search}
		LIMIT ' . (strlen($_REQUEST['name']) <= 2 ? '100' : '800'),
		array(
			'search' => $_REQUEST['name'],
			'query_see_game' => $user_info['query_see_game'],
			'query_arena_game' => $user_info['query_arena_game']
		)
	);
	$context['xml_data'] = array(
		'games' => array(
			'identifier' => 'game',
			'children' => array(),
		),
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (function_exists('iconv'))
		{
			$utf8 = iconv($txt['lang_character_set'], 'UTF-8', $row['game_name']);
			if ($utf8)
				$row['game_name'] = $utf8;
		}

		if (preg_match('~&#\d+;~', $row['game_name']) != 0)
		{
			$fixchar = create_function('$n', '
				if ($n < 128)
					return chr($n);
				elseif ($n < 2048)
					return chr(192 | $n >> 6) . chr(128 | $n & 63);
				elseif ($n < 65536)
					return chr(224 | $n >> 12) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);
				else
					return chr(240 | $n >> 18) . chr(128 | $n >> 12 & 63) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);');

			$row['game_name'] = preg_replace('~&#(\d+);~e', '$fixchar(\'$1\')', $row['game_name']);
		}

		$row['game_name'] = strtr($row['game_name'], array('&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;'));

		$context['xml_data']['games']['children'][] = array(
			'attributes' => array(
				'id' => $row['id_game'],
			),
			'value' => $row['game_name'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Template
	loadTemplate('Xml');
	$context['sub_template'] = 'generic_xml';
}

function ArcadeRate()
{
 	global $scripturl, $txt, $db_prefix, $modSettings, $context, $user_info, $smcFunc;

	if (empty($modSettings['arcadeEnableRatings']) || !$game = getGameInfo((int) $_REQUEST['game']))
		fatal_lang_error('arcade_game_not_found', false);

	$_REQUEST['rate'] = (int) $_REQUEST['rate'];

	// Check that rating is correct
	if ($_REQUEST['rate'] < 0 || $_REQUEST['rate'] > 5)
		fatal_lang_error('arcade_rate_error', false);

	// We may need time ;)
	$time = time();

	// Remove rating
	if ($_REQUEST['rate'] === 0)
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_rates
			WHERE id_member = {int:member}
				AND id_game = {int:game}',
			array(
				'game' => $game['id'],
				'member' => $user_info['id'],
			)
		);
	}
	// Update rating
	else
	{
		$smcFunc['db_insert']('replace',
			'{db_prefix}arcade_rates',
			array(
				'id_member' => 'int',
				'id_game' => 'int',
				'rating' => 'int',
				'rate_time' => 'int',
			),
			array(
				$user_info['id'],
				$game['id'],
				$_REQUEST['rate'],
				$time
			),
			array('id_member', 'id_game')
		);
	}

	// Update rating
	$request = $smcFunc['db_query']('', '
		SELECT SUM(rating), COUNT(*)
		FROM {db_prefix}arcade_rates
		WHERE id_game = {int:game}
		GROUP BY id_game',
		array(
			'game' => $game['id'],
		)
	);
	list ($sum_rates, $num_rates) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	updateGame($game['id'], array('rating' => $sum_rates / $num_rates, 'num_rates' => $num_rates));

	if (isset($_REQUEST['xml']))
		ArcadeXMLOutput(
			array(
				'message' => &$txt['arcade_rating_saved'],
				'rating' => floor($sum_rates / $num_rates)
			)
		);

	redirectexit('action=arcade;sa=highscore;game=' . $game['id']);
}

function ArcadeFavorite()
{
 	global $scripturl, $txt, $db_prefix, $modSettings, $context, $user_info, $smcFunc;

	$xml = isset($_REQUEST['xml']);

	is_not_guest();

	if (empty($modSettings['arcadeEnableFavorites']) || !($game = getGameInfo((int) $_REQUEST['game'])))
		fatal_lang_error('arcade_game_not_found', false);

	// It's favorite so we can remove it
	if ($game['is_favorite'])
	{
		$remove = true;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_favorite
			WHERE id_member = {int:member}
				AND id_game = {int:game}',
			array(
				'game' => $game['id'],
				'member' => $user_info['id'],
			)
		);

		// Update favorites count
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}arcade_favorite
			WHERE id_game = {int:game}
			GROUP BY id_game',
			array(
				'game' => $game['id'],
			)
		);

		list ($num_favorites) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		updateGame($game['id'], array('num_favorites' => $num_favorites));

		if ($xml)
			ArcadeXMLOutput(
				array(
					'message' => &$txt['arcade_favorite_removed'],
					'state' => 0
				)
			);
	}
	// It's not favorite, let's add it
	else
	{
		$remove = false;

		$smcFunc['db_insert']('insert',
			'{db_prefix}arcade_favorite',
			array(
				'id_member' => 'int',
				'id_game' => 'int',
			),
			array(
				$user_info['id'],
				$game['id']
			),
			array()
		);

		// Update favorites count
		updateGame($game['id'], array('num_favorites' => '+'));

		if ($xml)
			ArcadeXMLOutput(array(
				'message' => $txt['arcade_favorite_added'],
				'state' => 1
			));
	}

	redirectexit('?action=arcade;sa=highscore;game=' . $game['id']);
}

?>