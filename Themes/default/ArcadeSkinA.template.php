<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_above()
{
	global $scripturl, $txt, $context, $settings, $modSettings, $user_info;

	$selected = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? ' selected="selected"' : ' selected';
	if ( $_REQUEST['sa'] == 'list' || $_REQUEST['sa'] == 'search')
	{
		$categories = ArcadeCats($_SESSION['current_cat']);

		// SMF 2.0 / 2.1 css differs for inner title bg
		$divbg = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'titlebg' : 'cat_bar';
		$spanbg = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '' : ' class="catbg"';

		echo '
	<div class="clear cat_bar" style="position: relative;', $context['arcade_smf_version'] == 'v2.1' ? 'bottom: -2px;' : '', '">
		<h3 class="catbg centertext" style="vertical-align: middle;">
			<span style="clear: right;">', $txt['arcade_title'], '</span>
		</h3>
	</div>
	', $context['arcade_smf_version'] == 'v2.1' ? '
	<div class="up_contain windowbg">' :
	'<span class="clear upperframe" style="clear: both;position: relative;bottom: -2px;"><span>&nbsp;</span></span>
	<div class="roundframe">', '
		<div class="innerframe">
			<table style="border-collapse: collapse;width: 100%;" class="tborder table_grid">
				<tr>
					<td class="windowbg smalltext" style="vertical-align: top;width: 24%;padding: 5px;font-size:0.85em;">
						<div class="' . $divbg . ' centertext" style="font-size:1.3em;border-radius: 3px;overflow: hidden;">
							<span'. $spanbg . '><strong>', $txt['latest_games'] ,'</strong></span>
						</div>
						',  ArcadeNewestGames($modSettings['skin_latest_games']), '
						<div class="' . $divbg . ' centertext" style="margin-bottom:10px;font-size:1.3em;border-radius: 3px;overflow: hidden;"><span'. $spanbg . '><strong>', $txt['arcade_game_search'] ,'</strong></span></div>
						<div class="centertext smalltext" style="margin-bottom:15px;font-size:1.0em;">
							<form name="search" action="', $scripturl, '?action=arcade;sa=search" method="post" onsubmit="return empty();">
								<input id="gamesearch" type="text" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '" />
								<input class="button_submit smalltext" type="submit" value="', $txt['arcade_search_go'] , '"  name="submit1" />
								<div id="suggest_gamesearch" class="game_suggest"></div>
								<script type="text/javascript"><!-- // --><![CDATA[
									var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
								// ]]></script>
							</form>
						</div>
						<div class="' . $divbg . ' centertext" style="margin-bottom:3px;font-size:1.3em;border-radius: 3px;overflow: hidden;"><span'. $spanbg . '><strong>', $txt['arcade_game_sort'],'</strong></span></div>
						<div class="centertext smalltext" style="padding:5px;margin:10px;font-size:1.0em;">
							<form action="', $scripturl, '?action=arcade;sa=list" method="post">
								<select name="sortby" onchange="submit();">
									<option value="reset">', $txt['arcade_sort_by'], '</option>
									<option value="age"' . ($_SESSION['arcade_sortby'] === 'age' ? $selected : '') . '>', $txt['arcade_age'], '</option>
									<option value="a2z"' . ($_SESSION['arcade_sortby'] === 'a2z' ? $selected : '') . '>', $txt['arcade_a2z'], '</option>
									<option value="z2a"' . ($_SESSION['arcade_sortby'] === 'z2a' ? $selected : '') . '>', $txt['arcade_z2a'], '</option>
									<option value="plays"' . ($_SESSION['arcade_sortby'] === 'plays' ? $selected : '') . '>', $txt['arcade_plays'], '</option>
									<option value="plays_reverse"' . ($_SESSION['arcade_sortby'] === 'plays_reverse' ? $selected : '') . '>', $txt['arcade_playsl'], '</option>
									<option value="champion"' . ($_SESSION['arcade_sortby'] === 'champion' ? $selected : '') . '>', $txt['arcade_champion'], '</option>
									<option value="champs"' . ($_SESSION['arcade_sortby'] === 'champs' ? $selected : '') . '>', $txt['arcade_latest_champions'], '</option>
									<option value="rating"' . ($_SESSION['arcade_sortby'] === 'rating' ? $selected : '') . '>', $txt['arcade_rating'], '</option>', (!$user_info['is_guest'] ? '
									<option value="favorites"' . ($_SESSION['arcade_sortby'] === 'favorites' ? $selected : '') . '>' . $txt['arcade_favs'] . '</option>' : ''), '
								</select>
							</form>
						</div>
						<div class="' . $divbg . ' centertext" style="margin-bottom:3px;font-size:1.3em;border-radius: 3px;overflow: hidden;"><span'. $spanbg . '><strong>', $txt['arcade_random_game'],'</strong></span></div><br />
						<div style="margin-bottom:3px;font-size:0.8em;">', ArcadeRandomGames(1), '</div>
					</td>
					<td class="windowbg smalltext" style="padding: 5px;vertical-align: top;font-size:0.85em;">
						<div class="' . $divbg . ' centertext" style="margin-bottom:3px;font-size:1.3em;border-radius: 3px;overflow: hidden;"><span'. $spanbg . '><strong>', $txt['latest_champs'],'</strong></span></div>
						<div class="windowbg2" style="margin:5px 2px 5px 2px;font-size:1.0em;text-align:left;">', ArcadeNewChamps($modSettings['skin_latest_champs']), '</div>
						<div class="' . $divbg . ' centertext" style="margin-bottom:3px;font-size:1.3em;border-radius: 3px;overflow: hidden;">
							<span'. $spanbg . '><strong>
								<img src="', $settings['default_images_url'], '/gold.gif" alt="" />
								', ($_SESSION['current_cat'] == 'all' ? $txt['arcade_champs'] : sprintf($txt['cat_champs'], $context['cat_name'])), '
								<img src="', $settings['default_images_url'], '/gold.gif" alt="" />
							</strong></span>
						</div>
						<table style="border: 0px;width: 100%;border-spacing: 2px;border-collapse: separate;">
							<tr>';

		$bp = ArcadeChamps(3, $_SESSION['current_cat'] == 'all' ? 'wins' : 'cats');
		$score_poss = 0;
		if(is_array($bp))
		{
			foreach ($bp as $out)
			{
				$score_poss++;
				echo '
								<td class="windowbg2 centertext" style="width: 33%;border:0px;font-size:1.0em;">
									<img src="', $settings['default_images_url'], '/', $score_poss, '.gif" style="margin-bottom: 3px" alt="" /><br />
									', $out['avatar'], '<br /><strong>', $out['link'], '</strong><br />
									', $txt['win'], ' ', $out['champions'], '
								</td>';
			}
		}
		else
			echo '
								<td class="windowbg2 smalltext centertext" style="border:0px;font-size:1.0em;">
									', $txt['no_new_champs'], '
								</td>';

		echo '
							</tr>
						</table>
						<div class="' . $divbg . ' centertext" style="margin-bottom:3px;font-size:1.3em;border-radius: 3px;overflow: hidden;"><span'. $spanbg . '><strong>', $txt['latest_scores'] ,'</strong></span></div>
						<div class="windowbg2" style="border:0px;margin:5px 2px 1px 2px;font-size:1.0em;text-align:left;">', ArcadeLatest($modSettings['skin_latest_scores']), '</div>
					</td>
					<td class="windowbg smalltext" style="width: 24%;vertical-align: top;font-size:0.85em;">
						<div class="' . $divbg . ' centertext" style="margin-bottom:3px;font-size:1.3em;border-radius: 3px;overflow: hidden;"><span'. $spanbg . '><strong>', $txt['most_played'], '</strong></span></div>
						', ArcadePopular($modSettings['skin_most_popular']), '
						<div class="' . $divbg . ' centertext" style="margin-bottom:4px;font-size:1.3em;border-radius: 3px;overflow: hidden;"><span'. $spanbg . '><strong>', $txt['arcade_daily'], '</strong></span></div>';

		$game = getGameOfDay();
		if (!empty($game['url']['play']))
		{
			echo '
						<div class="smalltext" style="padding: 0px 5px 0px 5px">
							<div class="titlebg centertext" style="margin:4px 0px 5px 0px;border-bottom:1px solid #808080;font-size:1.1em;">', (strlen($game['name']) >= 23 ? substr($game['name'],0,22) . '...' : $game['name']), '</div><br />
							<div style="float: left; margin: 0px 5px 0px 0px;height:55px;">
								<a href="', $game['url']['play'], '">
									<img style="width: 40px;height: 40px;" class="imgBorder" src="', $game['thumbnail'], '" alt="' . $txt['alt_play'] . '" title="' . $txt['alt_play'] . '"/>
								</a>
							</div>
						</div>';

			if($game['description'])
				echo '
						<div style="height:55px; overflow: auto;font-size:0.95em">&nbsp;&nbsp;', $game['description'], '</div>';
			else
				echo '
						<div style="height:55px; overflow: auto">&nbsp;&nbsp;', $txt['no_description'], '</div>';
		}

		echo '
						<div class="titlebg" style="margin:4px 0px 5px 0px;border-bottom:1px solid #808080; text-align:center;font-size:1.1em;">', $txt['todays_scores'], '</div>
						<div style="margin: 5px 0px 0px 5px">', ArcadeDailyChallenge($game);

		if ($context['CH_error'])
			echo '
							<div class="smalltext centertext">', $txt['arcade_daily_none'], '</div>';

		echo '
						</div>';

		if (!empty($modSettings['arcadeDropCat']))
			echo '
						<div class="' . $divbg . ' centertext" style="margin-bottom:3px;font-size:1.3em;border-radius: 3px;overflow: hidden;"><span'. $spanbg . '><strong>', $txt['game_categories'], '</strong></span></div>
						<div class="smalltext centertext" style="margin: 5px 0px 0px 5px;font-size:1.0em;"><br />', ArcadeCategoryDropdown(), '</div>';

		echo '
					</td>
				</tr>
			</table>';

		if (empty($modSettings['arcadeDropCat']))
		{
			echo '
			<div class="title_bar">
				<h4 class="titlebg centertext" style="vertical-align: middle;">
					<span style="clear: right;"><a title="', $txt['arcade_defcat'], '" href="', $scripturl, '?action=arcade;category=0">', $txt['arcade_game_cats'], '</a></span>
				</h4>
			</div>', $categories;
		}

		echo '
		</div>
	</div>', ($context['arcade_smf_version'] !== 'v2.1' ? '
	<span class="lowerframe"><span>&nbsp;</span></span>' : ''), '
	<div style="width:100%;display: inline;" class="smalltext">
		<div style="display: inline;">', template_button_strip($context['arcade_tabs'], 'left', array()), '</div>';

		if ($context['arcade']['stats']['games'] != 0)
			echo '
		<div class="smalltext" style="clear: right;padding:8px 7px 0px 0px;float: right;display: inline;">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</div>';

		echo '
	</div>', ($context['arcade_smf_version'] == 'v2.1' ? '
	<span class="lowerframe"><span>&nbsp;</span></span>' : ''), '
	<div><span style="display: none;">&nbsp;</span></div>
	<div style="height: 10px;clear: both;">
		<span style="display: none;">&nbsp;</span>
	</div>';
	}
}

function template_arcade_below()
{
	global $arcade_version, $context, $modSettings;

	$subAction = !empty($_REQUEST['sa']) ? $_REQUEST['sa'] : '';
	if (empty($modSettings['arcadeList']))
		$modSettings['arcadeList'] = 0;

	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
			<div id="arcade_bottom" class="smalltext" style="text-align: center;">
				Powered by: <a href="http://web-develop.ca/index.php?page=arcade_license_BSD2" target="_blank">SMF Arcade ', $arcade_version, '</a> &copy; 2004-2017
			</div>';
}
?>