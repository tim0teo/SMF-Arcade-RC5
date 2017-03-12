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

// Defiant sub-routines
function Arcade3champsBlock($no)
{
	global $scripturl, $txt, $settings;

	$top_player = ArcadeLatestChamps($no);
	$content = '<div class="centertext"><table style="width: 100%;border: 0px;border-collapse: collapse;"><tr><td style="padding: 1px;" colspan="2"><div class="centertext"><i><b>' . $no . '&nbsp;' . $txt['arcade_g_i_b_8'] . '</b></i></div></td></tr>';
	if ($top_player != false)
	{
		foreach ($top_player as $row)
		{
			$content.= '<tr><td style="height: 25px;padding: 1px;"><div style="text-align: right;"><img src="' . $settings['images_url'] . '/arc_icons/cup_g.gif" alt="ico"/></div></td><td style="padding: 1px;"><div class="middletext"><div style="text-align: left;">&nbsp;-&nbsp;' . $row['member_link'] . '&nbsp;' . $txt['is_champ_of'] . '&nbsp;' . $row['game_link'] . '</div></div></td></tr>';
		}
	}
	$content.='</table></div>';

	return $content;
}

function ArcadeRandomGameBlock()
{
	global $context, $txt, $modSettings, $settings;

	$gamex = small_game_query('ORDER BY RAND() LIMIT 0,1');
	foreach($gamex as $game)
	{
		$ratecode = '';
		$rating = $game['rating'];

		if ($rating > 0)
		{
			$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $rating);
			$ratecode .= str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" />' , 5 - $rating);
		}

		$content = '
			<div class="centertext">
				<table style="width: 100%;border: 0px;border-collapse: collapse;">
					<tr>
						<td style="padding: 1px;" colspan="2">
							<div class="centertext"><i><b>' . $txt['arcade_random_game'] . '</b></i></div>
						</td>
					</tr>
					<tr>
						<td class="centertext" style="padding: 1px;">
							<div><span></span></div>
							<a href="' . $game['url']['play'] . '">
								<img style="width: 80px;height: 80px;" src="' . $game['thumbnail'] . '" alt="ico" title="' . $txt['arcade_play'] . '&nbsp;' . $game['name'] . '"/>
							</a>
							<div><span></span></div>
							<div class="middletext">
								<a href="' . $game['url']['play'] . '">' . $game['name'] . '</a>
							</div>
						</td>
					</tr>';


		if ($rating > 0)
			$content .= '
					<tr>
						<td class="centertext" style="padding: 1px;">'.$ratecode.'</td>
					</tr>';

		$content .= '
					<tr>
						<td class="centertext" style="padding: 1px;">
							<div class="middletext">';

		if ($game['isChampion'])
			$content .= '
								<strong>' . $txt['arcade_champion'] . ':</strong>&nbsp;'.$game['champion']['memberLink'].'&nbsp;-&nbsp;' . $game['champion']['score'];

		else
			$content .=$txt['arcade_no_scores'];

		$content .='
							</div>
						</td>
					</tr>
				</table>
			</div>';

		return $content;
	}
}


function ArcadeInfoNewestGames($no)
{
	global $smcFunc, $scripturl, $txt, $modSettings, $boardurl;

		$result = $smcFunc['db_query']('', '
		SELECT id_game, game_name, thumbnail, game_directory
		FROM {db_prefix}arcade_games
		WHERE enabled = 1
		ORDER BY id_game DESC
		LIMIT 0, {int:limit}',
		array(
		'limit' => $no,
		)
	);
	$content = '<div class="centertext"><table style="width: 100%;border: 0px;border-collapse: collapse;"><tr><td style="padding: 3px;" colspan="2"><div class="centertext"><i><b>' . $no . '&nbsp;'.$txt['arcade_LatestGames'] . '</b></i></div></td></tr>';
	while ($popgame = $smcFunc['db_fetch_assoc']($result))
	{
		$gamesUrl = $boardurl . '/' . basename($modSettings['gamesUrl']);
		$popgameico = empty($popgame['game_directory']) ?	$gamesUrl . '/' . $popgame['thumbnail'] : $gamesUrl . '/' . $popgame['game_directory'] . '/' . $popgame['thumbnail'];
		$content .='<tr><td style="padding: 3px;"><div style="text-align: right;"><a href="' . $scripturl . '?action=arcade;sa=play;game=' . $popgame['id_game'] . '"><img style="border: 0px;width: 25px;height: 25px;" src="' . $popgameico. '" alt="ico" title="'.$txt['arcade_champions_play'].' '. $popgame['game_name'].'"/></a></div></td><td style="padding: 3px;" class="middletext"><div class="centertext"><a href="' . $scripturl . '?action=arcade;sa=play;game=' . $popgame['id_game'] . '">' . $popgame['game_name'] . '</a></div></td></tr>';
	}

	$content .='</table></div>' ;

	return $content;
}

