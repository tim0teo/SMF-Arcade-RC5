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
	global $scripturl, $txt, $context, $settings, $user_info, $modSettings;

	// games per row
	$row_tally = 4;

	if (empty($modSettings['arcadeEnableDownload']))
		$modSettings['arcadeEnableDownload'] = false;

	$arcade_buttons = array(
		$arcade_buttons['search'] = array(
			'text' => 'arcade_show_all',
			'image' => 'arcade_search.gif',
			'url' => $scripturl . '?action=arcade;category=all',
			'lang' => true
		),
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
		)
	);

	// Header for Game listing
	echo '
		<div style="padding: 15px;"><span></span></div>
		<div class="cat_bar">
			<h3 class="catbg centertext" style="vertical-align: middle;">
				', $context['sort_arrow'], '<span style="clear: right;"><a href="', $context['sort_link'], '">', $txt['arcade_game_list'], '</a></span>
			</h3>
		</div>
		<span class="clear upperframe"><span></span></span>
		<div class="game_table roundframe">
			<div class="innerframe">';
	$tally = 0;
	$code = '';

	/*  loop through games for the list  */
	foreach ($context['arcade']['games'] as $game)
	{
		if (empty($modSettings['arcadeEnableReport']))
			$modSettings['arcadeEnableReport'] = false;

		if(empty($game['report_id']))
			$game['report_id'] = 0;

		strlen($game['name']) >= 23 ? $game['name'] = substr($game['name'], 0, 22) . '...' : '';
		// Show personal best and champion
		$game['personal_best'] ? $your_best = $txt['your_score'] . $game['personal_best'] : $your_best=$txt['your_score'] . $txt['not_applicable'];
		$game['champion']['member_link'] == $txt['arcade_guest'] && empty($game['champion']['score']) ? $game['champion']['member_link'] = '' : '';
		$game['champion']['member_link'] ? $champ = sprintf($txt['champ'], $game['champion']['member_link']) : $champ = sprintf($txt['champ'], $txt['not_applicable']);
		$game['champion']['score'] ? $champ_score = $txt['champ_scoring'] . $game['champion']['score'] : $champ_score = $txt['champ_scoring'] . $txt['not_applicable'];
		(empty($game['description'])) ? $game['description'] = $txt['no_description'] : '';
		$game['description'] = stripslashes($game['description']);
		$fav = '';

		if ($context['arcade']['can_favorite'])
		{
			$fav = '
						<a href="'. $game['url']['favorite']. '" onclick="arcade_favorite('. $game['id'] . '); return false;">';

			if (!$game['is_favorite'])
				$fav .= '
							<img id="favgame' . $game['id'] . '" src="' . $settings['images_url'] . '/favorite.gif" style="width: 16px;height: 16px;border: 0px;" alt="' . $txt['arcade_add_favorites'] . '" />' . '
						</a>';
			else
				$fav .= '
							<img id="favgame' . $game['id'] . '" src="' . $settings['images_url'] . '/favorite2.gif" style="width: 16px;height: 16px;border: 0px;" alt="' . $txt['arcade_remove_favorite'] . '" />
							</a>';
		}

		$rate = '';
		if ($game['rating2'] > 0)
			$rate = str_repeat('<img src="' . $settings['images_url'] . '/arcade_star.gif" alt="*" />' , $game['rating2']) . str_repeat('<img src="' . $settings['images_url'] . '/arcade_star2.gif" alt="" />' , 5 - $game['rating2']);
		else
			$rate = str_repeat('<img src="' . $settings['images_url'] . '/arcade_star2.gif" alt="" />' , 5);

		if (empty($game['pdl_count']))
			$game['pdl_count'] = 0;

		$game['height'] = $game['height'] + 20;

		$pop = '<a href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['play'].';pop=1'.'\',' . $game['width'] . ',' . $game['height'] . ', 0)">' . $txt['pdl_popplay'] . '</a>';

		$hiscr = '
							<a href="' . $game['url']['highscore'] . ';">' . $txt['arcade_viewscore'] . '</a>';
		$viewdl = '
							<b>&bull;</b>&nbsp;'.$your_best.'<br /><b>&bull;</b>&nbsp;'. $txt['num_plays']. '&#058;&nbsp;' . $game['plays'] . '<br />';
		$viewreport = '
							<b>&bull;</b>&nbsp;'. $pop;

		if  (($modSettings['arcadeEnableReport'] == true) && (AllowedTo('arcade_report') == true))
			$viewreport .= '<br /><b>&bull;</b>&nbsp;<a href="'. $scripturl . '?action=arcade;sa=report;game=' . $game['id'] . '">' . $txt['pdl_report'] . '</a>';

		if ((AllowedTo('arcade_admin') == true) && ((int)$game['report_id'] > 0))
		{
			$viewreport .= '<br /><b>&bull;</b>&nbsp;<a href="' . $scripturl . '?action=admin;area=arcade;sa=pdl_reports;game=' . $game['id'] . '">' . $txt['show_pdl_report'] . '</a>';
			$gamename = '<span style="font-style: italic;"><a class="highlight" href="' . $game['url']['play'] . '" title="' . $txt['alt_play'] . '">' . $game['name'] . ' </a></span>';
		}
		else
			$gamename = '<a class="highlight" href="' . $game['url']['play'] . '" title="' . $txt['alt_play'] . '">' . $game['name'] . ' </a>';

		if ($modSettings['arcadeEnableDownload'] == true)
			$viewdl .= '
							<b>&bull;</b>&nbsp;'. $txt['pdl_counter']. '&nbsp;' .$game['pdl_count'].'<br />
							<b>&bull;</b>&nbsp;<a href="' . $scripturl.'?action=arcade;sa=download;game=' . $game['id'] . '">' . $txt['arcade_download_game'] . '</a><br />';

		if ($context['arcade']['can_admin_arcade'])
			$viewdl .= '<b>&bull;</b>&nbsp;<a href="' . $scripturl . '?action=admin;area=managegames;sa=edit;game=' . $game['id'] . '">' . $txt['pdl_edit'] . '</a><br />';

		// four cells wide
		$tally++;
		$remainder = intval($tally % $row_tally);

		switch ($remainder)
		{
			case 0:
				$open = '<div class="windowbg smalltext" style="display: table-cell;padding: 5px;width: 25%;">';
				$close = '</div></div>';
				break;
			case 1:
				$open = '<div style="display: table-row;width: 100%;"><div class="windowbg smalltext" style="display: table-cell;padding: 5px;width: 25%;">';
				$close = '</div>';
				break;
			default:
				$open = '<div class="windowbg smalltext" style="display: table-cell;padding: 5px;width: 25%;">';
				$close = '</div>';
		}

		$code .= $open . '
							<div class="titlebg" style="height: 18px;padding:2px 5px 2px 5px;margin:2px 5px 2px 5px;border-bottom:1px solid #808080;">
								<div class="button_strip_random" style="float: left;padding-top: 1px;" >' . $gamename . '</div>
								<div style="float: right; padding-top: 1px" >' . $fav . ' </div>
							</div>
							<div style="float: left; text-align: left; margin: 6px 3px 0px 5px;">
								<a href="' . $game['url']['play'] . '">
									<img class="imgBorder" style="width: 40px;height: 40px;" src="' . $game['thumbnail'] . '" alt="' . $txt['alt_play'] . '" title="' . $txt['alt_play'] . '"/>
								</a><br />
							</div>
							<div style="height:55px; margin: 4px 0px 5px 0px;padding-left: 3px; overflow: auto">' . $game['description'] . '</div>
							<div class="windowbg3" style="height: 1px"></div>
							<div class="smalltext" style="padding:4px 0px 4px 10px;line-height: 13px;">
								<b>&bull;</b>  ' . $champ . '<br />
								<b>&bull;</b> ' . $champ_score . '<br />
								' . $viewdl . '
								<b>&bull;</b> ' . $hiscr . '<br />
								' . $viewreport . ' <br />
								<b>&bull;</b> ' . $rate . '
							</div>
							' . $close;
	}


	if (!empty($remainder))
		$code .= str_repeat('<div class="windowbg" style="display: table-cell;padding: 5px;width: 25%;"></div>', $row_tally-$remainder);
	else
		$code .= '
							<div>';

	echo '
							<div style="display: table;width: 100%;">', $code, '</div>
							<div style="width: 100%;display: table-row;">
								<div style="display: table-cell;width: 25%;"><span style="display: none;"></span></div>
							</div>
						</div>
					</div>
				</div>
				<span class="lowerframe"><span></span></span>
				<div id="arcadebuttons_bottom" class="modbuttons clearfix marginbottom">', template_button_strip($arcade_buttons, 'right'), '<br /><br /></div>
				<div class="modbuttons clearfix marginbottom">
					<div class="floatleft middletext">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a name="bot" href="#top"><strong>' . $txt['go_up'] . '</strong></a>' : '', '</div>
				</div>
				<div style="padding-top: 40px;"><span></span></div>';

	if (!empty($modSettings['arcadeShowIC']))
	{
		echo '
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
					<h4 class="left">
						<span class="left"></span>
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
					<h4 class="left clear" style="padding-top:10px;">
						<span class="left"></span>
						<span>', $txt['arcade_game_highlights'], '</span>
					</h4>
					<div class="smalltext" style="padding-left:15px;word-wrap: break-word;word-break: hyphenate;overflow: auto;">';

		if ($context['arcade']['stats']['longest_champion'] !== false)
			echo '
						<span>', sprintf($txt['arcade_game_with_longest_champion'], $context['arcade']['stats']['longest_champion']['member_link'], $context['arcade']['stats']['longest_champion']['game_link']), '</span><br />';

		if ($context['arcade']['stats']['most_played'] !== false)
			echo '
						<span style="padding-top: 2px;">', sprintf($txt['arcade_game_most_played'], $context['arcade']['stats']['most_played']['link']), '</span><br />';

		if ($context['arcade']['stats']['best_player'] !== false)
			echo '
						<span style="padding-top: 2px;">', sprintf($txt['arcade_game_best_player'], $context['arcade']['stats']['best_player']['link']), '</span><br />';

		if ($context['arcade']['stats']['games'] != 0)
			echo '
						<span style="padding-top: 2px;">', sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']), '</span>';

		echo '
					</div>
				</div>';
		if (!empty($modSettings['arcadeShowOnline']))
			echo '
				<h4 class="smalltext" style="padding-top: 10px;text-align: left;"><span class="left"></span>
					<span>' . $txt['arcade_users'] . '</span>
				</h4>
				<div class="smalltext" style="padding-bottom: 3px;">' . $context['arcade_online_link'] . '</div>
				<div class="smalltext" style="padding-left:15px;word-wrap: break-word;word-break: keep-all;overflow: auto;">' . implode(', ', $context['arcade_viewing']) . '</div>';

		echo '
			</div>
		</div>
		<span class="lowerframe"><span></span></span>
		<div style="padding-bottom: 10px;"><span></span></div>';
	}
	elseif (!empty($modSettings['arcadeShowOnline']))
		echo'
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