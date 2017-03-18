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

function ArcadeChamps($count = 3, $type='wins')
{
	// Returns best players by count of champions
	global $db_prefix, $scripturl, $txt, $modSettings, $boardurl, $smcFunc, $context;

	if($type == 'wins')
	{
		$results = $smcFunc['db_query']('', '
			SELECT count(*) AS champions,
			IFNULL(mem.id_member, {int:zero}) AS id_member,
			IFNULL(mem.real_name, {string:empty}) AS real_name,
			IFNULL(mem.avatar, {string:empty}) AS avatar,
			IFNULL(attach.filename, {string:empty}) AS filename,
			IFNULL(attach.id_attach, {string:empty}) AS id_attach
			FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
			LEFT JOIN {db_prefix}attachments AS attach ON (attach.id_member = game.id_champion)
			WHERE id_champion_score > {int:zero} AND mem.id_member != 0
			GROUP BY game.id_champion
			ORDER BY champions DESC
			LIMIT '.$count,
			array(
				'empty' => '',
				'zero' => '0',
				'number' => $count,
			)
		);
	}
	else
	{
		$results = $smcFunc['db_query']('', '
			SELECT count(*) AS champions, game.id_cat,
			IFNULL(mem.id_member, {int:zero}) AS id_member,
			IFNULL(mem.real_name, {string:empty}) AS real_name,
			IFNULL(mem.avatar, {string:empty}) AS avatar,
			IFNULL(attach.filename, {string:empty}) AS filename,
			IFNULL(attach.id_attach, {string:empty}) AS id_attach
			FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
			LEFT JOIN {db_prefix}attachments AS attach ON (attach.id_member = game.id_champion)
			WHERE id_champion_score > {int:zero} AND mem.id_member != 0 AND game.id_cat = {int:cat}
			GROUP BY game.id_champion
			ORDER BY champions DESC
			LIMIT '.$count,
			array(
				'empty' => '',
				'zero' => '0',
				'number' => $count,
				'cat' => (int)$_REQUEST['category'],
			)
		);
	}

	$width = !empty($modSettings['skin_avatar_size_width']) && (int)$modSettings['skin_avatar_size_width'] > 0 ? (int)$modSettings['skin_avatar_size_width'] : 50;
	$height = !empty($modSettings['skin_avatar_size_height']) && (int)$modSettings['skin_avatar_size_height'] > 0 ? (int)$modSettings['skin_avatar_size_height'] : 50;
	while ($score = $smcFunc['db_fetch_assoc']($results))
	{
		unset($avatar);

		//linked avatar
		if (mb_substr($score['avatar'], 0, 7) == 'http://' || mb_substr($score['avatar'], 0, 8) == 'https://')
		{
			if ($wihi = ArcadeSizer($score['avatar'], $width, $height))
				$avatar = '<img src="' . $score['avatar'] . '" style="width: ' . $wihi[0] . 'px;height: ' . $wihi[1] . 'px;" alt="&nbsp;" />';
			else
				unset($avatar);
		}

		//resident avatar
		if($score['avatar'] && !isset($avatar))
		{
			if($wihi = ArcadeSizer($modSettings['avatar_url'].'/'.$score['avatar'], $width, $height))
				$avatar = '<img alt="&nbsp;" src="' . $modSettings['avatar_url'] . '/' . $score['avatar'] . '" style="width: ' . $wihi[0] . 'px;height: ' . $wihi[1] . 'px;" />';
			else
				unset($avatar);

		}

		//uploaded avatar custom
		if(isset($score['filename']) && !isset($avatar) && mb_substr($score['filename'],0, 7) == 'avatar_' )
		{
			if(isset($modSettings['custom_avatar_dir']) && file_exists($modSettings['custom_avatar_dir'].'/'.$score['filename']))
			{
				$wihi = ArcadeSizer($modSettings['custom_avatar_url'].'/'.$score['filename'], $width, $height);
				$avatar = '<img alt="&nbsp;" src="' . $modSettings['custom_avatar_url'] . '/' . $score['filename'] . '" style="border: 0px;width: ' . $wihi[0] . 'px;height: ' . $wihi[1] .'px;" />';
			}
		}

		//uploaded avatar attachment
		if(isset($score['filename']) && !isset($avatar) && mb_substr($score['filename'],0, 7) == 'avatar_')
			$avatar = '<img src="' . $scripturl.'?action=dlattach;attach=' . $score['id_attach'] . ';type=avatar" alt="&nbsp;" style="border: 0px;width: ' . $width . 'px;height: ' . $height . 'px;' . '" />';

		$champ_list[] = array(
			'id' => $score['id_member'],
			'name' => $score['real_name'],
			'link' => ($context['user']['is_logged'] && $score['id_member'])? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $score['real_name'],
			'champions' => isset($score['champions']) ? $score['champions'] : '',
			'score' => isset($score['value']) ? $score['value'] : '',
			'avatar' => isset($avatar) ? $avatar : '<img src="' . $modSettings['avatar_url'] . '/noavatar.gif" alt="" />',
		);
	}

	$smcFunc['db_free_result']($results);
	if(isset($champ_list))
		return $champ_list;
	else
		return false;
}