function ArcadeInfoLongestChamps($no)
{
	global $scripturl, $txt, $modSettings, $boardurl;

	$mostgame = ArcadeStats_LongestChampions($no);

	$content = '<div class="centertext"><table style="width: 100%;border: 0px;border-collapse: collapse;"><tr><td style="padding: 3px;" colspan="3"><div class="centertext"><i><b>' . $no . '&nbsp;' . $txt['arcade_g_i_b_11'] . '</b></i></div></td></tr>';
	foreach($mostgame as $popgame)
	{
		$gamesUrl = $boardurl . '/' . basename($modSettings['gamesUrl']);
		$popgameico = empty($popgame['game_directory']) ?	$gamesUrl . '/' . $popgame['thumbnail'] : $gamesUrl . '/' . $popgame['game_directory'] . '/' . $popgame['thumbnail'];
		$content .=	'<tr><td style="padding: 3px;width: 25px;"><a href="' . $scripturl.'?action=arcade;sa=play;game=' . $popgame['id'] . '"><img style="border: 0px;width: 25px;height: 25px;" src="' . $popgameico . '" alt="ico" title="'.$txt['arcade_champions_play'].' '. $popgame['game_name'].'"/></a></td><td style="padding: 3px;" class="middletext"><div style="text-align: left;">' . $popgame['member_link'] . '&nbsp;' . $txt['arcade_g_i_b_9'] . '&nbsp;' . $popgame['game_name'] . '&nbsp;' . $txt['arcade_g_i_b_5'] . '&nbsp;' . $popgame['duration'] . '</div></td></tr>';
	}

	$content .='</table></div>' ;

	return $content;
}

function ArcadeInfoMostPlayed($no)
{
	global $scripturl, $txt, $modSettings, $boardurl;

	$mostgame = ArcadeStats_MostPlayed($no);

	$content = '<div class="centertext"><table style="width: 100%;border: 0px;border-collapse: collapse;"><tr><td style="padding: 3px;" colspan="3"><div class="centertext"><i><b>'.$no.' '.$txt['arcade_g_i_b_10'].'</b></i></div></td></tr>';
	foreach($mostgame as $popgame)
	{
		$gamesUrl = $boardurl . '/' . basename($modSettings['gamesUrl']);
		$popgameico = empty($popgame['game_directory']) ?	$gamesUrl . '/' . $popgame['thumbnail'] : $gamesUrl . '/' . $popgame['game_directory'] . '/' . $popgame['thumbnail'];
		$content .='<tr><td style="width: 25px;padding: 3px;"><a href="' . $scripturl . '?action=arcade;sa=play;game=' . $popgame['id'] . '"><img style="border: 0px;width: 25px;height: 25px;" src="' . $popgameico . '" alt="ico" title="' . $txt['arcade_champions_play'] . '&nbsp;' . $popgame['name'] . '"/></a></td><td style="padding: 3px;" class="middletext"><div style="text-align: left;">' . $popgame['link'] . '&nbsp;' . $txt['arcade_g_i_b_6'] . '&nbsp;' . $popgame['plays'] . '&nbsp;' . $txt['arcade_g_i_b_7'] . '</div></td></tr>';
	}

	$content .='</table></div>' ;

	return $content;
}

function ArcadeInfoBestPlayers($no)
{
	global $scripturl, $txt, $settings;

	$top_player = ArcadeStats_BestPlayers($no);
	$i=0; //players position

	//array for icons
	$poz = array('/first.gif','/second.gif','/third.gif',);

	$content = '<div class="centertext"><table style="width: 100%;border: 0px;border-collapse: collapse;"><tr><td style="padding: 1px;" colspan="2"><div class="centertext"><i><b>' . $no . '&nbsp;' . $txt['arcade_b3pb_1'] . '</b></i></div></td></tr>';

	if ($top_player != false)
	{
		foreach ($top_player as $row)
		{
			$content.= '<tr><td style="height: 25px;padding: 3px;"><div style="text-align: right;"><img src="' . $settings['default_images_url'] . '/arc_icons' . $poz[$i] . '" alt="ico"/></div></td><td style="padding: 1px;"><div class="middletext"><div style="text-align: left;">&nbsp;-&nbsp;' . $row['link'] . '&nbsp;' . $txt['arcade_b3pb_2'] . '&nbsp;' . $row['champions'] . '&nbsp;' . $txt['arcade_b3pb_3'] . '</div></div></td></tr>';
			$i++;
			if ($i > 2)
			{
				$poz[$i]= '/star2.gif';
			}
		}
	}
	$content.='</table></div>';

	return $content;

}

