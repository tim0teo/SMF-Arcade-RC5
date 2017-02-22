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

function template_arcade_above()
{
	global $settings, $context, $txt, $arcSettings, $scripturl, $db_count;

	$context['arcade']['queries_temp'] = $db_count;
	echo '<a name="top"></a>';

	// Show the start of the tab section.
    $tab='<td nowrap="nowrap" style="cursor: pointer; font-size: 11px; padding: 6px 10px 6px 10px;  border: solid 1px #ADADAD;border-top: 0px; border-bottom:0px; border-left:0px" align="center" onmouseover="this.style.backgroundPosition=\'0 -5px\'" onmouseout="this.style.backgroundPosition=\'0 0px\'">';
    $tab2='<td nowrap="nowrap" style="cursor: pointer; padding: 6px 6px 6px 6px;  border-top: 0px; border-bottom:0px;" align="center" onmouseover="this.style.backgroundPosition=\'0 -5px\'" onmouseout="this.style.backgroundPosition=\'0 0px\'">';


	$context['arcade']['buttons_set']['arcade'] =  array(
    	'text' => 'arcade',
    	'url' => $scripturl . '?action=arcade',
    	'lang' => true,
    );

    $context['arcade']['buttons_set']['stats'] =  array(
    	'text' => 'arcade_stats',
    	'url' => $scripturl . '?action=arcade;sa=stats',
    	'lang' => true,
    );

    $context['arcade']['buttons_set']['tour'] =  array(
    	'text' => 'arcade_tour_tour',
    	'url' => $scripturl . '?action=arcade;sa=tour',
    	'lang' => true,
    );


    if ($context['arcade']['can_admin_arcade'])
       	$context['arcade']['buttons_set']['arcadeadmin'] =  array(
    		'text' => 'arcade_administrator',
    		'url' => $scripturl . '?action=admin;area=managearcade',
    		'lang' => true,
			'is_last' => true,
    	);


	echo '
	<div id="moderationbuttons" class="margintop">
		', Arcade_DoToolBarStrip($context['arcade']['buttons_set'], 'bottom'), '
	</div>
	<table style="border: 0px;border-collapse: collapse;width: 100%;">
		<tr style="padding: 0px;" class="catbg">
			<td style="width: 100%;border: solid 1px #ADADAD; border-bottom:0px; border-right:0px;border-top: 0px; border-left:0px" align="center">&nbsp;</td>
		</tr>
	</table>';
}

