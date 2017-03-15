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

/*	This file contains most functions used by Arcade

	void arcadePermissionQuery()
		- ???

	void PostPermissionCheck()
		- ???

	array loadArcadeSettings()
		- ???

	string getSubmitSystem()
		- ???

	array submitSystemInfo()
		- ???

	int loadGame()
		- ???

	array getGameInfo()
		- ???

	array getGameOfDay()
		- ???

	array newGameOfDay()
		- ???

	array getRecommendedGames()
		- ???

	boolean updateGame()
		- ???

	array arcadeGetEventTypes()
		- ???

	boolean arcadeEvent()
		- ???

	void arcadeEventNewChampion()
		- ???

	void arcadeEventArenaInvite()
		- ???

	boolean checkNotificationReceiver()
		- ???

	array checkNotificationReceivers()
		- ???

	void addNotificationRecievers()
		- ???

	array ArcadeLatestScores()
		- ???

	array SaveScore()
		- ???

	boolean deleteScores()
		- ???

	array loadMatch()
		- ???

	int createMatch()
		- ???

	boolean matchAddPlayers()
		- ???

	boolean deleteMatch()
		- ???

	void ArcadeXMLOutput()
		- ???

	void Array2XML()
		- ???

	array memberAllowedTo()
		- ???

	float microtime_float()
		- ???

	string duration_format()
		- ???

	array arcade_online()
		- ???
*/

function loadGame($id_game, $from_admin = false)
{
	global $scripturl, $txt, $db_prefix, $user_info, $smcFunc, $modSettings, $context;

	if (is_numeric($id_game) && isset($context['arcade']['game_data'][$id_game]))
		return $id_game;
	elseif (isset($context['arcade']['game_ids'][$id_game]))
		return $context['arcade']['game_ids'][$id_game];

	if ($from_admin)
		$where = "game.id_game = {int:game}";
	elseif (is_numeric($id_game))
		$where = "{raw:query_see_game}
			AND game.id_game = {int:game}";
	elseif ($id_game === 'random')
		$where = "{raw:query_see_game}
		ORDER BY RAND()";
	else
		$where = "{raw:query_see_game}
			AND game.internal_name = {string:game}";

	$result = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.description, game.game_rating, game.num_plays,
			game.game_file, game.game_directory, game.submit_system, game.internal_name,
			game.score_type, game.thumbnail, game.thumbnail_small,
			game.help, game.enabled, game.member_groups, game.extra_data, game.id_cat,
			IFNULL(score.id_score,0) AS id_score, IFNULL(score.score, 0) AS champ_score,
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, score.player_name) AS real_name,
			IFNULL(score.end_time, 0) AS champion_time, IFNULL(favorite.id_favorite, 0) AS is_favorite,
			IFNULL(category.id_cat, 0) AS id_cat, IFNULL(category.cat_name, {string:string_empty}) As cat_name,
			IFNULL(pb.id_score, 0) AS id_pb, IFNULL(pb.score, 0) AS personal_best, num_favorites
		FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_scores AS score ON (score.id_score = game.id_champion_score)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
			LEFT JOIN {db_prefix}arcade_favorite AS favorite ON (favorite.id_game = game.id_game AND favorite.id_member = {int:member})
			LEFT JOIN {db_prefix}arcade_scores AS pb ON (pb.id_game = game.id_game AND pb.id_member = {int:member} AND pb.personal_best = 1)
			LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)
		WHERE ' . $where . '
		LIMIT 1',
		array(
			'game' => $id_game,
			'string_empty' => '',
			'member' => $user_info['id'],
			'query_see_game' => $user_info['query_see_game']
		)
	);

	// No game was found
	if ($smcFunc['db_num_rows']($result) == 0)
		return false;

	$game = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	$context['arcade']['game_data'][$game['id_game']] = $game;
	$context['arcade']['game_ids'][$game['internal_name']] = $game['id_game'];

	return $game['id_game'];
}

// Updates Game
function updateGame($id_game, $gameOptions, $log = false)
{
	global $scripturl, $sourcedir, $db_prefix, $user_info, $smcFunc;

	if (empty($id_game))
		fatal_error('arcade_game_update_error', false);

	$gameUpdates = array();
	$updateValues = array();

	if (isset($gameOptions['internal_name']))
	{
		$gameUpdates[] = "internal_name = {string:internal_name}";
		$updateValues['internal_name'] = $gameOptions['internal_name'];
	}

	if (isset($gameOptions['name']))
	{
		$gameUpdates[] = "game_name = {string:game_name}";
		$updateValues['game_name'] = $gameOptions['name'];
	}

	if (isset($gameOptions['description']))
	{
		$gameUpdates[] = "description = {string:description}";
		$updateValues['description'] = $gameOptions['description'];
	}

	if (isset($gameOptions['help']))
	{
		$gameUpdates[] = "help = {string:help}";
		$updateValues['help'] = $gameOptions['help'];
	}

	if (isset($gameOptions['thumbnail']))
	{
		$gameUpdates[] = "thumbnail = {string:thumbnail}";
		$updateValues['thumbnail'] = $gameOptions['thumbnail'];
	}

	if (isset($gameOptions['thumbnail_small']))
	{
		$gameUpdates[] = "thumbnail_small = {string:thumbnail_small}";
		$updateValues['thumbnail_small'] = $gameOptions['thumbnail_small'];
	}

	if (isset($gameOptions['game_file']))
	{
		$gameUpdates[] = "game_file = {string:game_file}";
		$updateValues['game_file'] = $gameOptions['game_file'];
	}

	if (isset($gameOptions['game_directory']))
	{
		$gameUpdates[] = "game_directory = {string:game_directory}";
		$updateValues['game_directory'] = $gameOptions['game_directory'];
	}

	if (isset($gameOptions['submit_system']))
	{
		$gameUpdates[] = "submit_system = {string:submit_system}";
		$updateValues['submit_system'] = $gameOptions['submit_system'];
	}

	if (isset($gameOptions['member_groups']))
	{
		$gameUpdates[] = "member_groups = {string:member_groups}";
		$updateValues['member_groups'] = implode(',', $gameOptions['member_groups']);
	}

	if (isset($gameOptions['extra_data']))
	{
		$gameUpdates[] = "extra_data = {string:extra_data}";
		$updateValues['extra_data'] = serialize($gameOptions['extra_data']);
	}

	if (isset($gameOptions['score_type']))
	{
		$gameUpdates[] = "score_type = {int:score_type}";
		$updateValues['score_type'] = $gameOptions['score_type'];

		require_once($sourcedir . '/ArcadeMaintenance.php');
		fixScores($id_game, $gameOptions['score_type']);
	}

	if (isset($gameOptions['num_plays']))
	{
		if ($gameOptions['num_plays'] == '+')
		{
			$gameUpdates[] = "num_plays = num_plays + 1";
		}
		else
		{
			$gameUpdates[] = "num_plays = {int:num_plays}";
			$updateValues['num_plays'] = $gameOptions['num_plays'];
		}
	}

	if (isset($gameOptions['num_rates']))
	{
		if ($gameOptions['num_rates'] == '+')
			$gameUpdates[] = "num_rates = num_rates + 1";
		elseif ($gameOptions['num_rates'] == '-')
			$gameUpdates[] = "num_rates = num_rates - 1";
		else
		{
			$gameUpdates[] = "num_rates = {int:num_rates}";
			$updateValues['num_rates'] = $gameOptions['num_rates'];
		}
	}

	if (isset($gameOptions['num_favorites']))
	{
		if ($gameOptions['num_favorites'] == '+')
			$gameUpdates[] = "num_favorites = num_favorites + 1";
		elseif ($gameOptions['num_favorites'] == '-')
			$gameUpdates[] = "num_favorites = num_favorites - 1";
		else
		{
			$gameUpdates[] = "num_favorites = {int:num_favorites}";
			$updateValues['num_favorites'] = $gameOptions['num_favorites'];
		}
	}

	if (isset($gameOptions['rating']))
	{
		$gameUpdates[] = "game_rating = {float:rating}";
		$updateValues['rating'] = $gameOptions['rating'];
	}

	if (isset($gameOptions['category']))
	{
		$gameUpdates[] = "id_cat = {int:category}";
		$updateValues['category'] = $gameOptions['category'];
		$updateCat = true;
	}

	if (isset($gameOptions['champion']))
	{
		$gameUpdates[] = "id_champion = {int:champion}";
		$updateValues['champion'] = $gameOptions['champion'];
	}

	if (isset($gameOptions['champion_score']))
	{
		$gameUpdates[] = "id_champion_score = {int:champion_score}";
		$updateValues['champion_score'] = $gameOptions['champion_score'];
	}

	if (isset($gameOptions['enabled']))
	{
		$gameUpdates[] = "enabled = {int:enabled}";
		$updateValues['enabled'] = $gameOptions['enabled'] ? 1 : 0;
		$updateCat = true;
	}

	if (isset($gameOptions['local_permissions']))
	{
		$gameUpdates[] = "local_permissions = {int:local_permissions}";
		$updateValues['local_permissions'] = $gameOptions['local_permissions'];
	}

	if (empty($gameUpdates))
		return;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_games
		SET ' . implode(', ', $gameUpdates) . '
		WHERE id_game = {int:game}',
		array_merge($updateValues, array(
			'game' => $id_game,
		))
	);

	if ($log)
		logAction('arcade_update_game', array('game' => $id_game));

	if (!empty($updateCat))
		updateCategoryStats();

	return true;
}