function ArcadeShout()
{
	global $smcFunc, $txt, $modSettings, $user_info;

	if (isset($_REQUEST['del']))
	{
	   // Only allow admins to delete shouts

       if (allowedTo('arcade_admin'))
       {
    		$id = (int)$_REQUEST['del'];

    		$smcFunc['db_query']('', '
    			DELETE FROM {db_prefix}arcade_newshouts
    			WHERE id_shout = {int:ids}',
    			array(
    				'ids' => $id,
    			)
    		);

			// force a reload
    		cache_put_data('arcade_shouts', null, 86400);
      }
	}
	elseif (!$user_info['is_guest'])
	{
		$_REQUEST['the_shout'] = isset($_REQUEST['the_shout']) ? $_REQUEST['the_shout'] : '';
		$shout = strlen($_REQUEST['the_shout']) > 100 ? mb_substr($_REQUEST['the_shout'], 0, 100) . '...' : $_REQUEST['the_shout'];
		$shout = $txt['arcade_shouted'] . $smcFunc['htmlspecialchars']($shout, ENT_QUOTES);
		add_to_arcade_shoutbox($shout);
	}

	redirectexit('action=arcade');
}

function ArcadeLatestChamps($no)
{
	global $smcFunc, $scripturl, $txt;

	$result = $smcFunc['db_query']('', '
		SELECT g.id_game, g.game_name, g.thumbnail, g.game_directory, m.id_member, m.real_name, s.id_member
		FROM {db_prefix}arcade_games AS g
		LEFT JOIN {db_prefix}arcade_scores AS s ON ( g.id_champion_score = s.id_score )
		LEFT JOIN {db_prefix}members AS m ON ( m.id_member = s.id_member )
		WHERE g.id_champion_score > 0
		ORDER BY s.champion_from DESC
		LIMIT 0, {int:limit}',
		array(
		'limit' => $no,
		)
	);

	$top = array();
	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		$top[] = array(
			'id' => $score['id_game'],
			'game_name' => $score['game_name'],
			'thumbnail' => $score['thumbnail'],
			'game_directory' => $score['game_directory'],
			'game_link' => '<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $score['id_game'] . '">' .  $score['game_name'] . '</a>',
			'real_name' => $score['real_name'],
			'member_link' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $txt['arcade_guest'],
		);
	}
		return $top;
}

function category_games()
{
	global $smcFunc;
	list($no, $cats) = array(30, array());

 	$result = $smcFunc['db_query']('', '
		SELECT c.id_cat, count(g.id_cat) AS games, c.cat_name, c.cat_icon
		FROM {db_prefix}arcade_games g, {db_prefix}arcade_categories c
		WHERE g.id_cat = c.id_cat AND g.enabled = 1
 		GROUP BY g.id_cat
 		ORDER BY c.cat_order
 		LIMIT 0, {int:limit}',
		array(
		'limit' => $no,
		)
	);
	while ($cat = $smcFunc['db_fetch_assoc']($result))
	{
		if (empty($cat['cat_icon']) && !empty($cat['cat_name']))
			$cat['cat_icon'] = ArcadeSpecialChars(trim($cat['cat_name'])) . '.gif';

		$cats[$cat['id_cat']] = $cat;
	}
	return $cats;
}

// functions rewrtitten after this line
function ArcadeInfoFader()
{
	global $modSettings;
	list($content, $cacheData, $i) = array('', array('news' => ''), 0);

	if (!empty($modSettings['enable_arcade_cache']))
	{
		if (($cacheData = cache_get_data('arcade_newsFader', 300)) == null)
		{
			$a_news = arcade_news_fader($modSettings['arcadeNewsFader'], $modSettings['arcadeNewsNumber']);
			foreach($a_news as $news_out)
			{
				$content .= '<div id="arcadeNews' . $i . '">' . addslashes($news_out['body']) . '</div>';
				$i++;
			}
			$cacheData['news'] = $content;
			cache_put_data('arcade_newsFader', $cacheData, 300);

		}
	}
	else
	{
		$a_news = arcade_news_fader($modSettings['arcadeNewsFader'], $modSettings['arcadeNewsNumber']);
		foreach($a_news as $news_out)
		{
			$content .= '<div id="arcadeNews' . $i . '">' . addslashes($news_out['body']) . '</div>';
			$i++;
		}

		$cacheData['news'] = !empty($content) ? $content : '';
	}

	return !empty($cacheData['news']) ? $cacheData['news'] : '';
}

