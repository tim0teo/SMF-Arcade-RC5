<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function arcadeStats($memID)
{
	global $db_prefix, $scripturl, $txt, $modSettings, $context, $settings, $user_info, $smcFunc, $sourcedir, $context;

	require_once($sourcedir . '/Arcade.php');
	loadArcade('profile');

	$context['arcade']['member_stats'] = array();

	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS champion
		FROM {db_prefix}arcade_games
		WHERE id_champion = {int:member}
			AND enabled = 1',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats'] += $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS rates, (SUM(rating) / COUNT(*)) AS avg_rating
		FROM {db_prefix}arcade_rates
		WHERE id_member = {int:member}',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats'] += $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE id_member = {int:member}
			AND personal_best = 1
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY position
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['scores'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['scores'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE id_member = {int:member}
			AND personal_best = 1
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY end_time DESC
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['latest_scores'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['latest_scores'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	// 1st 2nd 3rd placements
	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE id_member = {int:member}
			AND s.position = 1
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY score DESC
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['position1'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['position1'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => (strlen($row['game_name']) > 33) ? substr($row['game_name'], 0, 30) . '...' : $row['game_name'],
			'title' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE id_member = {int:member}
			AND s.position = 2
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY score DESC
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['position2'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['position2'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => (strlen($row['game_name']) > 33) ? substr($row['game_name'], 0, 30) . '...' : $row['game_name'],
			'title' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE id_member = {int:member}
			AND s.position = 3
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY score DESC
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['position3'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['position3'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => (strlen($row['game_name']) > 33) ? substr($row['game_name'], 0, 30) . '...' : $row['game_name'],
			'title' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	// Layout
	$context['sub_template'] = 'arcade_user_statistics';
	$context['page_title'] = sprintf($txt['arcade_user_stats_title'], $context['member']['name']);
}

function arcadeChallenge($memID)
{
	global $db_prefix, $scripturl, $txt, $modSettings, $context, $settings, $user_info, $smcFunc, $sourcedir;

	require_once($sourcedir . '/Arcade.php');
	require_once($sourcedir . '/ArcadeArena.php');
	require_once($sourcedir . '/Subs-Members.php');

	loadArcade('profile');

	if (!memberAllowedTo(array('arcade_join_match', 'arcade_join_invite_match'), $memID))
		fatal_lang_error('arcade_no_invite', false);

	$context['matches'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT id_match, name
		FROM {db_prefix}arcade_matches
		WHERE id_member = {int:member}
			AND status = 0',
		array(
			'member' => $user_info['id'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['matches'][] = array(
			'id' => $row['id_match'],
			'name' => $row['name'],
		);
	$smcFunc['db_free_result']($request);

	// Layout
	$context['sub_template'] = 'arcade_arena_challenge';
	$context['page_title'] = sprintf($txt['arcade_arena_challenge_title'], $context['member']['name']);
}

function arcadeSettings($memID)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc, $user_info, $sourcedir, $modSettings;

	require_once($sourcedir . '/Arcade.php');
	loadArcade('profile');
	$events = arcadeEvent('get');
	$arcadeSettings = loadArcadeSettings($memID);
	switch($modSettings['arcadeSkin'])
	{
		case 1:
			$skin = $txt['arcade_skin_a'];
			break;
		case 2:
			$skin = $txt['arcade_skin_b'];
			break;
		default:
			$skin = $txt['arcade_default'];
	}
	switch($modSettings['arcadeList'])
	{
		case 1:
			$list = $txt['arcade_list1'];
			break;
		case 2:
			$list = $txt['arcade_list2'];
			break;
		default:
			$list = $txt['arcade_list0'];
	}

	$context['profile_fields'] = array(
		'notifications' => array(
			'type' => 'callback',
			'callback_func' => 'arcade_notification',
		),
		'games_per_page' => array(
			'label' => $txt['arcade_user_gamesPerPage'],
			'type' => 'select',
			'options' => array(
				0 => sprintf($txt['arcade_user_gamesPerPage_default'], $modSettings['gamesPerPage']),
				5 => 5,
				10 => 10,
				20 => 20,
				25 => 25,
				50 => 50,
			),
			'cast' => 'int',
			'validate' => 'int',
			'value' => isset($arcadeSettings['games_per_page']) ? $arcadeSettings['games_per_page'] : 0,
		),
		'scores_per_page' => array(
			'label' => $txt['arcade_user_scoresPerPage'],
			'type' => 'select',
			'options' => array(
				0 => sprintf($txt['arcade_user_scoresPerPage_default'], $modSettings['scoresPerPage']),
				5 => 5,
				10 => 10,
				20 => 20,
				25 => 25,
				50 => 50,
			),
			'cast' => 'int',
			'validate' => 'int',
			'value' => isset($arcadeSettings['scores_per_page']) ? $arcadeSettings['scores_per_page'] : 0,
		),
	);

	if (allowedTo('arcade_skin'))
		$context['profile_fields'] += array(
			'skin' => array(
				'label' => $txt['arcade_user_skin'],
				'type' => 'select',
				'options' => array(
					0 => sprintf($txt['arcade_user_default'], $skin),
					1 => $txt['arcade_default'],
					2 => $txt['arcade_skin_a'],
					3 => $txt['arcade_skin_b'],
				),
				'cast' => 'int',
				'validate' => 'int',
				'value' => isset($arcadeSettings['skin']) ? $arcadeSettings['skin'] : 0,
			),
		);
		
	if (allowedTo('arcade_list'))
		$context['profile_fields'] += array(	
			'list' => array(
				'label' => $txt['arcade_user_list'],
				'type' => 'select',
				'options' => array(
					0 => sprintf($txt['arcade_user_default'], $list),
					1 => $txt['arcade_list0'],
					2 => $txt['arcade_list1'],
					3 => $txt['arcade_list2'],
				),
				'cast' => 'int',
				'validate' => 'int',
				'value' => isset($arcadeSettings['list']) ? $arcadeSettings['list'] : 0,
			),
		);

	if (!empty($modSettings['disableCustomPerPage']))
	{
		unset($context['profile_fields']['games_per_page']);
		unset($context['profile_fields']['scores_per_page']);
	}

	if (isset($_REQUEST['save']))
	{
		checkSession('post');

		$updates = array();

		$errors = false;

		foreach ($events as $event)
		{
			foreach ($event['notification'] as $notify => $default)
			{
				if (empty($_POST[$notify]))
					$updates[] = array($memID, $notify, 0);
				else
					$updates[] = array($memID, $notify, 1);
			}
		}

		foreach ($context['profile_fields'] as $id => $field)
		{
			if ($id == 'notifications' || !isset($_POST[$id]))
				continue;

			if ($field['cast'] == 'int')
				$_POST[$id] = (int) $_POST[$id];

			if ($field['type'] == 'select')
			{
				if (isset($field['options'][$_POST[$id]]))
					$updates[] = array($memID, $id, $_POST[$id]);
			}
		}

		if (!$errors)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}arcade_members
				WHERE id_member = {int:member}
				LIMIT 1',
				array(
					'member' => $memID == 0 ? $user_info['id'] : $memID,
				)
			);
			$row = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			if (!empty($row))
			{
				foreach ($updates as $update)
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_members
						SET {raw:variable} = {int:value}
						WHERE id_member = {int:member}',
						array(
							'member' => $update[0],
							'variable' => $update[1],
							'value' => $update[2],
						)
					);
				}
			}
			else
			{
				$member = $memID == 0 ? $user_info['id'] : $memID;
				$variables = array('id_member', 'arena_invite', 'arena_match_end', 'arena_new_round', 'champion_email', 'champion_pm', 'games_per_page', 'new_champion_any', 'new_champion_own', 'scores_per_page', 'skin', 'list');
				foreach ($updates as $update)
					$new[$update[1]] = $update[2];

				foreach ($variables as $variable)
				{
					if ($variable == 'id_member')
						$new['id_member'] = $member;
					elseif (empty($new[$variable]))
						$new[$variable] = 0;
				}

				$smcFunc['db_insert']('replace',
					'{db_prefix}arcade_members',
					array(
						'id_member' => 'int',
						'arena_invite' => 'int',
						'arena_match_end' => 'int',
						'arena_new_round' => 'int',
						'champion_email' => 'int',
						'champion_pm' => 'int',
						'games_per_page' => 'int',
						'new_champion_any' => 'int',
						'new_champion_own' => 'int',
						'scores_per_page' => 'int',
						'skin' => 'int',
						'list' => 'int'
					),
					array(
						(int)$member,
						(int)$new['arena_invite'],
						(int)$new['arena_match_end'],
						(int)$new['arena_new_round'],
						(int)$new['champion_email'],
						(int)$new['champion_pm'],
						(int)$new['games_per_page'],
						(int)$new['new_champion_any'],
						(int)$new['new_champion_own'],
						(int)$new['scores_per_page'],
						(int)$new['skin'],
						(int)$new['list']
					),
					array('id_member')
				);
			}

			redirectexit('action=profile;area=arcadeSettings;u=' . $memID);
		}
	}

	$context['notifications'] = array();

	foreach ($events as $event)
	{
		foreach ($event['notification'] as $notify => $default)
		{
			$context['notifications'][$notify] = array(
				'id' => $notify,
				'text' => $txt['arcade_notification_' . $notify],
				'value' => isset($arcadeSettings[$notify]) ? (bool) $arcadeSettings[$notify] : $default,
				'default' => !isset($arcadeSettings[$notify])
			);
		}
	}

	// Template
	$context['profile_custom_submit_url'] = $scripturl . '?action=profile;area=arcadeSettings;u=' . $memID . ';save';
	$context['page_desc'] = $txt['arcade_usersettings_desc'];
	$context['sub_template'] = 'edit_options';
}
?>