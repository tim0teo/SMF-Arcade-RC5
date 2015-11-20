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
	global $scripturl, $txt, $context, $sourcedir, $settings, $modSettings;
	require_once($sourcedir . '/skin.php');

	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/skin.js?b4"></script>';

	!isset($_SESSION['current_cat']) ? $_SESSION['current_cat'] = 'all' : '';
    isset($_REQUEST['category']) ? $_SESSION['current_cat'] = $_REQUEST['category'] : $_REQUEST['category'] = $_SESSION['current_cat'];

	if ( $_REQUEST['sa'] == 'list' || $_REQUEST['sa'] == 'search')
	{
		$categories = cats($_SESSION['current_cat']);
		echo '
	<div class="tborder" style="text-align:center;">
		<table cellspacing="0" cellpadding="5" class="table_grid" width="100%">
			<thead>
				<tr>';

		if($context['curved'])
			echo '
					<td class="catbg" style="border:0px;height: 23px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 0 -160px;"></td>
					<td class="smalltext catbg" style="border:0px;text-align:center;overflow: hidden;height: 23px;line-height: 23px;border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat -10px -160px;font-family: georgia; font-style: oblique;font-size: 1.1em;font-weight: bold;">', $txt['arcade_title'], '</td>
					<td class="catbg" style="border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 100% -160px;height: 23px;line-height: 23px;"></td>';
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
					<td class="windowbg smalltext" valign="top" width="24%" style="font-size:0.85em;">
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">', $txt['latest_games'] ,'</div>
						',  newest_games($modSettings['skin_latest_games']), '
						<div class="titlebg centertext" style="margin-bottom:10px;font-size:1.3em;">
						', $txt['arcade_game_search'] ,'
						</div>
						<div class="centertext smalltext" style="margin-bottom:15px;font-size:1.0em;">
							<form name="search" action="', $scripturl, '?action=arcade;sa=search" method="post" onSubmit="return empty();">
								<input id="gamesearch" type="text" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '" />
								<input class="button_submit smalltext" type="submit" value="', $txt['arcade_search_go'] , '"  name="submit1" />
								<div id="suggest_gamesearch" class="game_suggest"></div>
								<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
									var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
								// ]]></script>
							</form>
						</div>
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
						', $txt['arcade_game_sort'],'
						</div>
						<div class="centertext smalltext" style="padding:5px;margin:10px;font-size:1.0em;">
							<form action="', $scripturl, '?action=arcade;sa=list;category=all" method="post">
								<select name="sortby" onchange="submit();">
									<option value="0">', $txt['sort_by'], '</option>
									<option value="age">', $txt['age'], '</option>
									<option value="a2z">', $txt['a2z'], '</option>
									<option value="z2a">', $txt['z2a'], '</option>
									<option value="plays">', $txt['plays'], '</option>
									<option value="playsl">', $txt['playsl'], '</option>
									<option value="champion">', $txt['champion'], '</option>
								</select>
							</form>
						</div>
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
						', $txt['arcade_random_game'],'
						</div><br />
						<div style="margin-bottom:3px;font-size:0.8em;">', random_games(1), '</div>
					</td>
					<td class="windowbg smalltext" valign="top" style="font-size:0.85em;">
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
						', $txt['latest_champs'],'
						</div>
						<div class="windowbg2" style="margin:5px 2px 5px 2px;font-size:1.0em;text-align:left;">
						', newchamps($modSettings['skin_latest_champs']), '
						</div>
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
							<img src="', $settings['images_url'], '/gold.gif" alt="" />
							', ($_SESSION['current_cat'] == 'all' ? $txt['arcade_champs'] : sprintf($txt['cat_champs'], $context['cat_name'])), '
							<img src="', $settings['images_url'], '/gold.gif" alt="" />
						</div>
						<table width="100%" border="0" cellspacing="2">
							<tr>';

		$bp = champs(3, $_SESSION['current_cat'] == 'all' ? 'wins' : 'cats');
		$score_poss = 0;
		if(is_array($bp))
		{
			foreach ($bp as $out)
			{
				$score_poss++;
				echo '
								<td class="windowbg2 centertext" width="33%" style="border:0px;font-size:1.0em;">
									<img src="', $settings['images_url'], '/', $score_poss, '.gif" style="margin-bottom: 3px" alt="" /><br />
									', $out['avatar'], '<br /><strong>', $out['link'], '</strong><br />
									', $txt['win'], ' ', $out['champions'], '
								</td>';
			}
		}
		else
			echo '
								<td class="windowbg2 smalltext" align="center" style="border:0px;font-size:1.0em;">
									', $txt['no_new_champs'], '
								</td>';

		echo '
							</tr>
						</table>
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">
						', $txt['latest_scores'] ,'
						</div>
						<div class="windowbg2" style="border:0px;margin:5px 2px 1px 2px;font-size:1.0em;text-align:left;">
						', latest_scores($modSettings['skin_latest_scores']), '
						</div>
					</td>
					<td class="windowbg smalltext" valign="top" width="24%" style="font-size:0.85em;">
						<div class="titlebg centertext" style="margin-bottom:3px;font-size:1.3em;">', $txt['most_played'], '</div>
						', popular($modSettings['skin_most_popular']), '
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
									<img width="40" height="40" class="imgBorder" src="', $game['thumbnail'], '" alt="' . $txt['alt_play'] . '" title="' . $txt['alt_play'] . '"/>
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
							', dailyCH($game);

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
						', category_dropdown(), '
						</div>';

			echo '
					</td>
				</tr>
				<tr>';

		if($context['curved'])
			echo '
					<td style="border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 0 -173px;" height="20"></td>
					<td style="border:0px;background: url(',$settings['default_theme_url'],'/images/theme/main_block.png) no-repeat -10px -173px;" height="20"></td>
					<td style="border:0px;background: url(',$settings['default_theme_url'],'/images/theme/main_block.png) no-repeat 100% -173px;" ></td>';
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
		</div><br clear="all" />
		<div style="height:10px">
			<span>&nbsp;</span>
		</div>';

		if ($modSettings['arcadeDropCat'] == 0)
		{
			echo '
		<table width="100%" cellspacing="0" cellpadding="5" class="table_grid">
			<thead>
				<tr>';

			if($context['curved'])
				echo '
					<td class="catbg" style="border:0px;display: block;height: 23px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 0 -160px;"></td>
					<td class="catbg" colspan="3" style="border: 0px;text-align: center;overflow: hidden;height: 23px;line-height: 23px;border: 0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat -10px -160px;font-family: georgia; font-style: oblique;font-size: 0.8em;font-weight: bold;">', $txt['arcade_game_cats'], '</td>
					<td class="catbg" style="border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 100% -160px;padding-right: 9px;" ></td>';
			else
				echo '
					<td class="catbg centertext" colspan="5">
						<div style="font-family: georgia; font-style: oblique;font-size: 1.1em;font-weight: bold;text-align:center">
						', $txt['arcade_game_cats'], '
						</div>
					</td>';

			echo '
				</tr>', $categories;

			if($context['curved'])
				echo '
				<tr>
					<td style="border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 0 -173px;" height="20"></td>
					<td colspan="3" style="border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat -10px -173px;" height="20"></td>
					<td style="border:0px;background: url(', $settings['default_theme_url'], '/images/theme/main_block.png) no-repeat 100% -173px;" ></td>
				</tr>';
			else
				echo '
				<tr>
					<td colspan="5" class="catbg">&nbsp;</td>
				</tr>';

			echo '
				<tr>
					<td>&nbsp;</td>
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
				Powered by: <a href="http://web-develop.ca/index.php?page=arcade_license_BSD2" target="_blank">SMF Arcade ', $arcade_version, '</a> &copy; 2004-2015
			</div>';

	 if (empty($context['game']['id']) && $modSettings['arcadeList'] == 0 && $subAction !== 'stats')
		echo '
			</div>';
}
?>