// Game list
function template_e_arcade_list()
{
	global $sourcedir, $scripturl, $txt, $boardurl, $context, $settings, $arcSettings, $user_info;

	template_top_blocks();

	echo'

	<table class="bordercolor" style="width: 100%;border: 0px;border-spacing: 1px;border-collapse: separate;">
		<tr class="titlebg">
			<td colspan="5" class="smalltext" style="padding: 8px; " valign="middle">', $txt['pages'], ': ', $context['arcade']['pageIndex'], '   &nbsp;&nbsp;<a href="#bot"><b>', $txt['go_down'], '</b></a></td>
		</tr>';

	// Is there games?
	if (count($context['arcade']['games']) > 0)
	{
		echo '
		<tr>
			<td class="catbg3"></td>
			<td class="catbg3">', $txt['arcade_game_name'], '</td>
			<td class="catbg3" style="width: 5%; text-align: center;">', $txt['arcade_plays'], '</td>
			<td nowrap="nowrap" class="catbg3" style="width: 5%; text-align: center;">', $txt['arcade_personal_best'],'</td>
			<td class="catbg3" style="width: 5%; text-align: center;">', $txt['arcade_champion'],'</td>
		</tr>';

		// Loop thought all games in page
		foreach ($context['arcade']['games'] as $game)
		{
			// Print out game information
			echo '
			<tr>
				<td class="windowbg2" style="width: 70px;" align="center">', $game['thumbnail'] != '' ? '
					<a href="' . $game['url']['play'] . '"><img style="width: 70px;height: 70px" src="' . $game['thumbnail'] . '" alt="'.$game['name'].'" title="'.$txt['arcade_champions_play'].' '.$game['name'].'"/></a>' : '', '
				</td>

				<td class="windowbg">
					<div style="float: left">
					<div><a href="', $game['url']['play'], '">', $game['name'], '</a></div>
					<div class="smalltext"><a href="javascript:popup(\''.$game['url']['pop'].'\',\''.$game['flash']['width'].'\',\''.$game['flash']['height'].'\')" >',$txt['arcade_popup'],'</a></div>';
					// Is there description?
			if (!empty($game['description']))
				echo '
					<div class="smalltext">', $game['description'], '</div>';


			if ($game['highscoreSupport']) // Does this game support highscores?
				echo '
					<div class="smalltext"><a href="' . $game['url']['highscore'] . '">' . $txt['arcade_viewscore'] . '</a></div>';

			if (!empty($game['topic_id']) && $arcSettings['arcadePostTopic']!=0)
				echo '
					<div class="smalltext"><a href="', $scripturl, '?topic=', $game['topic_id'], '">', $txt['arcade_topic_talk'],'</a></div></div>';

			echo '
					</div>
					<div style="float: right; text-align: right;" class="smalltext">';
					// Rating

			if ($game['rating2'] > 0)
				echo '
						<div>',
				str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $game['rating2']),
				str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="" />' , 5 - $game['rating2']), '
						</div>';

			// Category
			if ($game['category']['name'])
				echo '
					<a href="', $game['category']['link'], '">', $game['category']['name'], '</a><br />';

			if (in_array(1, $user_info['groups']))
				echo '
					<a href="', $game['url']['edit'], '">', $txt['arcade_edit'], '</a><br />';

				// Favorite link (if can favorite)
			if ($context['arcade']['can_favorite'])
				echo '
					<a href="', $game['url']['favorite'], '" onclick="arcade_favorite(', $game['id'] , '); return false;">
					', !$game['isFavorite'] ?
					'<img id="favgame' . $game['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite.gif" alt="' . $txt['arcade_add_favorites'] . '" title="' . $txt['arcade_add_favorites'] . '"/>' :
					'<img id="favgame' . $game['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite2.gif" alt="' . $txt['arcade_remove_favorite'] .'" title="' . $txt['arcade_remove_favorite'] . '" />', '</a>';

			echo '
					</div>
				</td>
				<td class="windowbg2" style="width: 5%; text-align: center;">', $game['number_plays'], '</td>';

			// Show personal best and champion only if game doest support highscores
			if ($game['highscoreSupport'] && $game['isChampion'])
			{
				echo '
				<td class="windowbg2" style="width: 5%; text-align: center;">';

				if ($game['personalBest']>0 && $user_info['id']==$game['champion']['member_id'])
					echo'
					<img src="' . $settings['images_url'] . '/arc_icons/cup_g.gif" border="0" alt="cup_g" title="' . $txt['arcade_you_are_first'] . '&nbsp;' . $game['name'] . '"/><br />';
				elseif ($game['personalBest']>0 && $user_info['id']==$game['secondPlace']['member_id'])
					echo'
					<img src="' . $settings['images_url'] . '/arc_icons/cup_s.gif" border="0" alt="cup_s" title="' . $txt['arcade_you_are_second'].'&nbsp;' . $game['name'] . '" /><br />';
				elseif ($game['personalBest']>0 && $user_info['id']==$game['thirdPlace']['member_id'])
					echo'
					<img src="' . $settings['images_url'] . '/arc_icons/cup_b.gif" border="0" alt="cup_b" title="' . $txt['arcade_you_are_third'] . '&nbsp;' . $game['name'] . '"/><br />';

				echo ($game['isPersonalBest'] ? $game['personalBest'] :  $txt['arcade_no_scores']), '
				</td>
				<td class="windowbg2" style="width: 15%; text-align: center;">
					<table style="width: 100%;">
						<tr>
							<td style="width: 10%; text-align: left;"><img src="' . $settings['images_url'] . '/arc_icons/cup_g.gif" border="0" alt="gold" title="' . $txt['arcade_first'] . '"/></td>
							<td style=" text-align: center;">', $game['champion']['memberLink'], ' </td>
							<td style="width: 15%; text-align: right;">', $game['champion']['score'], '</td>
						</tr>';
				if ($game['secondPlace']['score'] > 0)
					echo'
							<tr>
								<td style="width: 10%; text-align: left;"><img src="'. $settings['images_url']. '/arc_icons/cup_s.gif" border="0" alt="silver" title="' . $txt['arcade_second'].'"/></td>
								<td>', $game['secondPlace']['memberLink'], ' </td>
								<td style="width: 15%; text-align: right;">', $game['secondPlace']['score'], '</td>
							</tr>';

				if ($game['thirdPlace']['score'] > 0)
					echo'
						<tr>
							<td style="width: 10%; text-align: left;"><img src="'. $settings['images_url']. '/arc_icons/cup_b.gif" border="0" alt="bronze" title="'.$txt['arcade_third'].'"/></td>
							<td>', $game['thirdPlace']['memberLink'], ' </td>
							<td style="width: 15%; text-align: right;">', $game['thirdPlace']['score'], '</td>
						</tr>';

				echo'
					</table>
				</td>';
			}
			elseif (!$game['highscoreSupport'])
				echo '
				<td class="windowbg2" colspan="2" style="text-align: center; width: 30%;">', $txt['arcade_no_highscore'], '</td>';
			else
				echo '
				<td class="windowbg2" colspan="2" style="text-align: center; width: 30%;">', $txt['arcade_no_scores'], '</td>';

			echo '
		</tr>';
		}
	}
	else
		echo '
		<tr>
			<td class="catbg3"><b>', $txt['arcade_no_games'], '</b></td>
		</tr>';

	echo '
	</table>
	<table class="bordercolor" style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;">
		<tr class="titlebg">
			<td colspan="4"  class="smalltext" style="padding:8px;" valign="middle">', $txt['pages'], ': ', $context['arcade']['pageIndex'], '   &nbsp;&nbsp;<a href="#top"><b>', $txt['go_up'], '</b></a></td>
		</tr>
	</table>';

	if (!$user_info['is_guest'] && $arcSettings['arcade_active_user']==1)
	{
		$context['arcade']['who'] = true;
		echo'
		<table class="bordercolor" style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;">
			<tr>
				<td class="catbg centertext" style="padding: 5px;" colspan="0">',$txt['who_arcade_active'],'</td>
			</tr>
			<tr>
				<td class="windowbg2" style="vertical-align: bottom;">';
		$i = 0;

		foreach ($context['members'] as $member)
		{
			if ((stristr($member['action'],"arcade"))&&stristr($member['action'],"20"))
			{
				if ($i != 0)
					echo ' | ';

				echo $member['action'], '
					<span', $member['is_hidden'] ? ' style="font-style: italic;"' : '', '>','
						<a href="#" onclick="window.open(\'', $scripturl, '?action=arcade;sa=pro_stats;ta=', $member['id'], '\',\'PopupWindow\',\'height=300,width=700,scrollbars=1,resizable=1\');return false;" title="' . $member['time'] . ' - ' . $member['ip'] . '"' . (empty($member['color']) ? '' : ' style="color: ' . $member['color'] . '"') . '>' . $member['name'] . '</a>', '</span>&nbsp;';
				$i++;
			}
		}

		echo '
				</td>
			</tr>
		</table>';
		$context['arcade']['who'] = false;
	}
}

function template_arcade_front_page()
{
	global $scripturl, $txt, $context, $settings;
	template_top_blocks();
	echo '
	<div class="bordercolor">
		<table class="bordercolor" style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;">
			<tr class="catbg">
				<td colspan="4" style="padding: 4px;">', $context['arcade']['frontPage']['pageName'], '</td>
			</tr>
			<tr class="windowbg">
				<td style="display: none;"></td>';

	foreach($context['arcade']['frontPage']['games'] as $game)
	{
		$ratecode = '';
		$rating = $game['rating'];

		if ($rating > 0)
		{
			$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $rating);
			$ratecode .= str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" />' , 5 - $rating);
		}

		echo'
				<td width="25%" style="padding: 4px;"><div class="centertext">
					<table style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;">
						<tr>
							<td colspan="2"><div align="center"><i><b>', $game['name'], '</b></i></div></td>
						</tr>
						<tr>
							<td class="centertext">
								', ($game['thumbnail'] != '' ? '<a href="' . $game['url']['play'] . '"><img src="' . $game['thumbnail'] . '" style="width: 60px;height: 60px;" alt="ico" title="' . $txt['arcade_play']. '&nbsp;' . $game['name'] . '"/></a>' : ''), '
								<div class="smalltext">
									<a href="', $game['url']['play'], '">', $game['name'], '</a>
								</div>
							</td>
						</tr>';
		if ($rating > 0)
			echo '
						<tr>
							<td class="centertext">', $ratecode, '</td>
						</tr>';

		echo '
						<tr>
							<td class="centertext">
								<div class="smalltext">';

		if ($game['isChampion'])
			echo '
									<strong>', $txt['arcade_champion'], ':</strong>&nbsp;', $game['champion']['memberLink'], '&nbsp;-&nbsp;', $game['champion']['score'], '
								</div>';
		else
			echo $txt['arcade_no_scores'], '
								</div>';

		echo '
							</td>
						</tr>
					</table>
				</td>';
	}

		echo'
			</tr>
		</table>
	</div>';
}

