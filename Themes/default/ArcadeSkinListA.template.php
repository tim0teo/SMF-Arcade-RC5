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
		<div id="arcadebuttons_top" class="modbuttons clearfix margintop" style="top:-10px;position:relative;">', template_button_strip($arcade_buttons, 'right'), '<br /><br /></div>
		<div class="game_table">
			<table style="border-collapse: collapse;width: 100%;" class="table_grid">
				<thead>
					<tr>';

	// Is there games?
	if (!empty($context['arcade']['games']))
		echo '
						<th ', ($context['curved'] ? 'scope="col" class="smalltext first_th"' : 'class="catbg"'), ' style="padding: 5px;width: 25%;"></th>
						<th colspan="2" ', ($context['curved'] ?  'scope="col" class="smalltext"' : 'class="catbg"'),' style="padding: 5px;width: 50%;font-family: georgia; text-align: center; font-style: oblique;font-size: 0.8em;">', $txt['arcade_game_list'], '</th>
						<th ', ($context['curved'] ? 'scope="col" class="smalltext  last_th"' : 'class="catbg"'),' style="padding: 5px;width: 25%;"></th>';
	elseif ($context['curved'])
		echo '
						<td class="catbg" style="padding: 5px;border:0px;height: 23px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 0 -160px;"></td>
						<td colspan="2" class="smalltext catbg" style="padding: 5px;border:0px;text-align:center;overflow: hidden;height: 23px;line-height: 23px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat -10px -160px;font-family: georgia; font-style: oblique;font-size: 0.8em;font-weight: bold;">', $txt['arcade_no_games'], '</td>
						<td class="catbg" style="padding: 5px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 100% -160px;height: 23px;line-height: 23px;" ></td>';
	else
		echo '			<th class="catbg" style="padding: 5px;width: 25%;"></th>
						<th colspan="2" class="catbg" style="width: 50%;padding: 5px;font-family: georgia; text-align: center; font-style: oblique;">', $txt['arcade_no_games'], '</th>
						<th class="catbg" style="padding: 5px;width: 25%;"></th>';
	$lines = 0;
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

		$lines++;

		if ($lines == 5)
			$lines = 1;

		$lines == 1 ? $open = '<tr><td class="windowbg smalltext" style="padding: 5px;width: 25%;">' : $open = '<td class="windowbg smalltext" style="padding: 5px;width: 25%;">';
		$lines == 4 ? $close = '</td></tr>' : $close = '</td>';

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

	if ($lines > 0 && $lines < 4)
	{
		$loop = 4-$lines;
		for ($j=1; $j <= $loop; $j++)
			$code .= '<td class="windowbg" style="padding: 5px;width: 25%;">&nbsp;</td>';
	}

	echo $code;
	if($context['curved'])
		echo '
			<tr>
				<td style="padding: 5px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 0 -173px;height:20px;"></td>
				<td colspan="2" style="padding: 5px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat -10px -173px;height:20px;"></td>
				<td style="padding: 5px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 100% -173px;height:20px;"></td>
			</tr>';
	else
		echo '
			<tr>
				<td colspan="5" class="catbg2" style="padding: 5px;">&nbsp;</td>
			</tr>';
	echo '
		</table>';

	echo '
		<div id="arcadebuttons_bottom" class="modbuttons clearfix marginbottom">
		', template_button_strip($arcade_buttons, 'right'), '<br /><br />
		</div>
		<div class="modbuttons clearfix marginbottom">
			<div class="floatleft middletext">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a name="bot" href="#top"><strong>' . $txt['go_up'] . '</strong></a>' : '', '</div><br />
		<br /><br /></div>';

	if (!empty($modSettings['arcadeShowIC']))
	{
		echo '
		<span class="clear upperframe"><span></span></span>
		<div class="roundframe"><div class="innerframe">
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
				<div class="smalltext" style="padding-left:15px;word-wrap: break-word;word-break: hyphenate;overflow: auto;">
		';

		if ($context['arcade']['stats']['longest_champion'] !== false)
			echo '<span>', sprintf($txt['arcade_game_with_longest_champion'], $context['arcade']['stats']['longest_champion']['member_link'], $context['arcade']['stats']['longest_champion']['game_link']), '</span><br />';

		if ($context['arcade']['stats']['most_played'] !== false)
			echo '<span style="text-indent: 1.5em;padding-top: 2px;">', sprintf($txt['arcade_game_most_played'], $context['arcade']['stats']['most_played']['link']), '</span><br />';

		if ($context['arcade']['stats']['best_player'] !== false)
			echo '<span style="text-indent: 1.5em;padding-top: 2px;">', sprintf($txt['arcade_game_best_player'], $context['arcade']['stats']['best_player']['link']), '</span><br />';

		if ($context['arcade']['stats']['games'] != 0)
			echo '<span style="text-indent: 1.5em;padding-top: 2px;">', sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']), '</span>';

		echo '
				</div>
				<h4 class="left" style="padding-top: 10px;"><span class="left"></span>
					<span>', $txt['arcade_users'], '</span>
				</h4>
				<div class="smalltext" style="padding-left:15px;word-wrap: break-word;word-break: hyphenate;overflow: auto;">
					', implode(', ', $context['arcade_viewing']), '
				</div>
			</div>
		</div></div>
		<span class="lowerframe"><span></span></span>';
	}
}
?>