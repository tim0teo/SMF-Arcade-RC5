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
	void ArcadeOnline()
		- ...
*/

function ArcadeOnline()
{
	global $context, $scripturl, $user_info, $txt, $modSettings, $memberContext, $smcFunc;

	isAllowedTo('arcade_online');

	if (empty($modSettings['arcadeShowOnline']))
		fatal_lang_error('arcade_online_error', false);

	// Layout
	loadLanguage('Who');
	$context['sub_template'] = 'arcade_online';
	$context['members'] = array();
	$context['arcade_selected'] = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? ' selected="selected"' : ' selected';
	$action_array = array($txt['who_arcade'], $txt['who_arcade_play'], $txt['who_arcade_highscore'], $txt['who_arcade_match'], $txt['who_arcade_online'], $txt['who_arcade_view_match'], $txt['who_arcade_new_match']);

	// Sort out... the column sorting.
	$sort_methods = array(
		'user' => 'name',
		'time' => 'time',
	);

	$show_methods = array(
		'members' => '(lo.id_member != 0)',
		'guests' => '(lo.id_member = 0)',
		'all' => '1=1',
	);

	// Store the sort methods and the show types for use in the template.
	$context['sort_methods'] = array(
		'user' => $txt['who_user'],
		'time' => $txt['who_time'],
	);
	$context['show_methods'] = array(
		'all' => $txt['who_show_all'],
		'members' => $txt['who_show_members_only'],
		'guests' => $txt['who_show_guests_only'],
	);

	// Does the user prefer a different sort direction?
	if (isset($_REQUEST['sort']) && isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = $_SESSION['who_online_sort_by'] = $_REQUEST['sort'];
		$sort_method = $sort_methods[$_REQUEST['sort']];
	}
	// Did we set a preferred sort order earlier in the session?
	elseif (isset($_SESSION['who_online_sort_by']))
	{
		$context['sort_by'] = $_SESSION['who_online_sort_by'];
		$sort_method = $sort_methods[$_SESSION['who_online_sort_by']];
	}
	// Default to last time online.
	else
	{
		$context['sort_by'] = $_SESSION['who_online_sort_by'] = 'time';
		$sort_method = 'time';
	}

	$context['sort_direction'] = isset($_REQUEST['asc']) || (isset($_REQUEST['sort_dir']) && $_REQUEST['sort_dir'] == 'asc') ? 'up' : 'down';

	$conditions = array();
	if (!allowedTo('moderate_forum'))
		$conditions[] = '(IFNULL(lo.show_online, 1) = 1)';

	// Fallback to top filter?
	if (isset($_REQUEST['submit_top']) && isset($_REQUEST['show_top']))
		$_REQUEST['show'] = $_REQUEST['show_top'];
	// Does the user wish to apply a filter?
	if (isset($_REQUEST['show']) && isset($show_methods[$_REQUEST['show']]))
	{
		$context['show_by'] = $_SESSION['who_online_filter'] = $_REQUEST['show'];
		$conditions[] = $show_methods[$_REQUEST['show']];
	}
	// Perhaps we saved a filter earlier in the session?
	elseif (isset($_SESSION['who_online_filter']))
	{
		$context['show_by'] = $_SESSION['who_online_filter'];
		$conditions[] = $show_methods[$_SESSION['who_online_filter']];
	}
	else
		$context['show_by'] = $_SESSION['who_online_filter'] = 'all';

	// join or separate members & guests
	if (isset($_REQUEST['join']))
		$context['arcade_join'] = $_SESSION['arcade_do_join'] = $_REQUEST['join'];
	else
		$context['arcade_join'] = !empty($_SESSION['arcade_do_join']) ? $_SESSION['arcade_do_join'] : 'join';

	// Get the total amount of members in the arcade
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_member_data AS lo' . (!empty($conditions) ? '
		WHERE ' . implode(' AND ', $conditions) : ''),
		array(
		)
	);
	list ($totalMembers) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Get the total amount of guests in the arcade
	if ((!empty($_SESSION['who_online_filter'])) && $_SESSION['who_online_filter'] !== 'members')
	{
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}arcade_guest_data',
			array(
			)
		);
		list ($totalGuests) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$totalMembers = $totalMembers + $totalGuests;
	}

	// Prepare some page index variables.
	$context['page_index'] = constructPageIndex($scripturl . '?action=who;sort=' . $context['sort_by'] . ($context['sort_direction'] == 'up' ? ';asc' : '') . ';show=' . $context['show_by'], $_REQUEST['start'], $totalMembers, $modSettings['defaultMaxMembers']);
	$context['start'] = $_REQUEST['start'];

	// Look for users in the arcade
	$request = $smcFunc['db_query']('', '
		SELECT
			lo.online_time, lo.id_member, lo.online_ip, lo.online_name, lo.current_action,
			lo.current_game, lo.online_color, IFNULL(lo.show_online, 1) AS show_online
		FROM {db_prefix}arcade_member_data AS lo' . (!empty($conditions) ? '
		WHERE ' . implode(' AND ', $conditions) : '') . '
		ORDER BY online_time ASC',
		array(
			'regular_member' => 0,
			'sort_method' => $sort_method,
		)
	);
	list($context['members'], $context['arcade_members'], $context['arcade_guests']) = array(array(), array(), array());
	$member_ids = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Send the information to the template.
		$action = (empty($row['current_action'])) || $row['current_action'] > 4 ? '0' : abs($row['current_action']);
		$game = empty($row['current_game']) ? 0 : $row['current_game'];
		$gamename = arcade_game_name($game);
		$gamelink = !empty($gamename['enabled']) ? sprintf($action_array[$action], $game, $gamename['game_name']) : $gamename['game_name'];
		$currentAction = $action > 0 && $action < 3 && !empty($game) ? $gamelink : $action_array[$action];
		$context['arcade_members'][] = array(
			'id' => $row['id_member'],
			'ip' => allowedTo('moderate_forum') ? $row['online_ip'] : '',
			'realtime' => $row['online_time'],
			'time' => strtr(timeformat($row['online_time']), array($txt['today'] => '', $txt['yesterday'] => '')),
			'timestamp' => forum_time(true, $row['online_time']),
			'query' => empty($row['current_action']) ? 'index' : $row['current_action'],
			'is_hidden' => $row['show_online'] == 0,
			'color' => empty($row['online_color']) ? '' : $row['online_color'],
			'action' => $currentAction,
			'game' => $game,
			'name' => $row['online_name'],
			'user' => $row['online_name'],
			'is_guest' => false,
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
		);

		$member_ids[] = $row['id_member'];
	}
	$smcFunc['db_free_result']($request);

	// Look for guests in the arcade
	if ((!empty($_SESSION['who_online_filter'])) && $_SESSION['who_online_filter'] !== 'members')
	{
		$request = $smcFunc['db_query']('', '
			SELECT
				lo.online_time, lo.online_ip, lo.current_action,
				lo.current_game, show_online
			FROM {db_prefix}arcade_guest_data AS lo
			ORDER BY online_time ASC',
			array(
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			// Send the information to the template.
			$action = (empty($row['current_action'])) || $row['current_action'] > 4 ? '0' : abs($row['current_action']);
			$game = empty($row['current_game']) ? 0 : $row['current_game'];
			$gamename = arcade_game_name($game);
			$gamelink = !empty($gamename['enabled']) ? sprintf($action_array[$action], $game, $gamename['game_name']) : $gamename['game_name'];
			$currentAction = $action > 0 && $action < 3 && !empty($game) ? $gamelink : $action_array[$action];
			$context['arcade_guests'][] = array(
				'id' => 0,
				'ip' => allowedTo('moderate_forum') ? $row['online_ip'] : '',
				'realtime' => $row['online_time'],
				'time' => strtr(timeformat($row['online_time']), array($txt['today'] => '', $txt['yesterday'] => '')),
				'timestamp' => forum_time(true, $row['online_time']),
				'query' => empty($row['current_action']) ? 'index' : $row['current_action'],
				'is_hidden' => $row['show_online'] == 0,
				'color' => '',
				'action' => $currentAction,
				'game' => $game,
				'name' => $txt['guest_title'],
				'user' => $txt['guest_title'],
				'is_guest' => true,
				'href' => '',
			);

			$member_ids[] = '0';
		}
		$smcFunc['db_free_result']($request);
	}

	$sort = $context['sort_direction'] == 'up' ? 'SORT_ASC' : 'SORT_DESC';
	if ((!empty($context['arcade_join'])) && $context['arcade_join'] == 'disjoin')
	{
		arcade_array_sort_by_columns($context['arcade_members'], $sort_method, $sort);
		arcade_array_sort_by_columns($context['arcade_guests'], 'realtime', $sort);
		$context['members_all'] = array_merge_recursive($context['arcade_members'], $context['arcade_guests']);
	}
	else
	{
		arcade_array_sort_by_columns($context['arcade_members'], $sort_method, $sort);
		arcade_array_sort_by_columns($context['arcade_guests'], 'realtime', $sort);

		if ($sort == 'SORT_ASC')
			$context['members_all'] = array_merge_recursive($context['arcade_members'], $context['arcade_guests']);
		else
			$context['members_all'] = array_merge_recursive($context['arcade_guests'], $context['arcade_members']);

		//arcade_array_sort_by_columns($context['members_all'], $sort_method, $sort);
	}
	$context['members'] = array_slice($context['members_all'], $context['start'], $modSettings['defaultMaxMembers'], true);

	// Load up the guest user.
	$memberContext[0] = array(
		'id' => 0,
		'name' => $txt['guest_title'],
		'user' => 0,
		'group' => $txt['guest_title'],
		'href' => '',
		'link' => $txt['guest_title'],
		'email' => $txt['guest_title'],
		'is_guest' => true
	);

	$context['page_title'] = $txt['arcade_online_title'];
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=arcade;sa=online',
		'name' => $txt['arcade_online'],
	);

	// Put it in the context variables.
	foreach ($context['members'] as $i => $member)
	{
		// Keep the IP that came from the database.
		$memberContext[$member['id']]['ip'] = $member['ip'];
		$context['members'][$i]['action'] = isset($context['members'][$i]['is_hidden']) ? $context['members'][$i]['action'] : $txt['who_hidden'];
		$context['members'][$i] += $memberContext[$member['id']];
	}

	// Some people can't send personal messages...
	$context['can_send_pm'] = allowedTo('pm_send');

	// any profile fields disabled?
	$context['disabled_fields'] = isset($modSettings['disabled_profile_fields']) ? array_flip(explode(',', $modSettings['disabled_profile_fields'])) : array();

	loadTemplate('ArcadeOnline');
}

function arcade_array_sort_by_columns(&$array, $sort, $dir = 'SORT_ASC')
{
	foreach ($array as $key => $row)
		$name[$key] = $row[$sort];

	if ((!empty($name)) && is_array($name))
	{
		if ($dir == 'SORT_ASC')
			array_multisort($name, SORT_ASC, $array);
		else
			array_multisort($name, SORT_DESC, $array);
	}
}

function arcade_game_name($id_game)
{
	global $smcFunc;
	$game = array('game_name' => '???', 'enabled' => 0);

	// Look for game name
	$request = $smcFunc['db_query']('', '
		SELECT id_game, game_name, enabled
		FROM {db_prefix}arcade_games
		WHERE id_game = {int:game}
		LIMIT 1',
		array(
			'game' => $id_game,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$game = array(
			'game_name' => !empty($row['game_name']) ? $row['game_name'] : '???',
			'enabled' => !empty($row['enabled']) ? 1 : 0,
		);
	}
	$smcFunc['db_free_result']($request);

	return $game;
}
?>