function ArcadeInfoPanelBlock()
{
	global $context, $txt, $modSettings, $scripturl;
	list($no, $content, $gotd, $random, $cache3Best['info'], $cacheGotd['info']) = array(5, '', '', '', '', '');

	if (!empty($modSettings['enable_arcade_cache']))
	{
		if (($cache3Best = cache_get_data('arcade_infopanel', 86400)) == null)
		{
			$content .= 'pausecontent[0] = document.getElementById("pausecontent0").innerHTML;';
			$content .= 'pausecontent[1] = document.getElementById("pausecontent1").innerHTML;';
			$content .= 'pausecontent[2] = document.getElementById("pausecontent2").innerHTML;';
			$content .= 'pausecontent[3] = document.getElementById("pausecontent3").innerHTML;';
			$content .= 'pausecontent[4] = document.getElementById("pausecontent4").innerHTML;';

			$cache3Best = array('info' => $content);
			cache_put_data('arcade_infopanel', $cache3Best, 86400);
		}

		if (($cacheGotd = cache_get_data('arcade_gotd', 86400)) == null)
		{
			$gotd .= 'pausecontent[5] = document.getElementById("pausecontent5").innerHTML;';

			$cacheGotd = array('info' => $gotd);
			cache_put_data('arcade_gotd', $cacheGotd, 86400);
		}
	}
	else
	{
		$content .= 'pausecontent[0] = document.getElementById("pausecontent0").innerHTML;';
		$content .= 'pausecontent[1] = document.getElementById("pausecontent1").innerHTML;';
		$content .= 'pausecontent[2] = document.getElementById("pausecontent2").innerHTML;';
		$content .= 'pausecontent[3] = document.getElementById("pausecontent3").innerHTML;';
		$content .= 'pausecontent[4] = document.getElementById("pausecontent4").innerHTML;';
		$content .= 'pausecontent[5] = document.getElementById("pausecontent5").innerHTML;';
		$cache3Best['info'] = $content;
	}

	$random = 'pausecontent[6] = document.getElementById("pausecontent6").innerHTML;';

	return $cache3Best['info'] . $cacheGotd['info'] . $random;
}

function ArcadeGOTDBlock()
{
	global $context, $txt, $modSettings, $settings;

	list($ratecode, $game) = array('', getGameOfDay());
	$content = '<div class="centertext"><table style="width: 100%;border: 0px;"><tr><td style="padding: 1px;"><div class="centertext"><i><b>' . $txt['arcade_game_of_day'] . '</b></i></div></td></tr>';
	$rating = !empty($game['rating']) ? (int)$game['rating'] : 0;

	if ($rating > 0)
	{
		$ratecode = str_repeat('<img src="' . $settings['default_images_url'] . '/arc_icons/star.gif" alt="s" />' , $rating);
		$ratecode .= str_repeat('<img src="' . $settings['default_images_url'] . '/arc_icons/star2.gif" alt="s" />' , 5 - $rating);
	}

	$content .='<tr><td style="padding: 1px;"><div class="centertext">';

	if (!empty($game['thumbnail']))
		$content .= '<div><span></span></div><a href="' . $game['url']['play'] . '"><img style="width: 80px;height: 80px;" src="' . $game['thumbnail'] . '" alt="ico" title="'.$txt['arcade_play'].' '.$game['name'].'"/></a><div><span></span></div><div><span></span></div>';

	$content .= '<div class="middletext"><a href="'. $game['url']['play']. '">'. $game['name']. '</a></div></div></td></tr>';

	if ($rating > 0)
		$content .='<tr><td class="centertext">' . $ratecode . '</td></tr>';

	$content .='<tr><td class="centertext"><div class="middletext">';

	if (!empty($game['is_champion']))
		$content .= '<strong>' . $txt['arcade_champion'] . ':</strong> ' . $game['champion']['link']. '&nbsp;-&nbsp;' . $game['champion']['score'] . '</div>';
	else
		$content .= $txt['arcade_no_scores'];

	$content .= '</div></td></tr></table></div>';
	return $content;
}