function template_arcade_tour_show()
{
	global $scripturl, $txt, $context, $settings,  $user_info;

	template_top_blocks();
	arcade_tour_buttons();

	echo '
	<div class="bordercolor">
		<table style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;">

			<tr class="titlebg">
				<td colspan="6" class="smalltext" style="padding: 8px; " valign="middle">', $txt['arcade_tour_tours'], ':&nbsp;', $context['arcade']['tour']['pageindex'], '&nbsp;&nbsp;&nbsp;
					<a href="#bot"><b>', $txt['go_down'], '</b></a>
				</td>
			</tr>
			<tr class="titlebg">
				<td style="padding: 4px;">&nbsp;</td>
				<td style="padding: 4px;">', $txt['arcade_game_name'], '</td>
				<td style="padding: 4px;">', $txt['arcade_tour_players'], '</td>
				<td style="padding: 4px;">', $txt['arcade_tour_starter'], '</td>
				<td style="padding: 4px;">', $txt['arcade_time'], '</td>
				<td style="padding: 4px;">', $txt['arcade_tour_status'], '</td>
			</tr>';

	list($i, $a) = array(0, array('windowbg', 'windowbg2'));
	if (isset($context['arcade']['tour']['list']))
	{
		foreach ($context['arcade']['tour']['list'] as $tour)
		{
			$password = ($tour['password'] != "" ? '<i>' . $txt['arcade_tour_pass'] . '</i>' : '');

			echo '
			<tr class="', $a[$i % 2], '">';

			if ($tour['id_member'] == $user_info['id'] || allowedTo('admin_arcade'))
				echo '
				<td style="padding: 4px;">
					<a href="', $scripturl, '?action=arcade;sa=tour;ta=del;idd=', $tour['id_tour'], '"><img src="' . $settings['images_url'] . '/arc_icons/del.png" alt="*" /></a>
				</td>';
			else
				echo '
				<td style="padding: 4px;">&nbsp;</td>';

			echo '
				<td style="padding: 4px;">
					<a href="', $scripturl,'?action=arcade;sa=tour;ta=join;id=', $tour['id_tour'],'">', $tour['name'],'</a> ', $password, '
				</td>
				<td style="padding: 4px;">', $tour['joined'], '/', $tour['players'], '</td>
				<td style="padding: 4px;">
					<a href="' . $scripturl . '?action=profile;u=' . $tour['id_member'] . '">', $tour['creator'], '</a>
				</td>
				<td style="padding: 4px;">', timeformat($tour['tour_start_time']), '</td>';

			if ($tour['active'] == 1)
				echo '
				<td style="padding: 4px;">' . $txt['arcade_running'] . '</td>';
			elseif ($tour['active'] == 2)
				echo '
				<td style="padding: 4px;">' . $txt['arcade_ended'] . '</td>';
			else
				echo '
				<td style="padding: 4px;">
					<a href="',$scripturl,'?action=arcade;sa=tour;ta=join;id=',$tour['id_tour'],'">',$txt['arcade_tour_join'],'</a>
				</td>';

			echo '
			</tr>';
			$i++;
		}
	}
	else
		echo '
			<tr class="', $a[$i % 2], '">
				<td colspan="6" style="padding: 4px;">', $txt['arcade_tour_no_tour'], '</td>
			</tr>';

	echo '
			<tr class="titlebg">
				<td colspan="6" class="smalltext" style="padding: 8px;" valign="middle">', $txt['arcade_tour_tours'], ':&nbsp;', $context['arcade']['tour']['pageindex'], '&nbsp;&nbsp;&nbsp;
					<a href="#top"><b>', $txt['go_up'], '</b></a>
				</td>
			</tr>
		</table>
	</div>';
}

