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
	global $scripturl, $txt, $context, $settings, $user_info, $modSettings, $boardurl;

	$arcade_buttons = array(
		'random' => array(
			'text' => 'arcade_random_game',
			'image' => 'arcade_random.gif', // Theres no image for this included (yet)
			'url' => $scripturl . '?action=arcade;sa=play;random',
			'lang' => true
		),
		'favorites' => array(
			'text' => 'arcade_favorites_only',
			'image' => 'arcade_favorites.gif',
			'url' => $scripturl . '?action=arcade;favorites',
			'lang' => true
		),
	);

	if (isset($context['arcade']['search']) && $context['arcade']['search'])
		$arcade_buttons['search'] = array(
			'text' => 'arcade_show_all',
			'image' => 'arcade_search.gif',
			'url' => $scripturl . '?action=arcade'
		);


	// Header for Game listing
	echo '
		<div style="width: 100%;position: relative;clear: left;">
			<div class="pagesection" style="display: inline;">
				<div style="display: inline;padding-top: 15px;float: left;">', ($context['arcade_smf_version'] == 'v2.1' ? '' : $txt['arcade_number_pages'] . '&nbsp;'), $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#bot"><b>' . $txt['go_down'] . '</b></a>' : '', '</div>
				<div style="display: inline;clear: right;float: right;">', template_button_strip($arcade_buttons, 'right'), '</div>
			</div>
		</div>
		<div class="game_table">
			<table style="border: 0px;border-collapse: collapse;width: 100%;" class="table_grid">
				<thead>
					<tr>';

	// Is there games?
	if (!empty($context['arcade']['games']))
	{
		echo '

						<th style="border-radius: 4px 0px 0px 4px;vertical-align: middle;" class="first_th windowbg2" scope="col">', $context['sort_arrow'], '</th>
						<th class="windowbg2" scope="col" colspan="2"><a href="', $scripturl, '?action=arcade;sa=list;sortby=', ($context['arcade']['games'][0]['sort_by'] == 'a2z' ? 'z2a;#arctoplist' : 'a2z;#arctoplist'), '">', $txt['arcade_game_name'], '</a></th>
						<th class="windowbg2" scope="col"><a href="' . $scripturl . '?action=arcade;sa=list;sortby=myscore' . ($context['arcade']['games'][0]['sort_by'] == 'myscore' ? $context['changedir'] : ';dir=desc;#arctoplist') . '">' . $txt['arcade_personal_best'] . '</a></th>
						<th style="border-radius: 0px 4px 4px 0px;" class="last_th windowbg2 centertext" scope="col"><a href="', $scripturl, '?action=arcade;sa=list;sortby=champs', ($context['arcade']['games'][0]['sort_by'] == 'champs' ? $context['changedir'] : ';dir=desc;#arctoplist'), '">', $txt['arcade_champion'], '</a></th>';
	}
	else
	{
		echo '
						<th style="border-radius: 4px 0px 0px 4px;" scope="col" class="titlebg first_th" style="width: 8%;">&nbsp;</th>
						<th class="titlebg smalltext" colspan="3"><strong>', $txt['arcade_no_games'], '</strong></th>
						<th style="border-radius: 0px 4px 4px 0px;" scope="col" class="titlebg last_th" style="width: 8%;">&nbsp;</th>';
	}

	echo '
					</tr>
				</thead>
				<tbody>';

	foreach ($context['arcade']['games'] as $game)
	{
		list($report, $show_report) = array(false, false);
		$modSettings['arcadeEnableDownload'] = !empty($modSettings['arcadeEnableDownload']) ? $modSettings['arcadeEnableDownload'] : false;
		$modSettings['arcadeEnableReport'] = !empty($modSettings['arcadeEnableReport']) ? $modSettings['arcadeEnableReport'] : false;
		$modSettings['arcadeSkin'] = !empty($modSettings['arcadeSkin']) ? (int)$modSettings['arcadeSkin'] : 0;
		$game['report_id'] = !empty($game['report_id']) ? (int)$game['report_id'] : 0;
		$game['pdl_count'] = !empty($game['pdl_count']) ? (int)$game['pdl_count'] : 0;
		$game3 = !empty($game['id']) ? $game['id'] : '';
	    $game_buttons = array();
		$dlgame = array('url' => $scripturl . '?action=arcade;sa=download;game=' . $game3, 'text' => 'pdl_button1', 'image' => 'arcade_download.gif', 'lang' => true);
		$dl_count = $txt['pdl_counter']. $game['pdl_count'];
		$report = array('url' => $scripturl . '?action=arcade;sa=report;game=' . $game3, 'text' => 'pdl_report', 'image' => 'arcade_report.gif', 'lang' => true);
		$edit_game2 = array('url' => $scripturl . '?action=admin;area=managegames;sa=edit;game=' . $game3, 'text' => 'pdl_edit', 'image' => 'arcade_edit.gif', 'lang' => true);
		$popup = array('url' => 'javascript:void(0)', 'text' => 'pdl_popplay', 'image' => 'arcade_popup.gif', 'lang' => true, 'custom' => 'onclick="myGamePopupArcade(\'' . $game['url']['play'] . ';pop=1' . '\',' . $game['width'] . ',' . $game['height'] . ',0)"');
		if ((AllowedTo('arcade_admin') == true) && ((int)$game['report_id'] > 0))
		{
			$show_report = array('url' => $scripturl . '?action=admin;area=arcade;sa=pdl_reports;game=' . $game3, 'text' => 'show_pdl_report', 'image' => 'arcade_show_report.gif', 'lang' => true);
			$gamename = '<span style="font-style: italic;"><a href="' . $game['url']['play'] . '">' . $game['name'] . '</a></span>';
		}
		else
			$gamename = '<span><a href="' . $game['url']['play'] . '">' . $game['name'] . '</a></span>';

		if ($game['highscore_support'])
			$highscore = array('url' => $game['url']['highscore'], 'text' => 'arcade_viewscore', 'image' => 'arcade_highscore.gif', 'lang' => true);

		echo '
					<tr>
						<td class="windowbg centertext">', $game['thumbnail'] !== '' ? '
							<a href="' . $game['url']['play'] . '"><img class="board_icon" src="' . $game['thumbnail'] . '" alt="" /></a>' : '', '
						</td>
						<td class="windowbg2">', $gamename, !empty($game['description']) ? '<div><span>' . wordwrap($game['description'], 140) . '</span></div>' : '', '
							<div class="smalltext game_buttons">
								<div class="game_list_left" style="display: inline;">';

		if (!empty($modSettings['arcadeEnableDownload']))
			$game_buttons['download'] = $dlgame;

		if ($context['arcade']['can_admin_arcade'])
			$game_buttons['edit'] = $edit_game2;

		if  ((!empty($modSettings['arcadeEnableReport'])) && (allowedTo('arcade_report') == true))
			$game_buttons['report'] = $report;

		// Does this game support highscores?
		if ($game['highscore_support'])
			$game_buttons['highscore'] = $highscore;

		$game_buttons['popup'] = $popup;

		if ((AllowedTo('arcade_admin') == true) && ((int)$game['report_id'] > 0))
			$game_buttons['show_report'] = $show_report;

		if (!empty($game_buttons))
			echo template_button_strip($game_buttons, 'left', array('id' => 'game_buttons_' . $game['id']));

		echo '
								</div>
							</div>
						</td>
						<td class="windowbg" style="text-align: right;">';

		/* Favorite link (if can favorite) */
		if ($context['arcade']['can_favorite'])
			echo '
							<span>
								<a href="', $game['url']['favorite'], '" onclick="arcade_favorite(', $game['id'] , '); return false;">
			', !$game['is_favorite'] ? '
									<img id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/favorite.gif" alt="' . $txt['arcade_add_favorites'] . '" />' : '
									<img id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/favorite2.gif" alt="' . $txt['arcade_remove_favorite'] .'" />', '
								</a>
							</span>
							<div><span style="display: none;"></span></div>';

		// Rating
		if ($game['rating2'] > 0)
			echo str_repeat('<img src="' . $settings['default_images_url'] . '/arcade_star.gif" alt="*" />' , $game['rating2']), str_repeat('<img src="' . $settings['default_images_url'] . '/arcade_star2.gif" alt="" />' , 5 - $game['rating2']), '<div><span style="display: none;">&nbsp;</span></div>';

		if ($modSettings['arcadeEnableDownload'])
			echo $dl_count, '<span style="display: block;"><span style="display: none;">&nbsp;</span></span>';

		// Category
		if (!empty($game['category']['name']))
			echo '
							<a href="', $game['category']['link'], '">', $game['category']['name'], '</a><span style="display: block;"><span style="display: none;">&nbsp;</span></span>';

		echo '
						</td>';

		// Show personal best and champion only if game supports highscores
		if ($game['is_champion'] && !$user_info['is_guest'])
			echo '
						<td class="windowbg2 centertext">
							', $game['is_personal_best'] ? $game['personal_best'] :  $txt['arcade_no_scores'], '
						</td>';
		else
			echo '
						<td class="windowbg2 centertext">
							<span style="display: none;">&nbsp;</span>
						</td>';

		if ($game['is_champion'])
			echo '
						<td class="windowbg centertext">
							', $game['champion']['member_link'], '<div>', $game['champion']['score'], '</div>
						</td>';
		elseif (!$game['highscore_support'])
			echo '
						<td class="windowbg centertext">', $txt['arcade_no_highscore'], '</td>';
		else
			echo '
						<td class="windowbg centertext">', $txt['arcade_no_scores'], '</td>';

		echo '
					</tr>';
		}

	echo '
				</tbody>
			</table>
		</div>
		<div class="pagesection" style="display: inline;width: 100%;">
			<div style="text-align:left;display: inline;">', ($context['arcade_smf_version'] == 'v2.1' ? '' : $txt['arcade_number_pages'] . '&nbsp;'), $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '', '</div>
		</div>
		<div style="clear: both;padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>';

	if (!empty($modSettings['arcadeShowIC']))
	{
		echo '
		<div class="cat_bar centertext">
			<h3 class="catbg centertext">
				', $txt['arcade_info_center'], '
			</h3>
		</div>
		', $context['arcade_smf_version'] == 'v2.1' ? '
		<div class="up_contain windowbg">' :
		'<span class="clear upperframe"><span>&nbsp;</span></span>
		<div class="roundframe">', '
			<div class="', ($context['arcade_smf_version'] == 'v2.1' ? 'inline' : 'innerframe'), '">
				<div id="upshrinkHeaderArcadeIC">
					<h4 class="left">
						<span>', $txt['arcade_latest_scores'], '</span>
					</h4>';

		if (!empty($context['arcade']['latest_scores']))
		{
			echo '
					<div class="smalltext" style="padding-left: 15px;word-wrap: break-word;keep-all: hyphenate;overflow: hidden;">';

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
					<h4 class="left clear" style="padding-top:10px;">
						<span>', $txt['arcade_game_highlights'], '</span>
					</h4>
					<div class="smalltext" style="padding-left:15px;word-wrap: break-word;keep-all: hyphenate;overflow: auto;">';

		if ($context['arcade']['stats']['longest_champion'] !== false)
			echo '
						<div>', sprintf($txt['arcade_game_with_longest_champion'], $context['arcade']['stats']['longest_champion']['member_link'], $context['arcade']['stats']['longest_champion']['game_link']), '</div>';

		if ($context['arcade']['stats']['most_played'] !== false)
			echo '
						<div style="padding-top: 2px;">', sprintf($txt['arcade_game_most_played'], $context['arcade']['stats']['most_played']['link']), '</div>';

		if ($context['arcade']['stats']['best_player'] !== false)
			echo '
						<div style="padding-top: 2px;">', sprintf($txt['arcade_game_best_player'], $context['arcade']['stats']['best_player']['link']), '</div>';

		if ($context['arcade']['stats']['games'] != 0)
			echo '
						<div style="padding-top: 2px;">', sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']), '</div>';

		echo '
					</div>';
		if (!empty($modSettings['arcadeShowOnline']))
			echo '
					<div style="padding-top: 10px;"><span style="display: none;">&nbsp;</span></div>
					<div class="title_barIC smalltext">
						<h4 class="titlebg left">
							<span class="icon" style="vertical-align: middle;"><img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['images_url'], '/icons/online.gif" alt="" /></span>
							<span>' . $txt['arcade_users'] . '</span>
						</h4>
					</div>
					<div class="smalltext" style="padding-bottom: 3px;">' . $context['arcade_online_link'] . '</div>
					<div class="smalltext" style="padding-left:15px;word-wrap: break-word;keep-all: keep-all;overflow: auto;">' . implode(', ', $context['arcade_viewing']) . '</div>';

		echo '
				</div>
			</div>
		</div>
		<span class="lowerframe"><span>&nbsp;</span></span>
		<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
	}
	elseif (!empty($modSettings['arcadeShowOnline']))
		echo'
		<div style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>
		<div class="cat_bar">
			<h3 class="catbg" style="vertical-align: middle;">
				<img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['images_url'], '/icons/online.gif" alt="" />
				<span class="mediumtext" style="padding: 0px 6px 0px 0px;vertical-align: middle;">', $txt['arcade_users'], '</span>
			</h3>
		</div>
		', $context['arcade_smf_version'] == 'v2.1' ? '
		<div class="up_contain windowbg">' :
		'<span class="clear upperframe"><span>&nbsp;</span></span>
		<div class="roundframe">', '
			<div class="innerframe" style="border-radius: 5px;">
				<div class="smalltext" style="padding-bottom: 3px;border: 0px;">' . $context['arcade_online_link'] . '</div>
				<div class="smalltext" style="padding-left:15px;word-wrap: break-word;keep-all: keep-all;overflow: auto;border: 0px;">' . implode(', ', $context['arcade_viewing']) . '</div>
			</div>
		</div>
		<span class="lowerframe"><span>&nbsp;</span></span>
		<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
	else
		echo '
		<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
}
?>