function ArcadeLatest($count=5,$curved=false)
{
	global $smcFunc, $db_prefix, $scripturl, $txt, $modSettings, $context;

	$code = '<div style="padding:10px;margin-left:2px;">';
	$results = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.thumbnail, game.game_directory, score.score, score.position, score.champion_from, score.duration,
		IFNULL(mem.id_member, {int:zero}) AS id_member, IFNULL(score.player_name, {string:empty}) AS real_name, score.end_time
		FROM ({db_prefix}arcade_scores AS score, {db_prefix}arcade_games AS game)
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
		WHERE game.id_game = score.id_game
		ORDER BY end_time DESC
		LIMIT {int:count}',
		array(
			'count' => $count,
			'zero' => '0',
			'empty' => ''
		)
	);

	$found = '';
	$found = $smcFunc['db_num_rows']($results);
	if(empty($found))
	{
		echo '<div class="centertext">', $txt['arcade_scores_none'] ,'</div>';
		return false;
	}
	else
	{
		while ($row = $smcFunc['db_fetch_assoc']($results))
		{
			//latest scores details
			$date = $row['end_time'];
			$playerid = $row['id_member'];
			$player = $row['real_name'];
			$game_id = $row['id_game'];
			strlen($row['game_name']) >= 23 ? $row['game_name'] = mb_substr($row['game_name'],0,22).'...': '';
			$game_name = $row['game_name'];
			$score = comma_format($row['score']);
			$game_pic = $modSettings['gamesUrl'] .'/'.$row['game_directory'].'/'.$row['thumbnail'];
			$time = date("m/d/Y", $row['end_time']);
			$div_con = addslashes(sprintf($txt['skin_when'], $time));
			$code .= '
		<div style="height:3px;float:left;position:absolute;">
			<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $game_id . '" title="' . $game_name . '"><img src="' . $game_pic . '" alt="' . $game_name . '"  style="border: 0px;width: 20px;height: 20px;" /></a>
		</div>
		<div style="height:7px;vertical-align:bottom;text-indent:28px;">';

			if($context['user']['is_logged'] && $playerid)
				$code .= '<a href="'.$scripturl.'?action=profile;u='.$playerid.'"><b>'.$player.'</b></a>';
			else
				$code .= $player;

			$code .= ' scored ' . $score.' ' . $txt['on'] . ' <a href="' . $scripturl . '?action=arcade;sa=play;game=' . $game_id . '"><b>' . $game_name . '</b></a>
			<!--[if IE]>
			<div style="float:right;font-size:0.8em;height:7px;margin-top: -15px;">' . $time . '</div>
			<![endif]-->
			<!--[if !IE]><!-->
			<div style="float:right;font-size:0.8em;height:7px;">' . $time . '</div>
			<!--<![endif]-->
		</div><br />';
		}

		$code .= '
		<div class="' . ($curved ? 'plainbox' : 'windowbg') . '" id="arcadebox" style="display: none; position: fixed; left: 0px; top: 0px; width: 33%;' . ($curved ? '' : 'padding:5px') . '">
			<div id="arcadebox_html" style=""></div>
		</div>
	</div>';

		return $code;
	}
}