function template_arcade_tour_join()
{
	global $scripturl, $txt, $context, $settings,  $user_info;

	template_top_blocks();
	arcade_tour_buttons();

	$tours = &$context['arcade']['tour']['tourdata'];

	list($i, $a, $joinedPlayers, $joined) = array(1, array('windowbg', 'windowbg2'), &$context['arcade']['tour']['players'], array(0));
	echo'
	<table style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;" class="bordercolor">
		<tr class="titlebg">
			<td colspan="2" style="height: 25px;">', $txt['arcade_tour_tour'], '&nbsp;-&nbsp;', $tours['name'], '</td>
		</tr>
		<tr class="windowbg">
			<td colspan="2" style="height: 25px; text-align: center;">
				<b><i>', $txt['arcade_tour_info'], '</b></i>
			</td>
		</tr>
		<tr style="vertical-align: top;">
			<td style="width: 35%;">
				<table style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;" class="bordercolor">
					<tr>
						<td style="padding: 5px;" class="windowbg">', $txt['arcade_tour_tour'], '</td>
						<td style="padding: 5px;" class="windowbg2">', $tours['name'], '</td>
					</tr>
					<tr>
						<td style="padding: 5px;" class="windowbg">', $txt['arcade_tour_started'], '</td>
						<td style="padding: 5px;" class="windowbg2">', $tours['creator'], '</td>
					</tr>
					<tr >
						<td style="padding: 5px;" class="windowbg">', $txt['arcade_tour_players'], '</td>
						<td style="padding: 5px;" class="windowbg2">', $tours['players'], '</td>
					</tr>
					<tr>
						<td style="padding: 5px;" class="windowbg">', $txt['arcade_tour_rounds'], '</td>
						<td style="padding: 5px;" class="windowbg2">', $tours['rounds'], '</td>
					</tr>
					<tr>
						<td style="padding: 5px;" class="windowbg">', $txt['arcade_time'], '</td>
						<td style="padding: 5px;" class="windowbg2">', timeformat($tours['tour_start_time']), '</td>
					</tr>
				</table>
			</td>
			<td align="center" class="windowbg2">';

	foreach($joinedPlayers as $key => $players)
		$joined[] = $key;

	if (isset($tours['passFailed']))
		echo '
				<br /><font color="#FF0000" size="+1">', $txt['arcade_tour_wrong_pass'], '</font><br />';

	// Show the join button?
	if (!in_array($user_info['id'],$joined) && $tours['active'] < 1)
	{
		echo '
				<br />
				<form action="', $scripturl, '?action=arcade;sa=tour;ta=join;id=', $tours['id_tour'], ';in=1" method="post">';
		if ($tours['password'] != "")
			echo $txt['arcade_tour_pass'], '
					:&nbsp;<input type="password" name="pass" /><br />';

		echo '
					<input type="submit" value="', $txt['arcade_tour_join'], '" />
				</form>';
	}
	else
	{
		if (in_array($user_info['id'],$joined) && $tours['active'] != 2)
			echo '
				<br /><span style="font-size: 110%;">', $txt['arcade_tour_joined'], '</span><br /><br />';
		elseif ($tours['active'] == 2)
		{
			echo '
				<br /><span style="font-size: 110%;">', $txt['arcade_tour_ended'], '</span><br /><br />';

			if (count($context['arcade']['tour']['winner']) == 1)
				echo'
				<img src="' . $settings['images_url'] . '/arc_icons/cup_g.gif" border="0" alt="cup_g" title="' . $txt['arcade_you_are_first'] . '" />
				<span style="color" #FF0000;font-size: 110%;" size="+1">&nbsp;', $txt['arcade_txt_WINNER'], '&nbsp;
					<img src="' . $settings['images_url'] . '/arc_icons/cup_g.gif" border="0" alt="cup_g" title="' . $txt['arcade_you_are_first'].'"/><br /><br />', $context['arcade']['tour']['winner'][0], '
				</span><br />';
			else
				echo '
				<span style="color: #FF0000;font-size: 110%;" size="+1">', $txt['arcade_txt_itsadraw'], '</span><br /><br />';
				foreach($context['arcade']['tour']['winner'] as  $winners)
					echo '
				<span style="color: #FF0000;">&nbsp;', $winners, '&nbsp;</span><br />';
		}
		else
			echo '
				<span style="font-size: 110%;">', $txt['arcade_tour_cant_join'], '</span><br /><br />';
	}

	echo '
			</td>
		</tr>';
	$i = 1;

	echo '
		<tr style="vertical-align: top;">
			<td colspan="2" class="windowbg2">
				<table style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;" class="bordercolor">
					<tr>
						<td style="padding: 5px;" class="windowbg centertext"><b><i>', $txt['arcade_tour_heading2'], '</b></i></td>
					</tr>
				</table>
				<table border="0" cellspacing="1" cellpadding="5" class="bordercolor">
					<tr>
						<td style="padding: 5px;" class="windowbg2">&nbsp;</td>';

	// Add player to the row
	foreach($joinedPlayers as $key => $players)
	{
		echo '
						<td style="padding: 5px;" class="windowbg centertext">
							<a href="' . $scripturl.'?action=profile;u=' . $key . '">', $players['players'], '</a>';

		if (allowedTo('admin_arcade')&& $tours['active'] != 2)
			echo'
							<br />
							<a href="' . $scripturl.'?action=arcade;sa=tour;ta=delplay;tid=' . $tours['id_tour'] . ';u=' . $key . '">
								<img src="' . $settings['images_url'] . '/arc_icons/del2.png" alt="ico" style="border: 0px;width: 10px;height: 10px;" title="' . $txt['arcade_tour_remove1'] . '" />
							</a>&nbsp;
							<a href="' . $scripturl . '?action=arcade;sa=tour;ta=delplay;tid=' . $tours['id_tour'] . ';u=' . $key . ';lower=1">
								<img src="' . $settings['images_url'] . '/arc_icons/del1.png" alt="ico" style="border: 0px;width: 10px;height: 10px;" title="' . $txt['arcade_tour_remove2'] . '" />
							</a>';
	}
	echo '
						</td>
					</tr>';

	foreach($context['arcade']['tour']['rounds'] as $key => $r)
	{
		echo '
					<tr>
						<td style="padding: 5px;" class="windowbg"><b><i>', $txt['arcade_tour_round'], '&nbsp;' ,$i, '&nbsp;-&nbsp;', $r['game_name'], '</b></i></td>';

		foreach($joinedPlayers as $id => $arr1)
		{
			$match = 0;
			if (is_array($context['arcade']['tour']['scores']))
			{
				foreach($context['arcade']['tour']['scores'] as $k => $score)
				{
					if ($score['id_game'] == $r['id_game'] && $score['id_member'] == $id  &&  $score['round_number'] == $i)
					{
						$match = 1;
						echo'
						<td style="padding: 5px;" class="windowbg2 centertext">', $score['score'], '</td>';
					}
				}
			}

			if ($match == 0)
			{
				if ($user_info['id'] == $id)
					echo'
						<td style="padding: 5px;" class="windowbg2 centertext">
							<a href="' . $scripturl . '?action=arcade;sa=tour;ta=play;tid=' . $tours['id_tour'] . ';gid=' . $r['id_game'] . ';rid=', $i, '">', $txt['arcade_tour_wait'], '</a>
						</td>';
				else
					echo'
						<td style="padding: 5px;" class="windowbg2 centertext">', $txt['arcade_tour_wait'], '</td>';
			}
		}
		echo '
					</tr>';
		$i++;
	}

	echo'
					<tr>';
	if ($tours['active']==2)
	{
    	echo '
						<td style="padding: 5px;text-align: right;" class="windowbg"><b><i>', $txt['arcade_txt_results'], '</b></i></td>';

    	foreach($joinedPlayers as $key => $players)
    	{
    		echo '
						<td class="windowbg2 centertext" style="padding: 5px;">', $players['total'], '</td>';
    	}

    	echo '
					</tr>';
	}

	echo '
				</table>
			</td>
		</tr>
	</table>';
}

