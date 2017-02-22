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
	global $scripturl, $txt, $context, $settings, $modSettings;

	if ( $_REQUEST['sa'] == 'list' || $_REQUEST['sa'] == 'search')
	{
		$categories = ArcadeCats($_SESSION['current_cat']);
		echo '
	<div class="tborder" style="text-align:center;border: 2px solid;">
		<table style="border-collapse: collapse;width: 100%;" class="tborder table_grid">
			<thead>
				<tr>';

		if($context['curved'])
			echo '
					<td class="catbg" style="padding: 5px;border:0px;height: 23px;"></td>
					<td class="smalltext catbg" style="padding: 5px;border:0px;text-align:center;overflow: hidden;height: 23px;line-height: 23px;border:0px;font-family: georgia; font-style: oblique;font-size: 1.1em;font-weight: bold;">', $txt['arcade_title'], '</td>
					<td class="catbg" style="padding: 5px;border:0px;height: 23px;line-height: 23px;"></td>';
		else
			echo '
					<th class="catbg">&nbsp;</th>
					<th class="catbg centertext">
						<div style="font-family: georgia; font-style: oblique;font-size: 1.1em;font-weight: bold;">', $txt['arcade_stats'] , '</div>
					</th>
					<th class="catbg">&nbsp;</th>';

		echo '
				</tr>
				<tr>
					<td class="windowbg smalltext" style="vertical-align: top;width: 24%;padding: 5px;font-size:0.85em;">
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">', $txt['latest_games'] ,'</div>
						',  ArcadeNewestGames($modSettings['skin_latest_games']), '
						<div class="titlebg centertext" style="margin-bottom:10px;font-size:1.3em;">
						', $txt['arcade_game_search'] ,'
						</div>
						<div class="centertext smalltext" style="margin-bottom:15px;font-size:1.0em;">
							<form name="search" action="', $scripturl, '?action=arcade;sa=search" method="post" onSubmit="return empty();">
								<input id="gamesearch" type="text" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '" />
								<input class="button_submit smalltext" type="submit" value="', $txt['arcade_search_go'] , '"  name="submit1" />
								<div id="suggest_gamesearch" class="game_suggest"></div>
								<script type="text/javascript"><!-- // --><![CDATA[
									var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
								// ]]></script>
							</form>
						</div>
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
						', $txt['arcade_game_sort'],'
						</div>
						<div class="centertext smalltext" style="padding:5px;margin:10px;font-size:1.0em;">
							<form action="', $scripturl, '?action=arcade;sa=list" method="post">
								<select name="sortby" onchange="submit();">
									<option value="0">', $txt['arcade_sort_by'], '</option>
									<option value="age"', $_SESSION['arcade_sortby'] === 'age' ? ' selected' : '', '>', $txt['arcade_age'], '</option>
									<option value="a2z"', $_SESSION['arcade_sortby'] === 'a2z' ? ' selected' : '', '>', $txt['arcade_a2z'], '</option>
									<option value="z2a"', $_SESSION['arcade_sortby'] === 'z2a' ? ' selected' : '', '>', $txt['arcade_z2a'], '</option>
									<option value="plays"', $_SESSION['arcade_sortby'] === 'plays' ? ' selected' : '', '>', $txt['arcade_plays'], '</option>
									<option value="plays_reverse"', $_SESSION['arcade_sortby'] === 'plays_reverse' ? ' selected' : '', '>', $txt['arcade_playsl'], '</option>
									<option value="champion"', $_SESSION['arcade_sortby'] === 'champion' ? ' selected' : '', '>', $txt['arcade_champion'], '</option>
									<option value="champs"', $_SESSION['arcade_sortby'] === 'champs' ? ' selected' : '', '>', $txt['arcade_latest_champions'], '</option>
									<option value="rating"', $_SESSION['arcade_sortby'] === 'rating' ? ' selected' : '', '>', $txt['arcade_rating'], '</option>
									<option value="favorites"', $_SESSION['arcade_sortby'] === 'favorites' ? ' selected' : '', '>', $txt['arcade_favs'], '</option>
								</select>
							</form>
						</div>
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
						', $txt['arcade_random_game'],'
						</div><br />
						<div style="margin-bottom:3px;font-size:0.8em;">', ArcadeRandomGames(1), '</div>
					</td>
					<td class="windowbg smalltext" style="padding: 5px;vertical-align: top;font-size:0.85em;">
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
						', $txt['latest_champs'],'
						</div>
						<div class="windowbg2" style="margin:5px 2px 5px 2px;font-size:1.0em;text-align:left;">
						', ArcadeNewChamps($modSettings['skin_latest_champs']), '
						</div>
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
							<img src="', $settings['images_url'], '/gold.gif" alt="" />
							', ($_SESSION['current_cat'] == 'all' ? $txt['arcade_champs'] : sprintf($txt['cat_champs'], $context['cat_name'])), '
							<img src="', $settings['images_url'], '/gold.gif" alt="" />
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
									<img src="', $settings['images_url'], '/', $score_poss, '.gif" style="margin-bottom: 3px" alt="" /><br />
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
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
						', $txt['latest_scores'] ,'
						</div>
						<div class="windowbg2" style="border:0px;margin:5px 2px 1px 2px;font-size:1.0em;text-align:left;">
						', ArcadeLatest($modSettings['skin_latest_scores']), '
						</div>
					</td>
					<td class="windowbg smalltext" style="width: 24%;vertical-align: top;font-size:0.85em;">
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">', $txt['most_played'], '</div>
						', ArcadePopular($modSettings['skin_most_popular']), '
						<div class="titlebg centertext" style="margin-bottom:4px;font-size:1.3em;">
						', $txt['arcade_daily'], '
						</div>';

		$game = getGameOfDay();
		strlen($game['name']) >= 23 ? $game['name'] = substr($game['name'],0,22) . '...' : '';

		if (!empty($game['url']['play']))
		{
			echo '
						<div class="smalltext" style="padding: 0px 5px 0px 5px">
							<div class="titlebg centertext" style="margin:4px 0px 5px 0px;border-bottom:1px solid #808080;font-size:1.1em;">
							', $game['name'], '
							</div><br />
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
						<div class="titlebg" style="margin:4px 0px 5px 0px;border-bottom:1px solid #808080; text-align:center;font-size:1.1em;">
						', $txt['todays_scores'], '</div>
							<div style="margin: 5px 0px 0px 5px">
							', ArcadeDailyChallenge($game);

		if ($context['CH_error'])
			echo '
						<div class="smalltext centertext">', $txt['arcade_daily_none'], '</div>';

		echo '
						</div>';

		if (empty($modSettings['arcadeDropCat']))
			$modSettings['arcadeDropCat'] = 0;

		if ($modSettings['arcadeDropCat'] == 1)
			echo '
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
						', $txt['game_categories'], '
						</div>
						<div class="smalltext centertext" style="margin: 5px 0px 0px 5px;font-size:1.0em;"><br />
						', ArcadeCategoryDropdown(), '
						</div>';

			echo '
					</td>
				</tr>
				<tr>';

		if($context['curved'])
			echo '
					<td style="border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 0 -173px;height: 20px;"></td>
					<td style="border:0px;background: url(',$settings['default_theme_url'],'/images/theme/main_block.png) no-repeat -10px -173px;height: 20px;"></td>
					<td style="border:0px;background: url(',$settings['default_theme_url'],'/images/theme/main_block.png) no-repeat 100% -173px;"></td>';
		else
			echo '
					<td colspan="3" class="catbg2 headerpadding">&nbsp;</td>';

		echo '
				</tr>
			</table>
		</div>
		<div style="width:100%;">
			<div style="text-align:left;padding:6px 0px 3px 4px;">
				<ul class="dropmenu">';

		// Print out all the items in this tab.
		foreach ($context['arcade_tabs']['tabs'] as $tab)
		{
			echo '
					<li>
						<a href="', $tab['href'], '" class="', (!empty($tab['is_selected']) ? 'active ' : ''), 'firstlevel">
							<span class="firstlevel">', $tab['title'], '</span>
						</a>
					</li>';
		}

		echo '
				</ul>
			</div>
			<div class="smalltext" style="padding:0px 4px 3px 4px;text-align:right;">';

		if ($context['arcade']['stats']['games'] != 0)
			echo sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']);

		echo '
			</div>
		</div><br style="clear: both;" />
		<div style="height:10px;">
			<span>&nbsp;</span>
		</div>';

		if ($modSettings['arcadeDropCat'] == 0)
		{
			echo '
		<table style="width: 100%;border-collapse: collapse;" class="table_grid">
			<thead>
				<tr>';

			if($context['curved'])
				echo '
					<td class="catbg" style="padding: 5px;border:0px;display: block;height: 23px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 0 -160px;"></td>
					<td class="catbg" colspan="3" style="padding: 5px;border: 0px;text-align: center;overflow: hidden;height: 23px;line-height: 23px;border: 0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat -10px -160px;font-family: georgia; font-style: oblique;font-size: 0.8em;font-weight: bold;">
						<a title="', $txt['arcade_defcat'], '" href="', $scripturl, '?action=arcade;category=0">', $txt['arcade_game_cats'], '</a>
					</td>
					<td class="catbg" style="padding: 5px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 100% -160px;padding-right: 9px;" ></td>';
			else
				echo '
					<td class="catbg centertext" colspan="5" style="padding: 5px;">
						<div style="font-family: georgia; font-style: oblique;font-size: 1.1em;font-weight: bold;text-align:center">
							<a title="', $txt['arcade_defcat'], '" href="', $scripturl, '?action=arcade;category=0">', $txt['arcade_game_cats'], '</a>
						</div>
					</td>';

			echo '
				</tr>', $categories;

			if($context['curved'])
				echo '
				<tr>
					<td style="padding: 5px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 0 -173px;height: 20px;"></td>
					<td colspan="3" style="padding: 5px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat -10px -173px;height: 20px;"></td>
					<td style="padding: 5px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 100% -173px;" ></td>
				</tr>';
			else
				echo '
				<tr>
					<td colspan="5" class="catbg" style="padding: 5px;">&nbsp;</td>
				</tr>';

			echo '
				<tr>
					<td colspan="5" style="padding: 5px;">&nbsp;</td>
				</tr>
			</table>';
		}

	}

	echo '
			<div class="mediumtext" style="font-size:1.1em;">';
}

function template_arcade_below()
{
	global $arcade_version, $context, $modSettings;

	$subAction = !empty($_REQUEST['sa']) ? $_REQUEST['sa'] : '';
	if (empty($modSettings['arcadeList']))
		$modSettings['arcadeList'] = 0;

	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
			</div>
			<div id="arcade_bottom" class="smalltext" style="text-align: center;">
				Powered by: <a href="http://web-develop.ca/index.php?page=arcade_license_BSD2" target="_blank">SMF Arcade ', $arcade_version, '</a> &copy; 2004-2017
			</div>';

	 if (empty($context['game']['id']) && $modSettings['arcadeList'] == 0 && $subAction !== 'stats')
		echo '
			</div>';
}
?>