function ArcadeNewChamps($count = 5)
{
	global $smcFunc, $db_prefix, $scripturl, $txt, $modSettings, $context;

	$code = '<div style="padding:10px;margin-left:2px;">';
	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.game_directory, game.thumbnail, score.score, score.position, score.end_time,
		IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, score.player_name) AS real_name
		FROM ({db_prefix}arcade_scores AS score, {db_prefix}arcade_games AS game)
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
		WHERE score.position = 1 AND game.id_game = score.id_game
		ORDER BY end_time DESC
		LIMIT {int:count}',
		array(
			'count' => $count,
			'empty' => ''
		)
	);

	$found = '';
	$found = $smcFunc['db_num_rows']($request);
	if(empty($found))
	{
		echo '<div class="centertext">', $txt['arcade_scores_none'], '</div>';
		return false;
	}
	else
	{
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			//newest champ details
			$playerid = $row['id_member'];
			$player = $row['real_name'];
			$game_id = $row['id_game'];
			strlen($row['game_name']) >= 23 ? $row['game_name'] = mb_substr($row['game_name'],0,22) . '...' : '';
			$game_name = $row['game_name'];
			$score = $row['score'];
			$time = date("m/d/Y", $row['end_time']);
			$game_pic = $modSettings['gamesUrl'] .'/'.$row['game_directory'].'/'.$row['thumbnail'];
			$code .= '
	<div style="height:3px;float:left;position:absolute;">
		<a href="' . $scripturl.'?action=arcade;sa=play;game=' . $game_id . '">
			<img src="' . $game_pic . '" alt="' . $game_name . '" title="' . $game_name . '" style="border: 0px;width: 20px;height: 20px;" />
		</a>
	</div>
	<div style="height:7px;vertical-align:bottom;text-indent:28px;">';

			if($context['user']['is_logged'] && $playerid)
				$code .= '
		<b><a href="' . $scripturl.'?action=profile;u=' . $playerid . '">' . $player . '</a></b>';
			else
				$code .= '
		<b>' . $player . '</b>';

			$code .= '
		&nbsp;' . $txt['skin_new_champ'] . '&nbsp;<a href="' . $scripturl.'?action=arcade;sa=play;game=' . $game_id.'"><b>' . $game_name . '</b></a>
		<!--[if IE]>
		<div style="float:right;font-size:0.8em;height:7px;margin-top: -15px;">' . $time . '</div>
		<![endif]-->
		<!--[if !IE]><!-->
		<div style="float:right;font-size:0.8em;height:7px;">' . $time . '</div>
		<!--<![endif]-->
	</div><br />';
		}

		return $code . '</div>';
	}
}

function ArcadeNewestGames($limit=5)
{
	global $db_prefix, $scripturl, $modSettings, $smcFunc, $txt;

	$newgam = '<div style="padding:10px 10px 10px 10px;text-align:left;margin-left:1px;">';
	$results = $smcFunc['db_query']('', '
		SELECT id_game, internal_name, game_name, game_directory, thumbnail, enabled
		FROM {db_prefix}arcade_games
		WHERE enabled=1
		ORDER BY id_game DESC
		LIMIT 0,{int:num}',
		array(
			'num' => $limit,
		)
	);

	$found = '';
	$found = $smcFunc['db_num_rows']($results);

	if(empty($found))
	{
		echo '<div class="smalltext centertext;" style="font-size:1.2em;">', $txt['arcade_no_games'], '</div>';
		return false;
	}
	else
	{
		while ($newest_game = $smcFunc['db_fetch_assoc']($results))
		{
			strlen($newest_game['game_name']) >= 23 ? $newest_game['game_name'] = mb_substr($newest_game['game_name'],0,22).'...': '';
			$newgam .= '
	<div style="padding-bottom: 0.4em;">
		<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $newest_game['id_game'] . '"><img src="' . $modSettings['gamesUrl'] . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail'] . '" style="width: 16px;height: 16px;vertical-align: bottom;" alt="Play ' . $newest_game['game_name'] . '" title="Play ' . $newest_game['game_name'] . '" />&nbsp;' . $newest_game['game_name'] . '</a>
		<br />
	</div>';
		}

		empty($newgam) ? $newgam = $txt['arcade_no_games'] : '';
		$smcFunc['db_free_result']($results);
		return $newgam . '</div>';
	}
}