function template_arcade_tour_new()
{
	global $scripturl, $txt;

	//template_top_blocks();
	arcade_tour_buttons();

	//max players and max rounds
	list($maxr, $maxp) = array(10, 10);

	echo '
	<form name="tour" action="',$scripturl,'?action=arcade;sa=tour;ta=new;step=2" method="post">
		<table style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;" class="bordercolor">
			<tr class="catbg">
				<td style="padding: 5px;" colspan="2">', $txt['arcade_tour_new_tour'], '</td>
			</tr>
			<tr class="windowbg">
				<td syle="padding: 5px;">
					<input type="hidden" name="step" value="1" />
					<div class="maintour">
						<div class="lefty">
							<div class="left">',$txt['arcade_game_name'],': </div>
							<div class="left">',$txt['arcade_tour_password'],': </div>
							<div class="left">',$txt['arcade_tour_many_players'],': </div>
							<div class="left">',$txt['arcade_tour_many_rounds'],':</div>
						</div>
						<div class="righty">
							<div class="right">
								<input type="text" name="name" />
							</div>
							<div class="right">
								<input type="password" name="pass" />
							</div>
							<div class="right">
								<select name="players">';
	$i = 2;
	while($i <= $maxp)
	{
		echo '
									<option value="', $i,'">', $i, '</option>';
		$i++;
	}

	echo '
								</select>
							</div>
							<div class="right">
								<select name="rounds" id="rounds" onchange="arcade_tour_games(rounds.value);">';
	$i = 0;
	while($i <= $maxr)
	{
		echo '
									<option value="',$i,'">',$i,'</option>';
		$i++;
	}

	echo '
								</select>
							</div>
						</div>
					</div>
					<div class="maintour" id="tourgames"></div>
				</td>
			</tr>
		</table>
	</form>';
}

function arcade_tour_buttons()
{
	global $settings, $context, $txt, $arcSettings, $scripturl;

	// Show the start of the tab section.
    $tab='<td style="white-space: nowrap;cursor: pointer; font-size: 11px; padding: 6px 10px 6px 10px;  border: solid 1px #ADADAD;border-top: 0px; border-bottom:0px; border-left:0px" class="centertext" onmouseover="this.style.backgroundPosition=\'0 -5px\'" onmouseout="this.style.backgroundPosition=\'0 0px\'">';
    $tab2='<td style="white-space: nowrap;cursor: pointer; padding: 6px 6px 6px 6px;  border-top: 0px; border-bottom:0px;" class="centertext" onmouseover="this.style.backgroundPosition=\'0 -5px\'" onmouseout="this.style.backgroundPosition=\'0 0px\'">';

    if ($context['arcade']['tour']['show'] != 0)
        $context['arcadetour']['buttons_set']['newtour'] =  array(
			'text' => 'arcade_tour_new_tour',
			'url' => $scripturl . '?action=arcade;sa=tour;ta=new',
			'lang' => true
		);


    if ($context['arcade']['tour']['show']!=2)
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

	echo '
	<div id="moderationbuttons" class="margintop">
		', Arcade_DoToolBarStrip($context['arcadetour']['buttons_set'], 'bottom'), '
	</div>
	<table style="border: 0px;width: 100%;border-collapse: collapse;">
		<tr class="catbg">
			<td class="centertext" style="width: 100%;border: solid 1px #ADADAD;border-bottom: 0px;border-right: 0px;border-top: 0px;border-left: 0px;">&nbsp;</td>
		</tr>
	</table>';
}

function template_arcade_game_play()
{
	global $scripturl, $txt, $context, $settings;

	echo '
	<div class="tborder">
		<table class="bordercolor" style="border: 0px;width: 100%;border-collapse: collapse;">
			<tr class="catbg">
				<td style="padding: 4px;">', $context['arcade']['game']['name'], '</td>
			</tr>
			<tr class="windowbg">
				<td style="padding: 4px;">
					<div class="centertext">
						', $context['arcade']['game']['html'], '
						', (!$context['arcade']['can_submit'] ? '<br /><b>' . $txt['arcade_cannot_save'] . '</b>' : ''), '
						<br />', $context['arcade']['game']['help'], '
					</div>
				</td>
			</tr>';

	if ($context['arcade']['game']['isChampion'])
	{
		echo'
			<tr class="windowbg">
				<td style="padding: 4px;">
					<div class="centertext">
						<strong>', $txt['arcade_champion'], ':</strong> ', $context['arcade']['game']['champion']['memberLink'], '&nbsp;-&nbsp;', $context['arcade']['game']['champion']['score'], '&nbsp;&nbsp;&nbsp;&nbsp;';

		if ($context['arcade']['game']['isPersonalBest'])
			echo '
						<strong>', $txt['arcade_personal_best'], ':</strong> ', $context['arcade']['game']['personalBest'];

		echo'
					</div>
				</td>
			</tr>';
	}

	echo'
		</table>
	</div>';
}