// Event system
function arcadeGetEventTypes($id = '')
{
	$events = array(
		'championEmail' => array(
			'id' => 'championEmail',
			'func' => 'arcadeEventChampionEmail',
			'notification' => array(
				'championEmail' => false,
			)
		),
		'championPM' => array(
			'id' => 'championPM',
			'func' => 'arcadeEventChampionEmail',
			'notification' => array(
				'championPM' => false,
			)
		),
		'new_champion' => array(
			'id' => 'new_champion',
			'func' => 'arcadeEventNewChampion',
			'notification' => array(
				'new_champion_own' => true,
				'new_champion_any' => false
			)
		),
		'arena_invite' => array(
			'id' => 'arena_invite',
			'func' => 'arcadeEventArenaGeneral',
			'notification' => array(
				'arena_invite' => true,
			)
		),
		'arena_new_round' => array(
			'id' => 'arena_new_round',
			'func' => 'arcadeEventArenaGeneral',
			'notification' => array(
				'arena_new_round' => true,
			)
		),
		'arena_match_end' => array(
			'id' => 'arena_match_end',
			'func' => 'arcadeEventArenaGeneral',
			'notification' => array(
				'arena_match_end' => true,
			)
		),
	);

	if (empty($id))
		return $events;

	if (isset($events[$id]))
		return $events[$id];

	fatal_error('Hacking attempt...');
}