function ArcadePopular($count = 5)
{
	// Returns most played games
	global $db_prefix, $scripturl, $context, $modSettings, $smcFunc, $txt;

	$results = $smcFunc['db_query']('', '
		SELECT id_game, game_name, game_directory, thumbnail, enabled, num_plays
		FROM {db_prefix}arcade_games
		WHERE num_plays != 0 AND enabled = 1
		ORDER BY num_plays DESC
		LIMIT {int:num}',
		array(
			'num' => $count,
		)
	);

	$pop = '<div style="padding:10px 10px 10px 10px;text-align:right;margin-right:1px;">';
	$found = '';
	$found = $smcFunc['db_num_rows']($results);

	if(empty($found))
	{
		echo '<div class="smalltext centertext">', $txt['arcade_none_played'] ,'</div>';
		return false;
	}
	else
	{
		while ($score = $smcFunc['db_fetch_assoc']($results))
		{
			strlen($score['game_name']) >= 23 ? $score['game_name'] = mb_substr($score['game_name'],0,22).'...': '';
			$pop .= '
	<div style="padding-bottom: 0.4em;"><a href="' . $scripturl. '?action=arcade;sa=play;game=' . $score['id_game'] . '">' . $score['game_name'] . '
		<img src="' . $modSettings['gamesUrl'] . '/' . $score['game_directory'] . '/' . $score['thumbnail'] . '" style="width: 16px;height: 16px;vertical-align: bottom;" alt="Play ' . $score['game_name'] . '" title="Play ' . $score['game_name'] . '" /></a><br />
	</div>';
		}

		$smcFunc['db_free_result']($results);
		empty($pop) ? $pop = $txt['arcade_popular_none'] : '';
		$pop .= '</div>';
		return $pop;

	}
}

function ArcadeRandomGames($limit=5)
{
	global $db_prefix, $scripturl, $modSettings, $smcFunc, $txt;
	$random = '';
	$results = $smcFunc['db_query']('', '
		SELECT id_game, game_directory, thumbnail, game_name, enabled, description
		FROM {db_prefix}arcade_games
		WHERE enabled = 1
		ORDER BY RAND()
		LIMIT {int:num}',
		array(
			'num' => $limit,
		)
	);

	while ($rg = $smcFunc['db_fetch_assoc']($results))
	{
		$random_name = '<br /><div style="text-align:center;"><a href="' . $scripturl . '?action=arcade;sa=play;game=' . $rg['id_game'] . '">' . $rg['game_name'] . '</a></div>';
		$random_description = false;
		$random .= '
	<div style="text-align:center;margin:10px;font-size:1.2em;" class="smalltext">
		<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $rg['id_game'] . '"><img style="height: 55px;width: 55px;" class="imgBorder" src="' . $modSettings['gamesUrl'] . '/' . $rg['game_directory'] . '/' . $rg['thumbnail'] . '" title="' . $rg['game_name'] . '" alt="' . $rg['game_name'] . '" /></a>' . $random_name . $random_description . '
	</div>';
	}

	empty($random) ? $random = '<div class="smalltext centertext">' . $txt['arcade_no_games'] . '</div>' : '';
	$smcFunc['db_free_result']($results);
	return $random;
}

function ArcadeDailyChallenge($game='')
{
	global $db_prefix, $scripturl, $context, $smcFunc, $txt;

	$context['CH_error'] = '';
	if($game)
	{
		$results = $smcFunc['db_query']('', '
			SELECT score_type
			FROM  {db_prefix}arcade_games
			WHERE id_game = {int:id}',
			array(
				'id' => $game['id'],
			)
		);

		$t = $smcFunc['db_fetch_row']($results);
		$t['0']== 1 ? $sort = 'ASC'  :  $sort = 'DESC' ;
		$results = $smcFunc['db_query']('', "
			SELECT
			a.id_game, a.score, a.position, a.end_time,
			IFNULL(mem.id_member, {int:zero}) AS id_member, IFNULL(mem.real_name, a.player_name) AS real_name
			FROM  {db_prefix}arcade_scores AS a
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = a.id_member)
			WHERE id_game = {int:id} AND FROM_UNIXTIME(end_time, '%y-%m-%d') = CURDATE()
			ORDER BY a.score {raw:sort}",
			array(
				'empty' => '',
				'zero' => '0',
				'sort' => $sort,
				'id' => $game['id'],
				'date' => date('ymd')
			)
		);

		$count = 0;
		$display = '';
		while ($time = $smcFunc['db_fetch_assoc']($results))
		{
			$count++;
			$display .= $count . '. ' . (isset($time['real_name']) ? $time['real_name'] : $txt['arcade_guest']) . ' - ' . $time['score'] . '<br />';
			if($count == 5)
				break;
		}

		if($count == 0)
		{
			$context['CH_error'] = 1;
			cache_put_data('game_of_day', null, 120);
		}

		$smcFunc['db_free_result']($results);
	}
	else
		$display = '
	<div class="centertext">' . $txt['arcade_no_games'] . '</div>';

	return $display;
}
?>