function arcade_news_fader($board, $limit)
{
	global $smcFunc;

	$result = $smcFunc['db_query']('', '
		SELECT id_first_msg
		FROM {db_prefix}topics
		WHERE id_board = {int:board}
 		ORDER BY id_first_msg DESC
 		LIMIT 0, {int:limit}',
		array(
		'limit' => $limit,
		'board' => $board,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$posts[] = $row['id_first_msg'];
	}

	if (empty($posts))
		return array();

	$result = $smcFunc['db_query']('', '
		SELECT m.body, m.smileys_enabled, m.id_msg
		FROM {db_prefix}topics AS t, {db_prefix}messages AS m
		WHERE t.id_first_msg IN (' . implode(', ', $posts) . ')
 		AND m.id_msg = t.id_first_msg
		ORDER BY t.id_first_msg DESC
 		LIMIT 0, {int:limit}',
		array(
		'limit' => count($posts),
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{

		$find  = '<br';
		$pos = strpos($row['body'], $find);

		if ($pos !== false)
			$row['body'] = substr($row['body'], 0, $pos);

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);
		censorText($row['body']);
		$return[] = array(
			'body' => $row['body'],
			'is_last' => false
		);
	}

	$return[count($return) - 1]['is_last'] = true;

	return $return;
}

function ArcadeInfoShouts()
{
    global $smcFunc, $scripturl, $settings, $txt, $sourcedir, $modSettings;
	require_once($sourcedir . '/Subs.php');
	list($content, $shouts) = array('', array());

	if (!empty($modSettings['enable_arcade_cache']))
	{
		if ($shouts = cache_get_data('arcade_shouts', 86400) == null)
		{
			$result = $smcFunc['db_query']('', '
				SELECT s.id_shout, s.id_member, s.content, s.time, m.real_name
				FROM {db_prefix}arcade_newshouts AS s
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = s.id_member)
				ORDER BY id_shout DESC
				LIMIT 0, {int:limit}',
				array(
					'limit' => !empty($modSettings['arcade_show_shouts']) ? $modSettings['arcade_show_shouts'] : 25,
				)
			);

			while ($shout = $smcFunc['db_fetch_assoc']($result))
			{
				$shouts[] = array(
					'id_shout' => $shout['id_shout'],
					'id_member' => $shout['id_member'],
					'content' => $shout['content'],
					'time' => $shout['time'],
					'real_name' => $shout['real_name'],
				);
			}
			$smcFunc['db_free_result']($result);
			cache_put_data('arcade_shouts', $shouts, 86400);
		}
	}
	else
	{
		$result = $smcFunc['db_query']('', '
			SELECT s.id_shout, s.id_member, s.content, s.time, m.real_name
			FROM {db_prefix}arcade_newshouts AS s
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = s.id_member)
			ORDER BY id_shout DESC
			LIMIT 0, {int:limit}',
			array(
				'limit' => !empty($modSettings['arcade_show_shouts']) ? $modSettings['arcade_show_shouts'] : 25,
			)
		);

		while ($shout = $smcFunc['db_fetch_assoc']($result))
		{
			$shouts[] = array(
				'id_shout' => $shout['id_shout'],
				'id_member' => $shout['id_member'],
				'content' => $shout['content'],
				'time' => $shout['time'],
				'real_name' => $shout['real_name'],
			);
		}

		$smcFunc['db_free_result']($result);
	}

	foreach($shouts as $shout)
	{
		$content .= '
					<div style="margin: 4px;">
						<div style="border: dotted 1px; padding: 2px 4px 2px 4px;" class="windowbg2">';

		if (allowedTo('arcade_admin'))
			$content .= '
							<a href="' . $scripturl.'?action=arcade;sa=shout;del=' . $shout['id_shout'] . '"><img style="border: 0px;" src="' . $settings['images_url'] . '/arc_icons/del1.png" alt="X"  title="' . $txt['arcade_shout_del'] . '"/></a>&nbsp;';

		$content .= '
							<b>' . $shout['real_name'] . '</b>
						</div>
						<div style="padding: 2px;">' . timeformat($shout['time']) . '</div>
						<div style="padding: 4px;">' . wordwrap(parse_bbc(censorText($shout['content'])), 34, "\n", true) . '</div>
					</div>';


	}

	return $content;
}

function add_to_arcade_shoutbox($shout)
{
	global $user_info, $smcFunc, $arcSettings;

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_newshouts',
		array(
			'id_member' => 'int', 'content' => 'string-255', 'time' => 'int',
		),
		array(
			$user_info['id'], $shout, time(),
		),
		array('id_shout')
	);

	cache_put_data('arcade_shouts', null, 86400);
}
?>