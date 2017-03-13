<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_list()
{
	global $sourcedir, $scripturl, $txt, $boardurl, $context, $settings, $modSettings, $user_info;

	echo'
	<div style="padding-top: 10px;"><span></span></div>
	<span style="vertical-align: middle;font-size: x-small;padding: 8px;">', $txt['pages'], ': ', $context['page_index'], '   &nbsp;&nbsp;<a href="#bot"><b>', $txt['go_down'], '</b></a></span>
	<div style="padding-top: 5px;"><span></span></div>
	<span class="clear upperframe"><span></span></span>
	<div class="roundframe" style="border-radius: 3px;">
		<div class="innerframe">
			<div class="cat_bar">
				<h3 class="catbg smalltext">
					<span style="display: table;width: 100%;border: 0px;border-collapse: collapse;">
						<span style="display: table-row;">
							<span class="clear" style="display: table-cell;vertical-align: middle;width: 33.3%;">&nbsp;</span>
							<span style="display: table-cell;vertical-align: middle;width: 33.3%;text-align: center;"><a href="', $context['sort_link'], '">', $txt['arcade_game_list'], '</a></span>
							<span class="icon" style="display: table-cell;float: right;">', $context['sort_arrow'], '</span>
						</span>
					</span>
				</h3>
			</div>
			<table class="bordercolor" style="width: 100%;border: 0px;border-spacing: 1px;border-collapse: separate;">';

	// Is there games?
	if (count($context['arcade']['games']) > 0)
	{
		echo '
				<tr>
					<td class="catbg3">&nbsp;</td>
					<td class="catbg3"><a href="', $context['sort_link'], '">', $txt['arcade_game_name'], '</a></td>
					<td class="catbg3" colspan="2">', $txt['arcade_defdescript'], '</td>
					<td class="catbg3" style="width: 5%; text-align: center;">', $txt['arcade_list_popularity'], '</td>
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
						<a href="' . $game['url']['play'] . '"><img style="width: 70px;height: 70px" src="' . $game['thumbnail'] . '" alt="'.$game['name'].'" title="'.$txt['arcade_play'].' '.$game['name'].'"/></a>' : '', '
					</td>
					<td class="windowbg" style="position: relative;padding-left: 5px;">
						<div class="floatleft">
							<div><a href="', $game['url']['play'], '">', (strlen($game['name']) < 71 ? $game['name'] : substr($game['name'], 0, 67) . '...'), '</a></div>
							<div class="smalltext"><a href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['play'].';pop=1'.'\',' . $game['width'] . ',' . $game['height'] . ', 0)">' . $txt['arcade_popplay'] . '</a></div>
						</div>
					</td>
					<td class="windowbg" style="padding-left: 5px;">
						', !empty($game['description']) ? '<div class="smalltext" style="max-width:87%;word-wrap:break-word;">' . $game['description'] . '</div>' : '<div><span></span></div>', '
					</td>
					<td class="windowbg" style="width: 10%;">';

			if ($game['highscore_support']) // Does this game support highscores?
				echo '
						<div title="' . $txt['arcade_dviewscore'] . '" class="smalltext" style="text-align: right;">
							<a href="' . $game['url']['highscore'] . '">
								<img style="width: 32px;height: 13px;" alt="' . $txt['arcade_dviewscore'] . '" src="' . $settings['default_images_url'] . '/arc_icons/medals.png" />
							</a>
						</div>';

			if (!empty($game['topic_id']) && !empty($modSettings['arcadeEnablePosting']))
				echo '
						<div class="smalltext"><a href="', $scripturl, '?topic=', $game['id_topic'], '">', $txt['arcade_topic_talk'],'</a></div>';

			echo '
						<div style="float: right; text-align: right;" class="smalltext">';

			if ($game['rating2'] > 0)
				echo '
							<div>',
								str_repeat('<img style="padding-top: 2px;padding-bottom: 2px;" src="' . $settings['default_images_url'] . '/arcade_star.gif" alt="*" />' , $game['rating2']),
								str_repeat('<img style="padding-top: 2px;padding-bottom: 2px;" src="' . $settings['default_images_url'] . '/arcade_star2.gif" alt="" />' , 5 - $game['rating2']), '
							</div>';

			// Category
			if ($game['category']['name'])
				echo '
							<a href="', $game['category']['link'], '">', $game['category']['name'], '</a><br />';

			if (allowedTo('arcade_admin'))
				echo '
							<a href="', $game['url']['edit'], '"><img style="padding-top: 2px;" src="' . $settings['default_images_url'] . '/arc_icons/modify.png" border="0" alt="' . $txt['arcade_edit'] . '" title="' . $txt['pdl_edit'] . '&nbsp;' . $game['name'] . '"/></a><br />';

			// Favorite link (if can favorite)
			if (allowedTo('arcade_submit'))
				echo '
							<a href="', $game['url']['favorite'], '" onclick="arcade_favorite(', $game['id'] , '); return false;">
								', !$game['is_favorite'] ?
								'<img style="padding-top: 2px;" id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/favorite.gif" alt="' . $txt['arcade_add_favorites'] . '" title="' . $txt['arcade_add_favorites'] . '"/>' :
								'<img style="padding-top: 2px;" id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/favorite2.gif" alt="' . $txt['arcade_remove_favorite'] .'" title="' . $txt['arcade_remove_favorite'] . '" />', '
							</a>';

			echo '
						</div>
					</td>
					<td class="windowbg2" style="width: 5%; text-align: center;">', $game['plays'], '</td>';

			// Show personal best and champion only if game supports highscores
			if (!empty($game['is_champion']) && $game['highscore_support'])
			{
				echo '
					<td class="windowbg2" style="width: 5%; text-align: center;">';

				if (!empty($game['personal_best']) && $user_info['id'] == $game['champion']['member_id'])
					echo'
						<img class="icon" style="border: 0px;vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/cup_g.gif" alt="cup_g" title="' . $txt['arcade_you_are_first'] . '&nbsp;' . $game['name'] . '"/><br />';
				elseif ($game['personal_best'] > 0 && $user_info['id'] == $game['second_place']['member_id'])
					echo'
						<img class="icon" style="border: 0px;vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/cup_s.gif" alt="cup_s" title="' . $txt['arcade_you_are_second'].'&nbsp;' . $game['name'] . '" /><br />';
				elseif ($game['personal_best'] > 0 && $user_info['id'] == $game['third_place']['member_id'])
					echo'
						<img class="icon" style="border: 0px;vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/cup_b.gif" alt="cup_b" title="' . $txt['arcade_you_are_third'] . '&nbsp;' . $game['name'] . '"/><br />';

				echo ($game['is_personal_best'] ? $game['personal_best'] :  $txt['arcade_no_scores']), '
					</td>
					<td class="windowbg2" style="width: 15%; text-align: center;">
						<table style="width: 100%;">
							<tr>
								<td style="width: 10%; text-align: center;"><img style="border: 0px;vertical-align: middle;" class="icon" src="' . $settings['default_images_url'] . '/arc_icons/cup_g.gif" alt="gold" title="' . $txt['arcade_first'] . '"/></td>
								<td style=" text-align: center;">', $game['champion']['member_link'], ' </td>
								<td style="width: 15%; text-align: center;">', $game['champion']['score'], '</td>
							</tr>';
				if ($game['second_place']['score'] > 0)
					echo'
							<tr>
								<td style="width: 10%; text-align: center;"><img style="border: 0px;vertical-align: middle;" class="icon" src="'. $settings['default_images_url']. '/arc_icons/cup_s.gif" alt="silver" title="' . $txt['arcade_second'].'"/></td>
								<td>', $game['second_place']['member_link'], ' </td>
								<td style="width: 15%; text-align: center;">', $game['second_place']['score'], '</td>
							</tr>';

				if ($game['third_place']['score'] > 0)
					echo'
							<tr>
								<td style="width: 10%; text-align: center;"><img style="border: 0px;vertical-align: middle;" class="icon" src="'. $settings['default_images_url']. '/arc_icons/cup_b.gif" alt="bronze" title="'.$txt['arcade_third'].'"/></td>
								<td>', $game['third_place']['member_link'], ' </td>
								<td style="width: 15%; text-align: center;">', $game['third_place']['score'], '</td>
							</tr>';

				echo'
						</table>
					</td>';
			}
			elseif (!$game['highscore_support'])
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
					<td class="catbg3"><strong>', $txt['arcade_no_games'], '</strong></td>
				</tr>';

	echo '
			</table>			
		</div>
	</div>
	<span class="lowerframe"><span></span></span>
	<div style="vertical-align: middle;padding:8px;font-size: x-small;">', $txt['arcade_dpages'], ': ', $context['page_index'], '   &nbsp;&nbsp;<a href="#top"><strong>', $txt['arcade_dgo_up'], '</strong></a></div>';

	if (!empty($modSettings['arcadeShowIC']))
	{
		echo '
	<div style="padding-top: 20px;"><span></span></div>
	<span class="clear upperframe"><span></span></span>
	<div class="roundframe" style="border-radius: 3px;">
		<div class="innerframe">
			<div class="centertext" style="opacity: 0.7;">
				<h3 class="centertext" style="opacity: 0.7;">
					<img class="icon" id="upshrink_arcade_ic" src="', $settings['images_url'], '/collapse.gif" alt="*" title="', $txt['upshrink_description'], '" style="display: none;" />
					', $txt['arcade_info_center'], '
				</h3>
			</div>
			<div id="upshrinkHeaderArcadeIC"', empty($options['collapse_header_arcade_ic']) ? '' : ' style="display: none;"', '>
				<h4 class="left"><span class="left"></span>
					<span>', $txt['arcade_latest_scores'], '</span>
				</h4>';

		if (!empty($context['arcade']['latest_scores']))
		{
			echo '
				<div class="smalltext" style="padding-left: 15px;word-wrap: break-word;word-break: hyphenate;overflow: hidden;">';

			foreach ($context['arcade']['latest_scores'] as $score)
				echo '
					<span>', sprintf($txt['arcade_latest_score_item'], $scripturl . '?action=arcade;sa=play;game=' . $score['game_id'], $score['name'], $score['score'], $score['memberLink']), '</span><br />
					<span style="padding-left: 5px;padding-bottom: 1px;">',  $score['time'], '</span><br />';

			echo '
				</div>';
		}
		else
			echo '
				<div class="smalltext" style="padding-left:15px;">', $txt['arcade_no_scores'], '</div>';

		echo '
				<h4 class="left clear" style="padding-top:10px;"><span class="left"></span>
					<span>', $txt['arcade_game_highlights'], '</span>
				</h4>
				<div class="smalltext" style="padding-left:15px;word-wrap: break-word;word-break: keep-all;overflow: auto;">';

		if (!empty($context['arcade']['stats']['longest_champion']))
			echo '
					<div>', sprintf($txt['arcade_game_with_longest_champion'], $context['arcade']['stats']['longest_champion']['member_link'], $context['arcade']['stats']['longest_champion']['game_link']), '</div>';

		if (!empty($context['arcade']['stats']['most_played']))
			echo '
					<div style="padding-top: 2px;">', sprintf($txt['arcade_game_most_played'], $context['arcade']['stats']['most_played']['link']), '</div>';

		if (!empty($context['arcade']['stats']['best_player']))
			echo '
					<div style="padding-top: 2px;">', sprintf($txt['arcade_game_best_player'], $context['arcade']['stats']['best_player']['link']), '</div>';

		if (!empty($context['arcade']['stats']['games']))
			echo '
					<div style="padding-top: 2px;">', sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']), '</div>';

		echo '
				</div>';
		if (!empty($modSettings['arcadeShowOnline']))
			echo '
				<h4 class="left" style="padding-top: 10px;"><span class="left"></span>
					<span>' . $txt['arcade_users'] . '</span>
				</h4>
				<div class="smalltext" style="padding-bottom: 3px;">' . $context['arcade_online_link'] . '</div>
				<div class="smalltext" style="padding-left:15px;word-wrap: break-word;word-break: hyphenate;overflow: auto;">' . implode(', ', $context['arcade_viewing']) . '</div>';

		echo '
			</div>
		</div>
	</div>
	<span class="lowerframe"><span></span></span>
	<div style="padding-bottom: 10px;"><span></span></div>';
	}
	elseif (!empty($modSettings['arcadeShowOnline']))
		echo'
	<div style="padding-top: 15px;"><span></span></div>
	<span class="clear upperframe"><span></span></span>
	<div class="roundframe" style="border-radius: 3px;">
		<div class="innerframe" style="border-radius: 5px;">
			<div class="cat_bar">
				<h3 class="catbg" style="vertical-align: middle;">
					<img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['images_url'], '/icons/online.gif" alt="" />
					<span class="mediumtext" style="padding: 0px 6px 0px 0px;vertical-align: middle;">', $txt['arcade_users'], '</span>
				</h3>
			</div>
			<div class="smalltext" style="padding-bottom: 3px;border: 0px;">' . $context['arcade_online_link'] . '</div>
			<div class="smalltext" style="padding-left:15px;word-wrap: break-word;word-break: keep-all;overflow: auto;border: 0px;">' . implode(', ', $context['arcade_viewing']) . '</div>
		</div>
	</div>
	<span class="lowerframe"><span></span></span>
	<div style="padding-bottom: 10px;"><span></span></div>';
	else
		echo '
	<div style="padding-bottom: 10px;"><span></span></div>';
}
?>