function template_arcade_game_highscore()
{
	global $scripturl, $txt, $context, $settings,$arcSettings;

	$game = &$context['arcade']['game'];

	echo '
	<div>
		<table class="bordercolor" style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;">';

	if (isset($context['arcade']['new_score'])) // Was score submitted
	{
		list($score, $ratecode, $rating) = array(&$context['arcade']['new_score'], '', $context['arcade']['game']['rating']);
		if ($context['arcade']['can_rate'])
		{
			// Can rate
			for ($i = 1; $i <= 5; $i++)
			{
				if ($i <= $rating)
					$ratecode .= '<a href="' . $scripturl . '?action=arcade;sa=rate;game=' . $context['arcade']['game']['id'] . ';rate=' . $i . ';sesc=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['arcade']['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" /></a>';
				else
					$ratecode .= '<a href="' . $scripturl . '?action=arcade;sa=rate;game=' . $context['arcade']['game']['id'] . ';rate=' . $i . ';sesc=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['arcade']['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" /></a>';
			}
		}
		else
		{
			// Can't rate
			$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $rating) . str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" />' , 5 - $rating);
		}

		echo '
			<tr class="titlebg">
				<td style="padding: 4px;" colspan="5">', $txt['arcade_submit_score'],' ',$game['name'], '</td>
			</tr>
			<tr class="windowbg">
				<td style="padding: 4px;" colspan="3" class="centertext">
					<table class="centertext">
						<tr>
							<td class="centertext">
								', $context['arcade']['game']['thumbnail'] != '' ? '<div><a href="' .$scripturl . '?action=arcade;sa=play;game=' . $context['arcade']['game']['id'] . '"><img src="' . $context['arcade']['game']['thumbnail'] . '" alt="icon" title="'.$txt['arcade_play'].' '.$game['name'].'"/></a></div>' : '', '
							</td>
						</tr>
						<tr>
							<td class="centertext">', $txt['arcade_rate_game'], '&nbsp;', $game['name'], '&nbsp;', $ratecode, '</td>
						</tr>';

		// Favorite link (if can favorite)
		if ($context['arcade']['can_favorite'])
			echo '
						<tr>
							<td class="centertext">
								<a href="', $context['arcade']['game']['url']['favorite'], '" onclick="arcade_favorite(', $context['arcade']['game']['id'], '); return false;">', !$context['arcade']['game']['isFavorite'] ?  ''.$txt['arcade_add_favorites'].' <img id="favgame' . $context['arcade']['game']['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite.gif" alt="' . $txt['arcade_add_favorites'] . '" />' : '' . $txt['arcade_remove_favorite'] .' <img id="favgame' . $context['arcade']['game']['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite2.gif" alt="' . $txt['arcade_remove_favorite'] .'" />', '</a>
							</td>
						</tr>';

		echo'
						<tr>
							<td class="centertext">
								<a href="' .$scripturl . '?action=arcade;sa=play;game=' . $context['arcade']['game']['id'] . '">',$txt['arcade_play_again'],'</a>
							</td>
						</tr>
						<tr>
							<td class="centertext">
								<a href="javascript:popup(\''.$game['url']['pop'].'\',\''.$game['flash']['width'].'\',\''.$game['flash']['height'].'\')" >' . $txt['arcade_popup'] . '</a>
							</td>
						</tr>
						<tr>
							<td class="centertext">
								<a href="' . $scripturl . '?action=arcade">', $txt['arcade_play_other'], '</a></td>
						</tr>
					</table>
				</td>
				<td style="padding: 4px;" colspan="2" class="centertext">';

		if ($context['arcade']['game']['isChampion'])
			echo '
					<div>
						<strong>', $txt['arcade_champion'], ':</strong> ', $context['arcade']['game']['champion']['memberLink'], '&nbsp;-&nbsp;', $context['arcade']['game']['champion']['score'], '&nbsp;&nbsp;&nbsp;&nbsp;';

		if ($context['arcade']['game']['isPersonalBest'])
			echo '
						<strong>', $txt['arcade_personal_best'], ':</strong> ', $context['arcade']['game']['personalBest'], '
					</div>';

		// No permission to save
		if (!$score['saved'])
			echo '
					<br />
					<div>
						<strong>', $txt['arcade_txt_your'], $txt['arcade_score'], ':</strong> ', $score['score'], '<br /><br />', $txt[$score['error']], '<br />
					</div>';
		else
		{
			echo '
					<br />
					<div>
						<strong>', $txt['arcade_txt_your'], $txt['arcade_score'], ':</strong> ', $score['score'], '<br /><br />', $txt['arcade_score_saved'], '<br />
					</div>';

			if ($score['is_new_champion'])
				echo '
					<div>', $txt['arcade_you_are_now_champion'], '</div>';
			elseif ($score['is_personal_best'])
				echo '
					<div>', $txt['arcade_this_is_your_best'], '</div>';

			if ($score['can_comment'])
				echo '
					<div id="edit', $score['id'], '">
						<form action="', $scripturl, '?action=arcade;sa=comment;game=', $game['id'], ';score=',  $score['id'], '" onsubmit="arcadeCommentEdit(', $score['id'], ', ', $game['id'], ', 1); return false;" method="post">
							<input type="text" id="c', $score['id'], '" name="comment" style="width: 95%;" />
							<input type="submit" value="', $txt['arcade_save'], '" />
						</form>
					</div>';
		}

		if ($arcSettings['arcadePostTopic'] != 0)
			echo '
					<div>
						<br /><a href="', $scripturl, '?topic=', $game['topic_id'], '">', $txt['arcade_topic_talk2'],' ',$game['name'], ' here</a>
					</div>';

		echo '
				</td>
			</tr>';
	}

	if (count($context['arcade']['scores']) > 0) // There must be more than zero scores or we will skip them :)
	{
		if (!isset($context['arcade']['new_score'])) // Was score submitted
		{
			list($ratecode, $rating) = array('', $context['arcade']['game']['rating']);

			if ($context['arcade']['can_rate'])
			{
				// Can rate
				for ($i = 1; $i <= 5; $i++)
				{
					if ($i <= $rating)
						$ratecode .= '<a href="' . $scripturl . '?action=arcade;sa=rate;game=' . $context['arcade']['game']['id'] . ';rate=' . $i . ';sesc=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['arcade']['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" /></a>';
					else
						$ratecode .= '<a href="' . $scripturl . '?action=arcade;sa=rate;game=' . $context['arcade']['game']['id'] . ';rate=' . $i . ';sesc=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['arcade']['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" /></a>';
				}
			}
			else
				$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $rating) . str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" />' , 5 - $rating);

			echo '
			<tr class="windowbg">
				<td style="padding: 4px;" class="centertext" colspan="5">
					<table class="centertext">
						<tr>
							<td class="centertext">
								', $context['arcade']['game']['thumbnail'] != '' ? '<div><a href="' . $scripturl . '?action=arcade;sa=play;game=' . $context['arcade']['game']['id'] . '"><img src="' . $context['arcade']['game']['thumbnail'] . '" alt="icon" title="' . $txt['arcade_play'].'&nbsp;' . $game['name'].'"/></a></div>' : '', '
							</td>
						</tr>
						<tr>
							<td class="centertext">', $txt['arcade_rate_game'],' ', $game['name'],'&nbsp;', $ratecode, '</td>
						</tr>';

			// Favorite link (if can favorite)
			if ($context['arcade']['can_favorite'])
				echo '
						<tr>
							<td class="centertext">
								<a href="', $context['arcade']['game']['url']['favorite'], '" onclick="arcade_favorite(', $context['arcade']['game']['id'], '); return false;">', !$context['arcade']['game']['isFavorite'] ?  ''.$txt['arcade_add_favorites'].' <img id="favgame' . $context['arcade']['game']['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite.gif" alt="' . $txt['arcade_add_favorites'] . '" />' : '' . $txt['arcade_remove_favorite'] .' <img id="favgame' . $context['arcade']['game']['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite2.gif" alt="' . $txt['arcade_remove_favorite'] .'" />', '</a>
							</td>
						</tr>';

			echo'
						<tr>
							<td class="centertext">
								<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $context['arcade']['game']['id'] . '">',$txt['arcade_play'],' ',$game['name'],'</a>
							</td>
						</tr>
						<tr>
							<td class="centertext">
								<a href="javascript:popup(\''.$game['url']['pop'].'\',\''.$game['flash']['width'].'\',\''.$game['flash']['height'].'\')" >',$txt['arcade_popup'],'</a>
							</td>
						</tr>
					</table>
				</td>
			</tr>';
		}

		echo'
			<tr class="titlebg">
				<td colspan="5" style="padding: 4px;height: 25px;" class="smalltext">', $txt['arcade_highscores'], ' ', isset($context['arcade']['pageIndex']) ? ' ' . $context['arcade']['pageIndex'] : '' ,'</td>
			</tr>
			<tr class="catbg3">
				<td style="padding: 4px;width: 50px;">', $txt['arcade_position'], '</td>
				<td style="padding: 4px;width: 150px;">', $txt['arcade_member'], '</td>
				<td style="padding: 4px;width: 50px;">', $txt['arcade_score'], '</td>
				<td style="padding: 4px;width: 250px;">', $txt['arcade_time'], '</td>
				<tdstyle="padding: 4px;">', $txt['arcade_comment'], '</td>
			</tr>';

		$button['edit'] = create_button('modify.gif', 'arcade_edit', '', 'title="' . $txt['arcade_edit'] . '"');
		$button['delete'] = create_button('delete.gif', 'arcade_delete_score', '', 'title="' . $txt['arcade_delete_score'] . '"');

		foreach ($context['arcade']['scores'] as $score)
		{
			echo '
			<tr class="', $score['own'] ? 'windowbg3' : 'windowbg', '"', $score['highlight'] ? ' style="font-weight: bold;"' : '', '>
				<td class="windowbg2 centertext" style="padding: 4px;">', $score['position'], '</td>
				<td>', $score['memberLink'], '</td>
				<td  class="windowbg2" style="padding: 4px;">', $score['score'], '</td>
				<td style="padding: 4px;width: 300px;" class="centertext">', $score['time'], '</td>
				<td class="windowbg2">
					<div id="comment', $score['id'], '" style="float: left; ', $score['edit'] && $score['can_edit'] ? 'display: none;' : '', '">', $score['comment'], '</div>';
			if ($score['can_edit']) // Can edit
			{
				echo '
					<div id="edit', $score['id'], '" style="float: left; ', $score['edit'] ? '' : 'display: none;', ' width: 90%;">
						<form action="', $scripturl, '?action=arcade;sa=comment;game=', $game['id'], '" method="post" name="score_edit', $score['id'], '" onsubmit="arcadeCommentEdit(', $score['id'], ', ', $game['id'], '); return false;">
							<input type="hidden" name="score" value="', $score['id'], '" />
							<input type="text" name="comment" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" />
						</form>
					</div>';
			}

			// Buttons
			if ($score['can_edit'] || $context['arcade']['show_editor'])
			{
				echo '
					<div style="float: right">';

				// Edit
				if ($score['can_edit'])
					echo '
						<a onclick="arcadeCommentEdit(', $score['id'], ', ', $game['id'], ', 0); return false;" href="', $scripturl, '?action=arcade;sa=highscore;game=', $game['id'], ';edit;score=', $score['id'], '">', $button['edit'], '</a>';

					// Delete
				if ($context['arcade']['show_editor'])
					echo '
						<a onclick="return confirm(\'', $txt['arcade_really_delete'], '\');" href="', $scripturl, '?action=arcade;sa=highscore;game=', $game['id'], ';delete;score=', $score['id'], ';sesc=', $context['session_id'], '">', $button['delete'], '</a>';

				echo '
					</div>';
			}

			echo '
				</td>
			</tr>';
		}

		echo '
			<tr class="catbg3">
				<td style="padding: 4px;">', $txt['arcade_position'], '</td>
				<td style="padding: 4px;">', $txt['arcade_member'], '</td>
				<td style="padding: 4px;">', $txt['arcade_score'], '</td>
				<td style="padding: 4px;">', $txt['arcade_time'], '</td>
				<td style="padding: 4px;">', $txt['arcade_comment'], '</td>
			</tr>';
	}
	else
	{
		// No one has played this game
		echo '
			<tr class="windowbg">
				<td style="padding: 4px;" class="catbg3 centertext"><b>', $txt['arcade_no_scores'], '</b></td>
			</tr>';
	}

	echo '
			<tr class="titlebg">
				<td colspan="5" class="smalltext" style="padding: 4px;height: 25px;">', $txt['arcade_highscores'], ' ', isset($context['arcade']['pageIndex']) ? ' ' . $context['arcade']['pageIndex'] : '' ,'</td>
			</tr>
		</table>
	</div>';
}