function arcadeEvent($id_event, $data = array())
{
	global $smcFunc, $db_prefix, $scripturl, $txt, $user_info, $sourcedir, $modSettings, $language, $webmaster_email, $mbname, $memberContext;

	$modSettings['gamesEmail'] = !empty($modSettings['gamesEmail']) ? $modSettings['gamesEmail'] : $webmaster_email;
	$notifications = array('new_champion', 'arena_invite', 'match_end', 'new_round');
	require_once($sourcedir . '/Subs-Post.php');
	if (filter_var($modSettings['gamesEmail'], FILTER_VALIDATE_EMAIL) === false)
		$modSettings['gamesEmail'] = $txt['arcade_default_email'];
	list($arcadeSettings, $emails, $pvts, $from) = array(array(), array(), array(), array());
	loadLanguage('Arcade');
	loadLanguage('ArcadeAdmin');

	// change notifications language to the forum default for bulk notifications
	$lang = $language;
	loadLanguage('ArcadeEmail', $lang, false, false);

	if ($id_event == 'get' && empty($data))
		return arcadeGetEventTypes();
	else
		$event = arcadeGetEventTypes($id_event);

	$replacements = array(
		'ARCADE_SETTINGS_URL' => $scripturl . '?action=profile;area=arcadeSettings'
	);
	$pms = array();

	$event['func']($event, $replacements, $pms, $data);

	if (empty($pms))
		return true;

	// ensure the default for the switch
	if (!in_array($id_event, $notifications))
		return false;

	// email variables
	$old_champ = $data['game']['champion']['name'];
	$new_champ = $user_info['name'];
	$game = '<a href="' . str_replace('action=arcade', 'action=ingressarcade', $data['game']['url']['highscore']) . '">' . $data['game']['name'] . '</a>';
	$game_pm = '[url=' . $data['game']['url']['highscore'] . ']' . $data['game']['name'] . '[/url]';

	// set the sender as the user ID for posting from arcade settings else use the current user ID
	if ((!empty($modSettings['arcadePosterid'])) && (int)$modSettings['arcadePosterid'] !== $user_info['id'])
	{
		$id = (int)$modSettings['arcadePosterid'];
		loadMemberData($id, false, 'normal');
		loadMemberContext($id);
		$from = array('id' => $id, 'name' => $memberContext[$id]['name'], 'username' => $memberContext[$id]['username']);
	}
	$from =	empty($from) ? $from = array('id' => $user_info['id'], 'name' => $user_info['name'], 'username' => $user_info['username']) : $from;

	$request = $smcFunc['db_query']('', '
			SELECT id_member, variable, value
			FROM {db_prefix}arcade_settings
			ORDER BY id_member ASC',
			array(
			)
		);

	while ($row = $smcFunc['db_fetch_row']($request))
	{
		if (empty($arcadeSettings[$row[0]]))
			$arcadeSettings[$row[0]] = array($row[1] => $row[2]);
		else
			$arcadeSettings[$row[0]] += array($row[1] => $row[2]);
	}
	$smcFunc['db_free_result']($request);

	if (!empty($arcadeSettings))
		$request = $smcFunc['db_query']('', '
			SELECT mem.id_member, mem.email_address, mem.lngfile, mem.pm_email_notify
			FROM {db_prefix}members AS mem
			WHERE mem.id_member IN({array_int:members})
			ORDER BY mem.lngfile',
			array(
				'members' => array_keys($arcadeSettings),
			)
		);

	while ($rowmember = $smcFunc['db_fetch_assoc']($request))
	{
		// Opt out of a notification depending on certain condition(s)
		if ($rowmember['id_member'] == $user_info['id'])
			continue;
		elseif (!empty($arcadeSettings[$rowmember['id_member']]['new_champion_any']) && $data['game']['champion']['id'] == $user_info['id'])
			continue;
		elseif (empty($arcadeSettings[$rowmember['id_member']]['new_champion_own']) && empty($arcadeSettings[$rowmember['id_member']]['new_champion_any']))
			continue;

		if (!empty($modSettings['gamesNotificationsBulk']))
		{
			// Only send email if the user's SMF-Email or Arcade-PM notification is disabled & their arcade email setting is enabled
			if (empty($rowmember['pm_email_notify']) && !empty($arcadeSettings[$rowmember['id_member']]['championEmail']))
				$emails[] = $rowmember['email_address'];
			elseif (!empty($arcadeSettings[$rowmember['id_member']]['championEmail']) && empty($arcadeSettings[$rowmember['id_member']]['championPM']))
				$emails[] = $rowmember['email_address'];

			// Now send the PM notification if it is enabled
			if (!empty($arcadeSettings[$rowmember['id_member']]['championPM']))
				$pvts[] = $rowmember['id_member'];
		}
		else
		{
			// change notifications language for the specific destined user else the forum default
			$lang = empty($rowmember['lngfile']) || empty($modSettings['userLanguage']) ? $language : $rowmember['lngfile'];
			loadLanguage('ArcadeEmail', $lang, false, false);
			$adj = $data['game']['champion']['id'] == $rowmember['id_member'] ? 'own' : 'any';

			// Only send email if the user's SMF-Email or Arcade-PM notification is disabled & their Arcade-Email setting is enabled
			if (empty($rowmember['pm_email_notify']) && !empty($arcadeSettings[$rowmember['id_member']]['championEmail']))
			{
				switch ($id_event)
				{
					case 'new_champion':
						$message = str_replace(array('{old_champion.name}', '{GAMENAME}', '{champion.name}', '{old_champion.name}', '{ARCADE_SETTINGS_URL}', '{REGARDS}', '{champion.score}', '{play.the.game}'), array($old_champ, $game, $new_champ, $old_champ, $txt['arcade_email_profile'], $mbname, $data['score']['score'], '<a href="' . str_replace('action=arcade', 'action=ingressarcade', $data['game']['url']['play']) . '">' . $txt['arcade_pm_play_game'] . '</a>'), $txt['notification_arcade_new_champion_' . $adj . '_body']);
						$subject = str_replace(array('{old_champion.name}', '{GAMENAMESUB}'), array($old_champ, $data['game']['name']), $txt['notification_arcade_new_champion_' . $adj . '_subject']);
						$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $message . '</div></body></html>';
						$replacements = array(
							'SUBJECT' => $subject,
							'MESSAGE' => $htmlMessage,
							'SENDER' => un_htmlspecialchars($mbname),
							'READLINK' => str_replace('action=arcade', 'action=ingressarcade', $data['game']['url']['highscore']),
							'REPLYLINK' => str_replace('action=arcade', 'action=ingressarcade', $data['game']['url']['play']),
							'TOLIST' => $rowmember['email_address'],
							'old_champion.name' => $old_champ,
							'champion.score' => $data['score']['score'],
							'GAMENAMESUB' => $data['game']['name'],
							'GAMENAME' => $game,
							'play.the.game' => '<a href="' . str_replace('action=arcade', 'action=ingressarcade', $data['game']['url']['play']) . '">' . $txt['arcade_pm_play_game'] . '</a>',
							'champion.name' => $new_champ,
							'ARCADE_SETTINGS_URL' => $txt['arcade_email_profile'],
							'REGARDS' => un_htmlspecialchars($mbname)
						);
						$email_template = 'notification_arcade_new_champion_' . $adj;
						break;
					default:
						$message = str_replace(array('{MATCHURL}', '{MATCHNAME}', '{ARCADE_SETTINGS_URL}', '{REGARDS}'), array('<a href="' . $data['match_url'] . '">' . $txt['arcade_pm_join_match'] . '</a>', $data['match_name'], $txt['arcade_email_profile'], $mbname), $txt['notification_arcade_' . $id_event . '_body']);
						$subject = str_replace('{MATCHNAME}', $data['match_name'], $txt['notification_arcade_' . $id_event . '_subject']);
						$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $message . '</div></body></html>';
						$replacements = array(
							'SUBJECT' => $subject,
							'MESSAGE' => $htmlMessage,
							'SENDER' => un_htmlspecialchars($mbname),
							'READLINK' => $data['match_name'],
							'REPLYLINK' => $data['match_url'] . ';arcade_email=1',
							'TOLIST' => $rowmember['email_address'],
							'MATCHURL' => '<a href="' . $data['match_url'] . ';arcade_email=1">' . $txt['arcade_pm_join_match'] . '</a>',
							'MATCHNAME' => $data['match_name'],
							'ARCADE_SETTINGS_URL' => $txt['arcade_email_profile'],
							'REGARDS' => un_htmlspecialchars($mbname)
						);
						$email_template = 'notification_arcade_' . $id_event;
				}

				$emaildata = loadEmailTemplate($email_template, $replacements, $lang, false);
				$emailsSend = sendmail(array($rowmember['email_address']), $emaildata['subject'], $emaildata['body'], $modSettings['gamesEmail'], false, true, 2, null, true);
			}
			elseif (!empty($arcadeSettings[$rowmember['id_member']]['championEmail']) && empty($arcadeSettings[$rowmember['id_member']]['championPM']))
			{
				switch ($id_event)
				{
					case 'new_champion':
						$message = str_replace(array('{old_champion.name}', '{GAMENAME}', '{champion.name}', '{old_champion.name}', '{ARCADE_SETTINGS_URL}', '{REGARDS}', '{champion.score}', '{play.the.game}'), array($old_champ, $game, $new_champ, $old_champ, $txt['arcade_email_profile'], $mbname, $data['score']['score'], '<a href="' . str_replace('action=arcade', 'action=ingressarcade">', $data['game']['url']['play']) . '">' . $txt['arcade_pm_play_game'] . '</a>'), $txt['notification_arcade_new_champion_' . $adj . '_body']);
						$subject = str_replace(array('{old_champion.name}', '{GAMENAMESUB}'), array($old_champ, $data['game']['name']), $txt['notification_arcade_new_champion_' . $adj . '_subject']);
						$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $message . '</div></body></html>';
						$replacements = array(
							'SUBJECT' => $subject,
							'MESSAGE' => $htmlMessage,
							'SENDER' => un_htmlspecialchars($mbname),
							'READLINK' => str_replace('action=arcade', 'action=ingressarcade', $data['game']['url']['highscore']),
							'REPLYLINK' => str_replace('action=arcade', 'action=ingressarcade', $data['game']['url']['play']),
							'TOLIST' => $rowmember['email_address'],
							'old_champion.name' => $old_champ,
							'champion.score' => $data['score']['score'],
							'GAMENAMESUB' => $data['game']['name'],
							'GAMENAME' => $game,
							'play.the.game' => '<a href="' . str_replace('action=arcade', 'action=ingressarcade', $data['game']['url']['play']) . '">' . $txt['arcade_pm_play_game'] . '</a>',
							'champion.name' => $new_champ,
							'ARCADE_SETTINGS_URL' => $txt['arcade_email_profile'],
							'REGARDS' => un_htmlspecialchars($mbname)
						);
						$email_template = 'notification_arcade_new_champion_' . $adj;
						break;
					default:
						$message = str_replace(array('{MATCHURL}', '{MATCHNAME}', '{ARCADE_SETTINGS_URL}', '{REGARDS}'), array('<a href="' . $data['match_url'] . ';arcade_email=1">' . $txt['arcade_pm_join_match'] . '</a>', $data['match_name'], $txt['arcade_email_profile'], $mbname), $txt['notification_arcade_' . $id_event . '_body']);
						$subject = str_replace('{MATCHNAME}', $data['match_name'], $txt['notification_arcade_' . $id_event . '_subject']);
						$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $message . '</div></body></html>';
						$replacements = array(
							'SUBJECT' => $subject,
							'MESSAGE' => $htmlMessage,
							'SENDER' => un_htmlspecialchars($mbname),
							'READLINK' => $data['match_name'],
							'REPLYLINK' => $data['match_url'] . ';arcade_email=1',
							'TOLIST' => $rowmember['email_address'],
							'MATCHURL' => '<a href="' . $data['match_url'] . ';arcade_email=1">' . $txt['arcade_pm_join_match'] . '</a>',
							'MATCHNAME' => $data['match_name'],
							'ARCADE_SETTINGS_URL' => $txt['arcade_email_profile'],
							'REGARDS' => un_htmlspecialchars($mbname)
						);
						$email_template = 'notification_arcade_' . $id_event;
				}

				$emaildata = loadEmailTemplate($email_template, $replacements, $lang, false);
				$emailsSend = sendmail(array($rowmember['email_address']), $emaildata['subject'], $emaildata['body'], $modSettings['gamesEmail'], false, true, 2, null, true);
			}

			// Now send the PM notification if it is enabled
			if (!empty($arcadeSettings[$rowmember['id_member']]['championPM']))
			{
				switch ($id_event)
				{
					case 'new_champion':
						$message = str_replace(array('{old_champion.name}', '{GAMENAME}', '{champion.name}', '{old_champion.name}', '{ARCADE_SETTINGS_URL}', '{REGARDS}', '{champion.score}', '{play.the.game}'), array($old_champ, $game_pm, $new_champ, $old_champ, '[url=' . $scripturl . '?action=profile;area=arcadeSettings;' . ']' . $txt['arcade_pm_profile'] . '[/url]', $mbname, $data['score']['score'], '[url=' . $data['game']['url']['play'] . ']' . $txt['arcade_pm_play_game'] . '[/url]'), $txt['notification_arcade_new_champion_' . $adj . 'PM_body']);
						$subject = str_replace(array('{old_champion.name}', '{GAMENAMESUB}'), array($old_champ, $data['game']['name']), $txt['notification_arcade_new_champion_' . $adj . 'PM_subject']);
						break;
					default:
						$message = str_replace(array('{MATCHURL}', '{MATCHNAME}', '{ARCADE_SETTINGS_URL}', '{REGARDS}'), array('[url=' . $data['match_url'] . ']' . $txt['arcade_pm_join_match'] . '[/url]', $data['match_name'], '[url=' . $scripturl . '?action=profile;area=arcadeSettings;' . ']' . $txt['arcade_pm_profile'] . '[/url]', $mbname), $txt['notification_arcade_' . $id_event . '_body']);
						$subject = str_replace('{MATCHNAME}', $data['match_name'], $txt['notification_arcade_' . $id_event . '_subject']);
				}
				sendpm (array('to' => array($rowmember['id_member']), 'bcc' => array()), $subject, $message, '0', $from, '0');
			}
		}
	}
	$smcFunc['db_free_result']($request);

	if (!empty($modSettings['gamesNotificationsBulk']))
	{
		// bulk Emails
		if (!empty($emails))
		{
			switch ($id_event)
			{
				case 'new_champion':
					$message = str_replace(array('{old_champion.name}', '{GAMENAME}', '{champion.name}', '{old_champion.name}', '{ARCADE_SETTINGS_URL}', '{REGARDS}', '{champion.score}', '{play.the.game}'), array($old_champ, $game, $new_champ, $old_champ, $txt['arcade_email_profile'], $mbname, $data['score']['score'], '<a href="' . $data['game']['url']['play'] . '">' . $txt['arcade_pm_play_game'] . '</a>'), $txt['notification_arcade_new_champion_any_body']);
					$subject = str_replace(array('{old_champion.name}', '{GAMENAMESUB}'), array($old_champ, $data['game']['name']), $txt['notification_arcade_new_champion_any_subject']);
					$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $subject . '</div></body></html>';
					$replacements = array(
						'SUBJECT' => $subject,
						'MESSAGE' => $htmlMessage,
						'SENDER' => un_htmlspecialchars($mbname),
						'READLINK' => $data['game']['url']['highscore'] . ';arcade_email=1;hs=1',
						'REPLYLINK' => $data['game']['url']['play'] . ';arcade_email=1',
						'TOLIST' => $emails,
						'old_champion.name' => $old_champ,
						'champion.score' => $data['score']['score'],
						'GAMENAMESUB' => $data['game']['name'],
						'GAMENAME' => $game,
						'play.the.game' => '<a href="' . $data['game']['url']['play'] . ';arcade_email=1">' . $txt['arcade_pm_play_game'] . '</a>',
						'champion.name' => $new_champ,
						'ARCADE_SETTINGS_URL' => $txt['arcade_email_profile'],
						'REGARDS' => un_htmlspecialchars($mbname)
					);
					$email_template = 'notification_arcade_new_champion_any';
					break;
				default:
					$message = str_replace(array('{MATCHURL}', '{MATCHNAME}', '{ARCADE_SETTINGS_URL}', '{REGARDS}'), array('<a href="' . $data['match_url'] . ';arcade_email=1">' . $txt['arcade_pm_join_match'] . '</a>', $data['match_name'], $txt['arcade_email_profile'], $mbname), $txt['notification_arcade_' . $id_event . '_body']);
					$subject = str_replace('{MATCHNAME}', $data['match_name'], $txt['notification_arcade_' . $id_event . '_subject']);
					$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $subject . '</div></body></html>';
					$replacements = array(
						'SUBJECT' => $subject,
						'MESSAGE' => $htmlMessage,
						'SENDER' => un_htmlspecialchars($mbname),
						'READLINK' => $data['match_name'],
						'REPLYLINK' => $data['match_url'] . ';arcade_email=1',
						'TOLIST' => $rowmember['email_address'],
						'MATCHURL' => '<a href="' . $data['match_url'] . ';arcade_email=1">' . $txt['arcade_pm_join_match'] . '</a>',
						'MATCHNAME' => $data['match_name'],
						'ARCADE_SETTINGS_URL' => $txt['arcade_email_profile'],
						'REGARDS' => un_htmlspecialchars($mbname)
					);
					$email_template = 'notification_arcade_' . $id_event;
			}

			$emaildata = loadEmailTemplate($email_template, $replacements, $lang, false);
			$emailsSend = sendmail($emails, $emaildata['subject'], $emaildata['body'], $modSettings['gamesEmail'], false, true, 2, null, true);
		}

		// bulk PMs
		if (!empty($pvts))
		{
			switch ($id_event)
			{
				case 'new_champion':
					$message = str_replace(array('{old_champion.name}', '{GAMENAME}', '{champion.name}', '{old_champion.name}', '{ARCADE_SETTINGS_URL}', '{REGARDS}', '{champion.score}', '{play.the.game}'), array($old_champ, $game_pm, $new_champ, $old_champ, '[url=' . $scripturl . '?action=profile;area=arcadeSettings;' . ']' . $txt['arcade_pm_profile'] . '[/url]', $mbname, $data['score']['score'], '[url=' . $data['game']['url']['play'] . ']' . $txt['arcade_pm_play_game'] . '[/url]'), $txt['notification_arcade_new_champion_anyPM_body']);
					$subject = str_replace(array('{old_champion.name}', '{GAMENAMESUB}'), array($old_champ, $data['game']['name']), $txt['notification_arcade_new_champion_anyPM_subject']);
					break;
				default:
					$message = str_replace(array('{MATCHURL}', '{MATCHNAME}', '{ARCADE_SETTINGS_URL}', '{REGARDS}'), array('[url=' . $data['match_url'] . ']' . $txt['arcade_pm_join_match'] . '[/url]', $data['match_name'], '[url=' . $scripturl . '?action=profile;area=arcadeSettings;' . ']' . $txt['arcade_pm_profile'] . '[/url]', $mbname), $txt['notification_arcade_' . $id_event . '_body']);
					$subject = str_replace('{MATCHNAME}', $data['match_name'], $txt['notification_arcade_' . $id_event . '_subject']);
			}

			sendpm (array('to' => $pvts, 'bcc' => array()), $subject, $message, false, $from, 0);
		}
	}

	return true;
}

