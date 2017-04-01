<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_game_above()
{
	global $scripturl, $txt, $context, $settings, $modSettings, $boardurl, $options;

	echo '
	<div style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>
	<span class="clear upperframe"><span>&nbsp;</span></span>
	<div class="roundframe">
		<div class="innerframe">
			<div class="cat_bar">
				<h3 class="catbg" style="vertical-align: middle;">
					<a href="', $scripturl, '?index.php;action=arcade;sa=play;game=', $context['game']['id'], '" title="', $txt['arcade_play'],' ', $context['game']['name'], '">
						<span class="clear: right;" style="font-size: 0.8em;">', $context['game']['name'], '</span>
					</a>', (version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '
					<img id="game_toggle" class="floatright icon" src="' . $settings['images_url'] . '/collapse.gif' . '" alt="" title="' . $txt['upshrink_description'] . '" style="cursor: pointer;margin: 10px 5px 0 0;" />' : '
					<span id="game_toggle" class="floatright icon ' . (empty($options['game_panel_collapse']) ? ' toggle_up' : ' toggle_down') . '" title="' . $txt['upshrink_description'] . '" style="cursor: pointer;margin: 10px 5px 0 0;">&nbsp;</span>'), '
				</h3>
			</div>
			<div id="game_panel" class="windowbg2 smalltext" style="margin: 0;', empty($options['game_panel_collapse']) ? '' : ' display: none;', '">
				<span class="topslice"><span>&nbsp;</span></span>
				', !empty($context['game']['thumbnail']) ? '<img class="floatleft thumb" src="' . $context['game']['thumbnail'] . '" alt="" />' : '', '
				<div class="floatleft scores" style="padding-left: 5px;vertical-align: bottom;">';

	if ($context['game']['is_champion'])
		echo '
					<strong class="smalltext">', $txt['arcade_champion'], ':</strong> ', $context['game']['champion']['link'], ' - ', $context['game']['champion']['score'], '<br />';
	if ($context['game']['is_personal_best'])
		echo '
					<strong class="smalltext">', $txt['arcade_personal_best'], ':</strong> ', $context['game']['personal_best'], '<br />';

	echo '
				</div>
				<div class="floatright rating" style="text-align: right">';

	if ($context['arcade']['can_favorite'])
		echo '
					<a href="', $context['game']['url']['favorite'], '" onclick="arcade_favorite(', $context['game']['id'], '); return false;">', !$context['game']['is_favorite'] ?  '<img id="favgame' . $context['game']['id'] . '" src="' . $settings['images_url'] . '/favorite.gif" alt="' . $txt['arcade_add_favorites'] . '" />' : '<img id="favgame' . $context['game']['id'] . '" src="' . $settings['images_url'] . '/favorite2.gif" alt="' . $txt['arcade_remove_favorite'] . '" />', '</a><br />';

	if ($context['arcade']['can_rate'])
		echo '
					', $context['arcade_ratecode'], '<span style="display: block;"><span style="display: none;">&nbsp;</span></span>';

	echo '
				</div><br class="clear" />
				<span class="botslice"><span>&nbsp;</span></span>
			</div>
			<script type="text/javascript"><!-- // --><![CDATA[
				var oGameHeaderToggle = new smc_Toggle({
					bToggleEnabled: true,
					bCurrentlyCollapsed: ', empty($options['game_panel_collapse']) ? 'false' : 'true', ',
					aSwappableContainers: [\'game_panel\'],
					', (version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '
					aSwapImages: [
						{
							sId: \'game_toggle\',
							srcExpanded: smf_images_url + \'/collapse.gif\',
							altExpanded: ' . JavaScriptEscape($txt['upshrink_description']) . ',
							srcCollapsed: smf_images_url + \'/expand.gif\',
							altCollapsed: ' . JavaScriptEscape($txt['upshrink_description']) . '
						}
					],' : '
					aSwapImages: [
						{
							sId: \'game_toggle\',
							altExpanded: ' . JavaScriptEscape($txt['upshrink_description']) . ',
							altCollapsed: ' . JavaScriptEscape($txt['upshrink_description']) . '
						}
					],'), '
					oThemeOptions: {
						bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
						sOptionName: \'game_panel_collapse\',
						sSessionVar: ', JavaScriptEscape($context['session_var']), ',
						sSessionId: ', JavaScriptEscape($context['session_id']), '
					},
					oCookieOptions: {
						bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
						sCookieName: \'arcadegameupshrink\'
					}
				});', (version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '' : '
				var checkArcadeContainer = readArcadeCookie("checkArcadeContainer") != "" ? readArcadeCookie("checkArcadeContainer") : document.getElementById("game_panel").style.display;
				if (checkArcadeContainer === "none")
				{
					$("#game_toggle").toggleClass("toggle_down", true);
					writeArcadeCookie("checkArcadeContainer", "", 1);
				}
				else
				{
					$("#game_toggle").toggleClass("toggle_up", true);
					writeArcadeCookie("checkArcadeContainer", "none", 1);
				}'), '
				function myformxyz(myform)
				{
					document.getElementById(myform).submit();
				}
				function mycheckxyz()
				{
					if (confirm(\'', $txt['arcade_are_you_sure'], '\'))
						return true;
					else
						return false;
				}
			// ]]></script>';
}

// Play screen
function template_arcade_game_play()
{
	global $scripturl, $txt, $context, $settings, $modSettings;

	echo '
			<div class="windowbg2" id="playgame">
				<span class="topslice"><span>&nbsp;</span></span>
				<div id="gamearea">
					', $context['game']['html']($context['game'], true), '
					', !$context['arcade']['can_submit'] ? '<br /><strong>' . $txt['arcade_cannot_save'] . '</strong>' : '', '
				</div>
				<span class="botslice"><span>&nbsp;</span></span>
			</div>
		</div>';
}

function template_arcade_html5_game_play()
{
	global $scripturl, $txt, $context, $settings, $modSettings;

	echo '
			<form id="gameForm" action="', $scripturl, '?action=arcade;game=', $context['game']['id'], ';sa=html5Game;" method="POST">
				<input type="hidden" id="game" name="game" value="', $context['game']['id'], '">
				<input type="hidden" id="time" name="time" value="', time(), '">
				<input type="hidden" id="gamesessid" name="gamesessid">
				<input type="hidden" id="html5" name="html5" value="1">
				<input type="hidden" id="game_name" name="game_name" value="', $context['game']['internal_name'], '">
				<div class="windowbg2" id="playgame">
					<span class="topslice"><span>&nbsp;</span></span>
					<div id="gamearea" class="centertext">
						<div style="display: inline;overflow: hidden;border: 0px;height: ' . ((int)$context['game']['height'] + 8) . 'px;width: ' . ((int)$context['game']['width'] + 8) . 'px;">
							<object id="gameObj" type="text/html" style="overflow: hidden;height: ' . ((int)$context['game']['height'] + 50) . 'px;width: ' . ((int)$context['game']['width'] + 50) . 'px;" data="' . $modSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '">
							</object>
						</div>
						', !$context['arcade']['can_submit'] ? '<br /><strong>' . $txt['arcade_cannot_save'] . '</strong>' : '', '
					</div>
					<span class="botslice"><span>&nbsp;</span></span>
				</div>
			</form>
		</div>';
}

// Highscore
function template_arcade_game_highscore()
{
	global $scripturl, $txt, $context, $settings, $modSettings;

	if (isset($context['arcade']['submit']))
	{
		if ($context['arcade']['submit'] == 'newscore') // Was score submitted
		{
			$score = &$context['arcade']['new_score'];

			echo '
			<div class="cat_bar" id="submitscore">
				<h3 class="catbg">
					', $txt['arcade_submit_score'], '
				</h3>
			</div>
			<div class="windowbg2 smalltext">
				<span class="topslice"><span>&nbsp;</span></span>
				<div style="padding: 0 0.5em">';

			// No permission to save
			if (!$score['saved'])
				echo '
					<div>', $txt[$score['error']], '<br /><strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '</div>';

			else
			{
				echo '
					<div>', $txt['arcade_score_saved'], '<br /><strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '<br />';

				if ($score['is_new_champion'])
					echo '
						', $txt['arcade_you_are_now_champion'], '<br />';
				elseif ($score['is_personal_best'])
					echo '
						', $txt['arcade_this_is_your_best'], '<br />';

				if ($score['can_comment'])
					echo '
					</div>
					<div>
						<form id="commentform1" action="', $scripturl, '?action=arcade;sa=highscore;game=', $context['game']['id'], ';score=',  $score['id'], ';reload=', mt_rand(1, 9999), ';#commentform3" method="post" onsubmit="myformxyz(\'commentform1\')">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="text" id="new_comment" name="new_comment" style="width: 95%;" />
							<input onclick="myformxyz(\'commentform1\')" class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />
						</form>
					</div>';
			}

			echo '
				</div>
				<span class="botslice"><span>&nbsp;</span></span>
			</div><br />';
		}
		elseif ($context['arcade']['submit'] == 'askname')
		{
			echo '
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['arcade_submit_score'], '
				</h3>
			</div>
			<div class="windowbg2 smalltext">
				<span class="topslice"><span>&nbsp;</span></span>
				<div style="padding: 0 0.5em">
					<form id="commentform2" action="', $scripturl, '?action=arcade;sa=save;reload=', mt_rand(1, 9999), ';#commentform3" method="post" onsubmit="myformxyz(\'commentform2\')">
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="text" name="name" style="width: 95%;" />
						<input class="button_submit" type="submit" value="', $txt['arcade_save'], '" />
					</form>
				</div>
			</div><br />';
		}
	}
	echo '
		</div>';
	echo '
		<form id="commentform3" name="commentform3" action="', $scripturl, '?action=arcade;sa=highscore;reload=', mt_rand(1, 9999), ';#commentform3" method="post" onsubmit="myformxyz(\'commentform3\')">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="game" value="', $context['game']['id'], '" />
			<div style="padding-top: 10px;"><span style="display: none;">&nbsp;</span></div>
			<div class="title_bar">
				<h3 class="titlebg centertext" style="vertical-align: middle;">
					<span class="smalltext">', $txt['arcade_highscores'], '</span>
				</h3>
			</div>
			<div class="score_table smalltext">
				<table style="border-collapse: collapse;width: 100%;" class="table_grid" id="arccomments">
					<tr class="windowbg2">';

	// Is there games?
	if (!empty($context['arcade']['scores']))
	{
			echo '
						<th scope="col" class="first_th" style="width: 5px;border-bottom: 1px double;">', $txt['arcade_position'], '</th>
						<th scope="col" style="border-bottom: 1px double;">', $txt['arcade_member'], '</th>
						<th scope="col" style="border-bottom: 1px double;"> ', $txt['arcade_comment'], '</th>
						<th scope="col" class="', !$context['arcade']['can_admin_arcade'] ? ' last_th' : '', '" style="border-bottom: 1px double;">', $txt['arcade_score'], '</th>';

		if ($context['arcade']['can_admin_arcade'])
			echo '
						<th scope="col" class="last_th centertext" style="width: 15px;"><input type="checkbox" onclick="invertAll(this, this.form, \'scores[]\');" class="check" /></th>';
	}
	else
	{
		echo '
						<th scope="col" class="first_th" style="width: 8%;border-bottom: 1px double;">&nbsp;</th>
						<th class="smalltext" colspan="', !$context['arcade']['can_admin_arcade'] ? '2' : '3', '" style="border-bottom: 1px double;"><strong>', $txt['arcade_no_scores'], '</strong></th>
						<th scope="col" class="last_th" style="width: 8%;border-bottom: 1px double;">&nbsp;</th>';
	}

	echo '
					</tr>';

		$edit_button = '<span style="width: 16px;height: 16px;display: inline-block;background: url(' . $settings['default_theme_url'] . '/images/arcade_edit.gif) no-repeat;vertical-align: middle;">&nbsp;</span>';

	foreach ($context['arcade']['scores'] as $score)
	{
		if (empty($score['time']))
			continue;

		$div_con = addslashes(sprintf($txt['arcade_when'], $score['time'], duration_format($score['duration'])));

		echo '
					<tr class="', $score['own'] ? 'windowbg2 arcade_own_score' : 'windowbg2', '"', !empty($score['highlight']) ? ' style="font-weight: bold;"' : '', ' onmouseover="arcadeBox(\'', $div_con, '\')" onmousemove="arcadeBoxMove(event)" onmouseout="arcadeBox(\'\')">
						<td class="windowbg2 centertext">', $score['position'], '</td>
						<td class="windowbg2">', $score['member']['link'], '</td>
						<td style="width: 300px;" class="windowbg2">';

		if ($score['can_edit'] && empty($score['edit']))
			echo '
							<div id="comment', $score['id'], '" class="floatleft">', $score['comment'], '</div>
							<div id="edit', $score['id'], '" class="floatleft" style="display: none;">
								<input type="text" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;"  />
								<input type="button" onclick="myformxyz(\'commentform3\')" name="csave" value="', $txt['arcade_save'], '" />
							</div>
								<a id="editlink', $score['id'], '" onclick="myformxyz(\'commentform3\')" href="', $scripturl, '?action=arcade;sa=highscore;game=', $context['game']['id'], ';edit;score=', $score['id'], ';reload=' . mt_rand(1, 9999) . ';#commentform3" class="floatright">', $edit_button, '</a>';
		elseif ($score['can_edit'] && !empty($score['edit']))
		{
			echo '
							<input type="hidden" name="score" value="', $score['id'], '" />
							<input type="text" name="new_comment" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" />
							<input onclick="myformxyz(\'commentform3\')" class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />';
		}
		else
			echo $score['comment'];

		echo '
						</td>
						<td class="centertext windowbg2">', $score['score'], '</td>';


		if ($context['arcade']['can_admin_arcade'])
			echo '
						<td class="windowbg2 centertext"><input type="checkbox" name="scores[]" value="', $score['id'], '" class="check" /></td>';

		echo '
					</tr>';
	}

	if ($context['arcade']['can_admin_arcade'])
	{
		echo '
					<tr>
						<td colspan="', $context['arcade']['can_admin_arcade'] ? '5' : '6', '" style="text-align: right;">
							<select name="qaction">
								<option value="">--------</option>
								<option value="delete">', $txt['arcade_delete_selected'], '</option>
							</select>
							<input value="', $txt['go'], '" onclick="return mycheckxyz()" class="button_submit" type="submit" />
						</td>
					</tr>';
	}

	echo '
				</table>
			</div>
		</form>';
}

// Below game
function template_arcade_game_below()
{
	global $scripturl, $txt, $context, $settings, $modSettings;

	echo '
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div class="pagesection">
		<div class="align_left">';

	if (isset($context['page_index']))
		echo $txt['pages'], ': ', $context['page_index'];

	if (!empty($modSettings['topbottomEnable']))
		echo isset($context['page_index']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '';

	echo '</div>
		', template_button_strip($context['arcade']['buttons'], 'right'), '
	</div>
	<div class="plainbox" id="arcadebox" style="display: none; position: fixed; left: 0px; top: 0px; width: 33%;">
		<div id="arcadebox_html" style="display: inline;"></div>
	</div>';
}
?>