function template_arcade_statistics()
{
	global $scripturl, $txt, $context, $settings, $arcSettings;

	echo '
	<table style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;" class="bordercolor">
		<tr class="titlebg">
				<td style="padding: 4px;" class="centertext" colspan="4">', $txt['arcade_stats'], '</td>
		</tr>
		<tr class="windowbg">
			<td colspan="4" style="padding: 4px;">', sprintf($txt['arcade_game_we_have_games'], $arcSettings['arcade_total_games']), '<br />
				',$txt['arcade_champions_tgp'],' ',$context['arcade']['statistics']['total'],'
			</td>
		</tr>
		<tr>
			<td style="padding: 4px;" class="catbg" colspan="2"><b>', $txt['arcade_most_played'], '</b></td>
			<td style="padding: 4px;" class="catbg" colspan="2"><b>', $txt['arcade_most_active'], '</b></td>
		</tr>
		<tr>
			<td style="padding: 4px;width: 20px;vertical-align: middle;" class="windowbg centertext"><img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" /></td>
			<td class="windowbg2" style="padding: 4px;vertical-align: top;">
				<table style="border: 0px;width: 100%;border-collapse: collapse;">';

	// Most played games
	if ($context['arcade']['statistics']['play'] != false)
	{
		foreach ($context['arcade']['statistics']['play'] as $game)
			echo '
					<tr>
						<td style="padding: 1px;width: 60%;vertical-align: top;">', $game['link'], '</td>
						<td style="padding: 1px;width: 20%;text-align: left;vertical-align: top;">', $game['plays'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
						<td style="padding: 1px;width: 20%;text-align: right;vertical-align: top;">', $game['plays'], '</td>
					</tr>';
	}

	echo '
				</table>
			</td>
			<td style="padding: 4px;width: 20px;vertical-align: middle;" class="windowbg centertext">
				<img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" />
			</td>
			<td style="padding: 4px;vertical-align: top;" class="windowbg2">
				<table style="border: 0px;width: 100%;border-collapse: collapse;">';

	// Most active in arcade
	if ($context['arcade']['statistics']['active'] != false)
	{
		foreach ($context['arcade']['statistics']['active'] as $game)
			echo '
					<tr>
						<td style="padding: 1px;width: 60%;vertical-align: top;">', $game['link'], '</td>
						<td style="padding: 1px;width: 20%;vertical-align: top;text-align: left">', $game['scores'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
						<td style="padding: 1px;width: 20%;vertical-align: top;text-align: right;">', $game['scores'], '</td>
					</tr>';
	}

	echo '
				</table>
			</td>
		</tr>
		<tr>
			<td style="padding: 4px;" class="catbg" colspan="2"><b>', $txt['arcade_best_games'], '</b></td>
			<td style="padding: 4px;" class="catbg" colspan="2"><b>', $txt['arcade_best_players'], '</b></td>
		</tr>
		<tr>
			<td style="padding: 4px;width: 20px;vertical-align: middle;" class="windowbg centertext"><img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" /></td>
			<td style="padding: 4px;vertical-align: top;" class="windowbg2">
				<table style="border: 0px;width: 100%;border-collapse: collapse;">';

	// Top rated games
	if ($context['arcade']['statistics']['rating'] != false)
	{
		foreach ($context['arcade']['statistics']['rating'] as $game)
			echo '
					<tr>
						<td style="padding: 1px;width: 60%;vertical-align: top;">', $game['link'], '</td>
						<td style="padding: 1px;width: 20%;vertical-align: top;text-align: left;">', $game['rating'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
						<td style="padding: 1px;width: 20%;vertical-align: top;text-align: right;">', $game['rating'], '</td>
					</tr>';
	}

	echo '
				</table>
			</td>
			<td class="windowbg centertext" style="padding: 4px;width: 20px;vertical-align: middle;" align="center">
				<img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" />
			</td>
			<td class="windowbg2" style="padding: 4px;vertical-align: top;">
				<table style="border: 0px;width: 100%;border-collapse: collapse;">';

	// Best players by champions
	if ($context['arcade']['statistics']['champions'] != false)
	{
		foreach ($context['arcade']['statistics']['champions'] as $game)
			echo '
					<tr>
						<td style="padding: 1px;width: 60%;vertical-align: top;">', $game['link'], '</td>
						<td style="padding: 1px;width: 20%;vertical-align: top;text-align: left;">', $game['champions'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
						<td style="padding: 1px;width: 20%;vertical-align: top;text-align: right;">', $game['champions'], '</td>
					</tr>';
	}

	echo '
				</table>
			</td>
		</tr>
		<tr>
			<td style="padding: 4px;" class="catbg" colspan="4"><b>', $txt['arcade_longest_champions'], '</b></td>
		</tr>
		<tr>
			<td style="padding: 4px;width: 20px;vertical-align: middle;" class="windowbg centertext">
				<img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" />
			</td>
			<td class="windowbg2" style="padding: 4px;vertical-align: top;" colspan="3">
				<table style="border: 0px;width: 100%;border-collapse: collapse;">';

	// Top rated games
	if ($context['arcade']['statistics']['longest'] != false)
	{
		foreach ($context['arcade']['statistics']['longest'] as $game)
			echo '
					<tr>
						<td style="padding: 1px;width: 40%;vertical-align: top;">', $game['member_link'], ' (', $game['game_link'], ')</td>
						<td style="padding: 1px;width: 20%;vertical-align: top;text-align: left;">', $game['duration'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
						<td style="padding: 1px;width: 40%;vertical-align: top;text-align: right;">', $game['current'] ? '<b>' . $game['duration'] . '</b>' : $game['duration'], '</td>
					</tr>';
	}

	echo '
			</table>
		</td>
	</tr>
</table>';
}

function template_arcade_below()
{
	global $txt;
	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
	<a name="bot"></a>', $txt['pdl_arcade_copyright'];
}

// XML templates
function template_xml() // General XML template
{
	global $context, $txt;

	$extra = isset($context['arcade']['extra']) ? $context['arcade']['extra'] : '';

	echo '
	<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
	<smf>
		<txt><![CDATA[', isset($txt[$context['arcade']['message']]) ? $txt[$context['arcade']['message']] : $context['arcade']['message'], ']]></txt>
		', $extra, '
	</smf>';
}

function template_xml_list()
{
	global $context, $txt;

	echo '
	<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
	<smf>';

	foreach ($context['arcade']['search']['games'] as $game)
		echo '
		<game>
			<id>', $game['id'], '</id>
			<name><![CDATA[', $game['name'], ']]></name>
			<url><![CDATA[', $game['url'], ']]></url>
		</game>';

	echo '
		<more>
			<is>', $context['arcade']['search']['more'], '</is>
			<url>', $context['arcade']['search']['more_url'], '</url>
		</more>
	</smf>';
}
?>