function arcadeEventNewChampion($event, &$replaces, &$pms, $data)
{
	global $smcFunc, $scripturl, $txt, $user_info;

	$replaces += array(
		'champion.name' => $data['member']['name'],
		'champion.score' => comma_format($data['score']['score']),
		'champion.url' => $scripturl . '?action=profile;u=' . $data['member']['id'],
		'GAMENAME' => $data['game']['name'],
		'GAMEURL' => $scripturl . '?action=arcade;game=' . $data['game']['id'] . ';#playgame',
	);

	if ($data['game']['is_champion'])
	{
		$replaces += array(
			'old_champion.name' => $data['game']['champion']['name'],
			'old_champion.score' => $data['game']['champion']['score'],
			'old_champion.url' => $scripturl . '?action=profile;u=' . $data['member']['id'],
		);

		$send = checkNotificationReceiver($data['game']['champion']['id'], $event, 'new_champion_own');
		$send &= $data['member']['id'] != $data['game']['champion']['id'];

		if ($send)
			$pms[$data['game']['champion']['id']] = 'new_champion_own';

		addNotificationRecievers($pms, $event, 'new_champion_any');
	}
}

function arcadeEventChampionEmail()
{
	// just to satisfy the existing sub-routine of adding parameters to the notifications array
	return false;
}

function arcadeEventArenaGeneral($event, &$replaces, &$pms, $data)
{
	global $db_prefix, $scripturl, $txt, $user_info;

	$replaces += array(
		'MATCHURL' => $data['match_url'],
		'MATCHNAME' => $data['match_name'],
	);

	if (empty($data['players']))
		return;

	$pms = $pms + checkNotificationReceivers($data['players'], $event, $event['id']);
}

// Check
function checkNotificationReceiver($member, $event, $type)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT value
		FROM {db_prefix}arcade_settings AS arcset
		WHERE (arcset.variable = {string:type})
			AND arcset.id_member = {int:member}',
		array(
			'type' => $type,
			'member' => $member
		)
	);

	$numRow = $smcFunc['db_num_rows']($request);
	list ($value) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	if ($numRow == 0)
		return $event['notification'][$type];

	return $value;
}

function checkNotificationReceivers($members, $event, $type)
{
	global $smcFunc;

	if ($event['notification'][$type])
		$where = '((arcset.variable = {string:type} AND arcset.value = 1) OR ISNULL(arcset.value))';
	else
		$where = '(arcset.variable = {string:type} AND arcset.value = 1)';

	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.real_name, mem.email_address
		FROM {db_prefix}members AS mem
			LEFT JOIN {db_prefix}arcade_settings AS arcset ON (arcset.id_member = mem.id_member)
		WHERE ' . $where .'
			AND mem.id_member IN({array_int:members})',
		array(
			'type' => $type,
			'members' => $members
		)
	);

	$pms = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($pms[$row['id_member']]))
			$pms[$row['id_member']] = $type;
	}
	$smcFunc['db_free_result']($request);

	return $pms;
}

// PM
function addNotificationRecievers(&$pms, $event, $type)
{
	global $db_prefix, $smcFunc;

	if ($event['notification'][$type])
		$where = '((arcset.variable = {string:type} AND arcset.value = 1) OR ISNULL(arcset.value))';
	else
		$where = '(arcset.variable = {string:type} AND arcset.value = 1)';

	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.real_name, mem.email_address
		FROM {db_prefix}members AS mem
			LEFT JOIN {db_prefix}arcade_settings AS arcset ON (arcset.id_member = mem.id_member)
		WHERE ' . $where,
		array(
			'type' => $type,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($pms[$row['id_member']]))
			$pms[$row['id_member']] = $type;
	}
	$smcFunc['db_free_result']($request);
}
// Score saving
function SaveScore(&$game, $member, $score)
{
	global $db_prefix, $modSettings, $context, $smcFunc, $user_info, $sourcedir;

	if ($game['score_type'] == 0)
		$reverse = false;
	elseif ($game['score_type'] == 1)
		$reverse = true;

	// No error by default
	$canSave = true;
	$error = '';

	$scoreLimit = 0;

	if (!empty($modSettings['arcadeMaxScores']))
		$scoreLimit = (int) $modSettings['arcadeMaxScores'];

	if (!empty($scoreLimit))
	{
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}arcade_scores
			WHERE id_game = {int:game}
				AND id_member = {int:member}',
			array(
				'game' => $game['id'],
				'member' => $member['id']
			)
		);

		list ($scoreCount) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if ($scoreCount < $scoreLimit)
			$canSave = true;
		else
                {
			while ($scoreCount >= $scoreLimit)
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_score, score, position
					FROM {db_prefix}arcade_scores
					WHERE id_game = {int:game}
					    	AND id_member = {int:member}
					ORDER BY score ' . ($reverse ? 'DESC' : 'ASC'),
					array(
						'game' => $game['id'],
						'member' => $member['id']
					)
				);

				list ($old_id_score, $oldScore, $lPosition) = $smcFunc['db_fetch_row']($request);

				if (!$reverse)
					$deleteOld = $oldScore < $score['score'];
				else
					$deleteOld = $oldScore > $score['score'];

				if (!$deleteOld)
				{
					$canSave = false;
					$error = 'arcade_scores_limit';

					break;
				}
				else
				{
					$request = $smcFunc['db_query']('', '
						DELETE FROM {db_prefix}arcade_scores
						WHERE id_score = {int:score}',
						array(
							'score' => $old_id_score
						)
					);

					updatePositions($game, $lPosition, '- 1');

					$scoreCount--;
				}
			}
		}
	}

	// Get position
	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_scores
		WHERE score ' . ($reverse ? '<=' : '>='). ' {float:score}
			AND id_game = {int:game}',
		array(
			'score' => $score['score'],
			'game' => $game['id']
		)
	);

	list ($position) = $smcFunc['db_fetch_row']($result);
	$position++;
	$smcFunc['db_free_result']($result);

	if ($position == 1)
		$championFrom = $score['endTime'];
	else
		$championFrom = 0;

	if (!$canSave)
		return array('id' => false, 'error' => $error);

	// Update positions
	updatePositions($game, $position, '+ 1');

	$isPersonalBest = false;

	// This is my score
	if ($member['id'] != 0 && $user_info['id'] == $member['id'])
	{
		if (!$reverse)
			$isPersonalBest = $game['personal_best_score'] < $score['score'];
		else
			$isPersonalBest = $game['personal_best_score'] > $score['score'];
	}
	else
	{
		$request = $smcFunc['db_query']('', '
			SELECT score
			FROM {db_prefix}arcade_scores
			WHERE id_member = {int:member}
				AND personal_best = 1',
			array(
				'member' => $member['id']
			)
		);

		if ($smcFunc['db_num_rows'] == 0)
			$isPersonalBest = true;
		else
		{
			list ($personalBestScore) =  $smcFunc['db_fetch_row']($request);

			if (!$reverse)
				$isPersonalBest = $personalBestScore < $score['score'];
			else
				$isPersonalBest = $personalBestScore > $score['score'];
		}
		$smcFunc['db_free_result']($request);
	}

	if ($member['id'] != 0 && $game['is_personal_best'] && $isPersonalBest)
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_scores
			SET personal_best = 0
			WHERE id_game = {int:game}
				AND id_member = {int:member}',
			array(
				'game' => $game['id'],
				'member' => $member['id']
			)
		);
	}

	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_scores',
		array(
			'id_game' => 'int',
			'id_member' => 'int',
			'player_name' => 'string',
			'member_ip' => 'string',
			'score' => 'float',
			'position' => 'int',
			'duration' => 'float',
			'end_time' => 'int',
			'champion_from' => 'int',
			'champion_to' => 'int',
			'comment' => 'string',
			'personal_best' => 'int',
			'score_status' => 'string-30',
			'validate_hash' => 'string-255',
		),
		array(
			$game['id'],
			$member['id'],
			$member['name'],
			$member['ip'],
			$score['score'],
			$position,
			$score['duration'],
			$score['endTime'],
			$championFrom,
			0,
			'',
			$isPersonalBest ? 1 : 0,
			$score['status'],
			$score['hash'],
		),
		array()
	);

	$id_score = $smcFunc['db_insert_id']('{db_prefix}arcade_scores', 'id_score');
	$score['id'] = $id_score;

	if ($position == 1)
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_scores
			SET champion_to = {int:end_time}
			WHERE id_score = {int:score}',
			array(
				'end_time' => $score['endTime'],
				'score' => $game['champion']['score_id'],
			)
		);

		updateGame($game['id'], array('champion' => $member['id'], 'champion_score' => $score['id']));

		$event = array(
			'game' => &$game,
			'member' => $member,
			'score' => $score,
			'time' => $score['endTime']
		);

		arcadeEvent('new_champion', $event);
	}

	cache_put_data('arcade-stats', null, 120);

	return array(
		'id' => $id_score,
		'isPersonalBest' => $isPersonalBest,
		'position' => $position,
		'newChampion' => $position == 1
	);
}

// Delete Scores
function deleteScores(&$game, $id_score)
{
	global $scripturl, $txt, $db_prefix, $modSettings, $context, $smcFunc, $user_info, $sourcedir;

	if (!is_array($id_score) && is_numeric($id_score))
		$id_score = array((int) $id_score);

	if (empty($id_score))
		return true;

	$request = $smcFunc['db_query']('', '
		SELECT id_score, id_member, position, score, personal_best
		FROM {db_prefix}arcade_scores
		WHERE id_score IN({array_int:score})
			AND id_game = {int:game}
		ORDER BY position',
		array(
			'game' => $game['id'],
			'score' => $id_score,
		)
	);

	$removeIds = array();
	$personalBest = array();
	$positions = array();
	$championUpdate = false;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['personal_best'])
			$personalBest[] = $row['id_member'];

		$removeIds[] = $row['id_score'];
		$positions[] = $row['position'];

		if ($row['position'] == 1)
			$championUpdate = true;
	}
	$smcFunc['db_free_result']($request);

	$personalBest = array_unique($personalBest);

	$count = -1;

	if (empty($positions))
		return true;

	$startPos = $positions[0];

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_scores
		WHERE id_score IN({array_int:scores})',
		array(
			'scores' => $removeIds,
		)
	);

	// Log removed scores
	logAction('arcade_remove_scores', array('game' => $game['id'], 'scores' => count($removeIds)));

	for ($i = 0; $i < count($positions); $i++)
	{
		if (isset($positions[$i + 1]) && $positions[$i] + 1 == $positions[$i + 1])
			$count--;
		else
		{
			updatePositions($game, $startPos, $count);

			$startPos = $positions[$i];
			$count = -1;
		}
	}

	if ($championUpdate)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_score, id_member
			FROM {db_prefix}arcade_scores
			WHERE position = 1
				AND id_game = {int:game}
			LIMIT 1',
			array(
				'game' => $game['id'],
			)
		);

		$row = $smcFunc['db_fetch_assoc']($request);

		if ($row !== false)
			updateGame($game['id'], array('champion' => $row['id_member'], 'champion_score' => $row['id_score']));
		else
			updateGame($game['id'], array('champion' => 0, 'champion_score' => 0));

		$smcFunc['db_free_result']($request);

		if (!empty($row['id_score']))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_scores
				SET champion_from = {int:time}
				WHERE id_score = {int:score}',
				array(
					'time' => time(),
					'score' => $row['id_score'],
				)
			);
		}
	}

	if (empty($personalBest))
		return true;

	$request = $smcFunc['db_query']('', '
		SELECT id_score, id_member
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}
			AND id_member IN({array_int:members})
		ORDER BY position',
		array(
			'game' => $game['id'],
			'members' => $personalBest,
		)
	);

	$newPersonalBest = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($newPersonalBest[$row['id_member']]))
			$newPersonalBest[$row['id_member']] = $row['id_score'];
	}
	$smcFunc['db_free_result']($request);

	if (empty($newPersonalBest))
		return true;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_scores
		SET personal_best = 1
		WHERE id_score IN({array_int:scores})',
		array(
			'scores' => $newPersonalBest,
		)
	);

	return true;
}

// Updates positions. (new score, remove)
function updatePositions(&$game, $start, $how)
{
	global $db_prefix, $smcFunc;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_scores
		SET position = position ' . $how . '
		WHERE id_game = {int:game}
			AND position >= {int:position}',
		array(
			'game' => $game['id'],
			'position' => $start,
		)
	);

	return $smcFunc['db_affected_rows']();
}

function loadMatch($match)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc, $user_info;

	$request = $smcFunc['db_query']('', '
		SELECT m.id_match, m.name, m.private_game, m.created, m.updated, m.status,
			m.num_players, m.current_players, m.num_rounds, m.current_round, m.id_member,
			IFNULL(me.id_member, 0) AS participation, me.status AS my_state
		FROM {db_prefix}arcade_matches AS m
			LEFT JOIN {db_prefix}arcade_matches_players AS me ON (me.id_match = m.id_match
				AND me.id_member = {int:current_member})
		WHERE m.id_match = {int:match}
			AND {query_see_match}',
		array(
			'match' => $match,
			'current_member' => $user_info['id'],
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!$row)
		fatal_lang_error('match_not_found', false);

	$status = array(
		0 => 'arcade_arena_player_invited',
		1 => 'arcade_arena_player_waiting',
		2 => 'arcade_arena_player_waiting',
		3 => 'arcade_arena_player_played',
		4 => 'arcade_arena_player_knockedout',
		10 => 'arcade_arena_waiting_players',
		11 => 'arcade_arena_waiting_other_players',
		20 => 'arcade_arena_started',
		21 => 'arcade_arena_not_played',
		22 => 'arcade_arena_not_played',
		23 => 'arcade_arena_not_other_played',
		24 => 'arcade_arena_dropped',
		30 => 'arcade_arena_complete',
		31 => 'arcade_arena_complete',
		32 => 'arcade_arena_complete',
		33 => 'arcade_arena_complete',
		34 => 'arcade_arena_complete',
	);

	$context['match'] = array(
		'id' => $row['id_match'],
		'name' => $row['name'],
		'private' => !empty($row['private_game']),
		'created' => timeformat($row['created']),
		'updated' => !empty($row['updated']) ? timeformat($row['updated']) : '',
		'players' => array(),
		'starter' => $row['id_member'],
		'num_players' => $row['current_players'],
		'players_limit' => $row['num_players'],
		'round' => $row['current_round'],
		'rounds' => array(),
		'num_rounds' => $row['num_rounds'],
		//'status' => $status[$row['status']],
		'status' => $status[$row['my_state'] + ($row['status'] * 10 + 10)],
	);

	$can_play = $row['participation'] && ($row['my_state'] == 1 || $row['my_state'] == 2) && $row['status'] == 1;
	$context['can_play_match'] = false;
	$context['can_leave'] = $row['participation'] && $row['status'] == 0 && $row['my_state'] != 0;
	$context['can_accept'] = $row['participation'] && $row['my_state'] == 0 && $row['status'] == 0;
	$context['can_decline'] = $row['participation'] && $row['my_state'] == 0 && $row['status'] == 0;
	$context['can_join_match'] = allowedTo('arcade_join_match') && $row['status'] == 0 && !$row['participation'] && $row['current_players'] < $row['num_players'];
	$context['can_edit_match'] = (allowedTo('arcade_admin') || $context['match']['starter'] == $user_info['id']) && $row['status'] != 2;
	$context['can_start_match'] = $context['can_edit_match'] && $row['status'] == 0 && $row['current_players'] >= 2;

	unset($row);

	// Load players
	$request = $smcFunc['db_query']('', '
		SELECT p.id_member, p.status, p.score, mem.real_name
		FROM {db_prefix}arcade_matches_players AS p
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.id_member)
		WHERE id_match = {int:match}
		ORDER BY score DESC',
		array(
			'match' => $context['match']['id']
		)
	);

	$rank = 1;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['match']['players'][$row['id_member']] = array(
			'id' => $row['id_member'],
			'rank' => $rank++,
			'name' => $row['real_name'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'score' => comma_format($row['score']),
			'status' => $txt[$status[$row['status']]],
			'can_accept' => $row['status'] == 0 && $row['id_member'] == $user_info['id'],
			'can_decline' => $row['status'] == 0 && $row['id_member'] == $user_info['id'],
			'can_kick' => $context['can_edit_match'] && $row['id_member'] != $user_info['id'],
			'accept_url' => $scripturl . '?action=arcade;sa=viewMatch;join;match=' . $context['match']['id'] . ';' . $context['session_var'] . '=' . $context['session_id'],
			'decline_url' => $scripturl . '?action=arcade;sa=viewMatch;leave;match=' . $context['match']['id'] . ';' . $context['session_var'] . '=' . $context['session_id'],
			'kick_url' => $scripturl . '?action=arcade;sa=viewMatch;kick;player=' . $row['id_member'] . ';match=' . $context['match']['id'] . ';' . $context['session_var'] . '=' . $context['session_id'],
		);

		$context['can_start_match'] &= $row['status'] != 0;
	}
	$smcFunc['db_free_result']($request);

	// Load rounds
	$request = $smcFunc['db_query']('', '
		SELECT r.round, r.id_game, r.status, game.game_name
		FROM {db_prefix}arcade_matches_rounds As r
			LEFT JOIN {db_prefix}arcade_games AS game ON (game.id_game = r.id_game)
		WHERE id_match = {int:match}
		ORDER BY r.round',
		array(
			'match' => $context['match']['id']
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['match']['rounds'][$row['round']] = array(
			'round' => $row['round'],
			'game' => $row['id_game'],
			'name' => $row['game_name'],
			'status' => $row['status'],
			'select' => !(empty($row['game_name']) || empty($row['id_game'])),
			'can_select' => (empty($row['game_name']) || empty($row['id_game'])) && $context['can_edit_match'],
			'can_play' => !empty($row['id_game']) && $row['round'] == $context['match']['round'] && $can_play,
			'play_url' => $scripturl . '?action=arcade;sa=play;match=' . $context['match']['id'],
		);

		if ($context['match']['rounds'][$row['round']]['can_play'])
			$context['can_play_match'] = true;
	}
	$smcFunc['db_free_result']($request);
}

// createMatch
function createMatch($matchOptions)
{
	global $smcFunc, $db_prefix, $sourcedir, $scripturl, $user_info, $txt;

	if (empty($matchOptions['created']))
		$matchOptions['created'] = time();

	if (!isset($matchOptions['private_game']))
		$matchOptions['private_game'] = 0;

	if (!isset($matchOptions['starter']))
		$matchOptions['starter'] = $user_info['id'];

	if (!empty($matchOptions['extra']))
		$matchOptions['extra'] = serialize($matchOptions['extra']);
	else
		$matchOptions['extra'] = '';

	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_matches',
		array(
			'name' => 'string',
			'id_member' => 'int',
			'private_game' => 'int',
			'status' => 'int',
			'created' => 'int',
			'updated' => 'int',
			'num_players' => 'int',
			'current_players' => 'int',
			'num_rounds' => 'int',
			'current_round' => 'int',
			'match_data' => 'string',
		),
		array(
			$matchOptions['name'],
			$matchOptions['starter'],
			!empty($matchOptions['private_game']) ? 1 : 0,
			0,
			$matchOptions['created'],
			0,
			$matchOptions['num_players'],
			0,
			$matchOptions['num_rounds'],
			0,
			$matchOptions['extra']
		),
		array()
	);

	$id_match = $smcFunc['db_insert_id']('{db_prefix}arcade_matches', 'id_match');

	$rows = array();
	for ($i = 0; $i < $matchOptions['num_rounds']; $i++)
	{
		$rows[] = array(
			$id_match,
			$i + 1,
			isset($matchOptions['games'][$i]) ? $matchOptions['games'][$i] : 0,
			0,
		);
	}

	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_matches_rounds',
		array(
			'id_match' => 'int',
			'round' => 'int',
			'id_game' => 'int',
			'status' => 'int',
		),
		$rows,
		array()
	);
	unset($rows);

	if (!empty($matchOptions['players']))
	{
		require_once($sourcedir . '/Subs-Post.php');

		$players = array();

		foreach ($matchOptions['players'] as $id)
			$players[$id] = $id == $matchOptions['starter'] ? 1 : 0;

		matchAddPlayers($id_match, $players);
	}

	return $id_match;
}

function matchAddPlayers($id_match, $players)
{
	global $smcFunc, $sourcedir, $scripturl;

	require_once($sourcedir . '/Subs-Post.php');

	$request = $smcFunc['db_query']('', '
		SELECT m.id_match, m.name, m.current_players, m.num_players
		FROM {db_prefix}arcade_matches AS m
		WHERE m.id_match = {int:match}',
		array(
			'match' => $id_match,
		)
	);
	$matchInfo = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!$matchInfo)
		return false;

	if ((count($players) + $matchInfo['current_players']) > $matchInfo['num_players'])
		return false;

	$request = $smcFunc['db_query']('', '
		SELECT id_member, real_name
		FROM {db_prefix}members
		WHERE id_member IN({array_int:members})',
		array(
			'members' => array_keys($players),
		)
	);

	$rows = array();
	$sendPms = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$rows[] = array(
			$id_match,
			$row['id_member'],
			$players[$row['id_member']],
			'',
		);

		if ($players[$row['id_member']] == 0)
			$sendPms[] = $row['id_member'];
	}
	$smcFunc['db_free_result']($request);

	if (!empty($sendPms))
		arcadeEvent('arena_invite',
			array(
				'match_name' => $matchInfo['name'],
				'match_id' => $id_match,
				'match_url' => $scripturl . '?action=arcade;match=' . $id_match,
				'players' => $sendPms,
			)
		);

	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_matches_players',
		array(
			'id_match' => 'int',
			'id_member' => 'int',
			'status' => 'int',
			'player_data' => 'string',
		),
		$rows,
		array()
	);

	unset($rows);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_matches
		SET current_players = {int:players}
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match,
			'players' => (count($players) + $matchInfo['current_players']),
		)
	);

	matchUpdateStatus($id_match);

	return true;
}

function matchUpdatePlayers($id_match, $players, $status = 1)
{
	global $smcFunc, $sourcedir;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_matches_players
		SET status = {int:status}
		WHERE id_match = {int:match}
			AND id_member IN({array_int:players})',
		array(
			'match' => $id_match,
			'players' => $players,
			'status' => $status,
		)
	);

	matchUpdateStatus($id_match);

	return true;
}

function matchUpdateStatus($id_match)
{
	global $smcFunc, $scripturl;

	$request = $smcFunc['db_query']('', '
		SELECT m.id_match, m.name, m.current_players, m.num_players, m.current_round, m.num_rounds, m.status, m.match_data
		FROM {db_prefix}arcade_matches AS m
		WHERE m.id_match = {int:match}',
		array(
			'match' => $id_match,
		)
	);
	$matchInfo = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($matchInfo['match_data']))
		$matchInfo['match_data'] = unserialize($matchInfo['match_data']);
	else
		$matchInfo['match_data'] = array();

	if ($matchInfo['status'] == 0)
	{
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}arcade_matches_players
			WHERE id_match = {int:match}
				AND status = 0',
			array(
				'match' => $id_match,
			)
		);

		list ($cn) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if ($cn > 0)
			return;

		if ($matchInfo['current_players'] == $matchInfo['num_players'])
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_matches
				SET status = 1, current_round = 1
				WHERE id_match = {int:match}',
				array(
					'match' => $id_match,
				)
			);

			// No one has played yet
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_matches_players
				SET status = 1
				WHERE id_match = {int:match}',
				array(
					'match' => $id_match,
				)
			);
		}
	}
	elseif ($matchInfo['status'] == 1)
	{
		if ($matchInfo['current_round'] == 0)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_matches
				SET current_round = 1
				WHERE id_match = {int:match}',
				array(
					'match' => $id_match,
				)
			);

			// No one has played yet
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_matches_players
				SET status = 1
				WHERE id_match = {int:match}',
				array(
					'match' => $id_match,
				)
			);
		}
		else
		{
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}arcade_matches_players
				WHERE id_match = {int:match}
					AND (status = 1 OR status = 2)',
				array(
					'match' => $id_match,
				)
			);

			list ($cn) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			// Has all played?
			if ($cn > 0)
				return;

			$request = $smcFunc['db_query']('', '
				SELECT id_game
				FROM {db_prefix}arcade_matches_rounds
				WHERE id_match = {int:match}
					AND round = {int:round}',
				array(
					'match' => $id_match,
					'round' => $matchInfo['current_round'],
				)
			);

			$round = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			$request = $smcFunc['db_query']('', '
				SELECT id_game, score_type, extra_data
				FROM {db_prefix}arcade_games
				WHERE id_game = {int:game}',
				array(
					'game' => $round['id_game'],
				)
			);

			$game = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			if ($game['score_type'] == 0)
				$order = 'DESC';
			elseif ($game['score_type'] == 1)
				$order = 'ASC';

			// Scores to give
			$scores = array(10, 8, 6, 5, 4, 3, 2, 1);

			$request = $smcFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}arcade_matches_results
				WHERE id_match = {int:match}
					AND round = {int:round}
				ORDER BY score ' . $order . '',
				array(
					'match' => $id_match,
					'round' => $matchInfo['current_round'],
				)
			);

			$current = 0;

			$players = array();

			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if (isset($scores[$current]))
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_matches_players
						SET score = score + {int:score}
						WHERE id_match = {int:match}
							AND id_member = {int:player}',
						array(
							'match' => $id_match,
							'player' => $row['id_member'],
							'score' => $scores[$current],
						)
					);
				}

				$players[] = $row['id_member'];

				$current++;
			}
			$smcFunc['db_free_result']($request);

			if ($matchInfo['match_data']['mode'] == 'knockout')
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_member
					FROM {db_prefix}arcade_matches_players
					WHERE id_match = {int:match}
					ORDER BY score
					LIMIT 1',
					array(
						'match' => $id_match,
					)
				);

				list ($knockout) = $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);

				$request = $smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_matches_players
					SET status = 4
					WHERE id_match = {int:match}
						AND id_member = {int:member}',
					array(
						'match' => $id_match,
						'member' => $knockout,
					)
				);
			}

			// Last round?
			if ($matchInfo['current_round'] == $matchInfo['num_rounds'])
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_matches
					SET status = 2
					WHERE id_match = {int:match}',
					array(
						'match' => $id_match,
					)
				);

				arcadeEvent('arena_match_end',
					array(
						'match_name' => $matchInfo['name'],
						'match_id' => $id_match,
						'match_url' => $scripturl . '?action=arcade;match=' . $id_match,
						'players' => $players,
					)
				);

				return;
			}
			// Advance to next round
			else
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_matches
					SET current_round = current_round + 1
					WHERE id_match = {int:match}',
					array(
						'match' => $id_match,
					)
				);

				// No one has played yet
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_matches_players
					SET status = 1
					WHERE id_match = {int:match}
						AND status = 3',
					array(
						'match' => $id_match,
					)
				);

				arcadeEvent('arena_new_round',
					array(
						'match_name' => $matchInfo['name'],
						'match_id' => $id_match,
						'match_url' => $scripturl . '?action=arcade;match=' . $id_match,
						'players' => $players,
					)
				);
			}
		}
	}

	return;
}

// matchRemovePlayers
function matchRemovePlayers($id_match, $players)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches_players
		WHERE id_match = {int:match}
			AND id_member IN({array_int:players})',
		array(
			'match' => $id_match,
			'players' => $players,
		)
	);

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_matches_players
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match,
		)
	);

	list ($cn) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_matches
		SET current_players = {int:players}
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match,
			'players' => $cn,
		)
	);

	matchUpdateStatus($id_match);

	return true;
}

// deleteMatch
function deleteMatch($id_match)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches_players
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches_results
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches_rounds
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match
		)
	);

	return true;
}
?>