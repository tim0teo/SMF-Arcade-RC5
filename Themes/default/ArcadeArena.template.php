<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_arena_matches()
{
	global $scripturl, $txt, $context, $settings, $user_info, $modSettings;

	$buttons = array();

	if ($context['arcade']['can_create_match'])
		$buttons['newMatch'] = array(
			'text' => 'arcade_newMatch',
			'image' => 'arcade_newmatch.gif',
			'url' => $scripturl . '?action=arcade;sa=newMatch',
			'lang' => true,
		);

	echo '
	<div class="pagesection">', template_button_strip($buttons, 'right'), '</div>
	<div style="padding-top: 15px;" class="game_table">';

	if (empty($context['matches']))
		echo '
		<div class="cat_bar">
			<h3 class="catbg centertext" style="vertical-align: middle;">
				<strong>', $txt['arcade_no_matches'], '</strong>
			</h3>
		</div>';
	else
	{
		echo '
		<table style="border-collapse: collapse;" class="table_grid">
			<thead>
				<tr class="catbg">
					<th scope="col" class="first_th"></th>
					<th scope="col">', $txt['match_name'], '</th>
					<th scope="col">', $txt['match_status'], '</th>
					<th scope="col">', $txt['match_players'], '</th>
					<th scope="col" class="smallext last_th">', $txt['match_round'], '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['matches'] as $match)
		{
			echo '
				<tr class="windowbg">
					<td></td>
					<td class="windowbg2">', $match['link'], '<br >', $match['starter']['link'], '</td>
					<td>', $txt[$match['status']], '</td>
					<td>', $match['players'], ' / ', $match['players_limit'], '</td>
					<td>', $match['round'], ' / ', $match['rounds'], '</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>
	<div class="pagesection" style="padding-top: 5px;">
		<div class="align_left">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '', '</div>
	</div>';
}

function template_arcade_arena_view_match_above()
{
	global $scripturl, $txt, $context, $settings, $user_info, $modSettings;

	echo '
	<div style="padding-top: 15px;"><span></span></div>
	<span class="clear upperframe"><span></span></span>
	<div class="roundframe" style="border-radius: 3px;">
		<div class="innerframe" style="border-radius: 5px;">
			<div class="cat_bar">
				<h3 class="catbg centertext" style="vertical-align: middle;">
					<span class="floatleft">', $context['match']['name'], '</span>
				</h3>
			</div>
			<div style="padding-top: 10px;"><span></span></div>
			<div style="padding: 0 0.5em;display: table;border-collapse:separate;border-spacing:5px;width: 100%;">
				<div style="display: table-row;width: 100%;">
					<span class="clear" style="display: table-cell;position: absolute;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
						<span style="display: table;">
							<span style="display: table-row;">
								<span style="display: table-cell"><strong>', $txt['match_status'], '</strong>:</span><span style="display: table-cell">', $txt[$context['match']['status']], '</span>
							</span>
							<span style="display: table-row;">
								<span style="display: table-cell"><strong>', $txt['match_players'], '</strong>:</span><span style="display: table-cell">', $context['match']['num_players'], ' / ', $context['match']['players_limit'], '</span>
							</span>
							<span style="display: table-row;">
								<span style="display: table-cell"><strong>', $txt['match_round'], '</strong>:</span><span style="display: table-cell">', $context['match']['round'], ' / ', $context['match']['num_rounds'], '</span>
							</span>
						</span>
					</span>';

	if ($context['can_start_match'])
	{
		echo '
					<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
						<span style="display: table;">
							<span style="display: table-row;">								
								<span style="display: table-cell;">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;start;match=', $context['match']['id'], ';' . $context['session_var'] . '=' . $context['session_id'], '" class="arc_button floatleft clearfix">
										', $txt['arcade_startMatch'], '
									</a>
								</span>
								<span style="display: table-cell;">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;start;match=', $context['match']['id'], ';' . $context['session_var'] . '=' . $context['session_id'], '" class="arc_button floatleft clearfix">
										<img class="icon" src="', $settings['images_url'], '/arena_accept.png" alt="" />
									</a>
								</span>
							</span>
						</span>
					</span>';
	}

	if ($context['can_play_match'])
	{
		echo '
					<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
						<span style="display: table;">
							<span style="display: table-row;">								
								<span style="display: table-cell;">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=play;match=', $context['match']['id'], '" class="arc_button floatleft clearfix">
										', $txt['arcade_play'], '
									</a>
								</span>
								<span style="display: table-cell">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=play;match=', $context['match']['id'], '" class="arc_button floatleft clearfix">
										<img class="icon" src="', $settings['images_url'], '/arena_accept.png" alt="" />
									</a>
								</span>
							</span>
						</span>
					</span>';
	}

	if ($context['can_edit_match'])
	{
		echo '
					<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">						
						<span style="display: table;">
							<span style="display: table-row;">								
								<span style="display: table-cell;">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;delete;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										', $txt['arcade_cancelMatch'], '
									</a>
								</span>
								<span style="display: table-cell">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;delete;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										<img class="icon" src="', $settings['images_url'], '/arena_decline.png" alt="" />
									</a>
								</span>
							</span>
						</span>
					</span>';
	}

	if ($context['can_join_match'])
	{
		echo '
					<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
						<span style="display: table;">
							<span style="display: table-row;">								
								<span style="display: table-cell;">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;join;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										', $txt['arcade_joinMatch'], '
									</a>
								</span>
								<span style="display: table-cell">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;join;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										<img class="icon" src="', $settings['images_url'], '/arena_accept.png" alt="" />
									</a>
								</span>
							</span>
						</span>
					</span>';
	}
	elseif ($context['can_leave'])
	{
		echo '
					<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
						<span style="display: table;">
							<span style="display:table-row;">								
								<span style="display: table-cell;">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;leave;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										', $txt['arcade_leaveMatch'], '
									</a>
								</span>
								<span style="display: table-cell">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;leave;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										<img class="icon" src="', $settings['images_url'], '/arena_decline.png" alt="" />
									</a>
								</span>
							</span>
						</span>
					</span>';
	}
	elseif ($context['can_accept'])
	{
		echo '
					<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
						<span style="display: table;">
							<span style="display: table-row;">								
								<span style="display: table-cell;">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;join;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										', $txt['arcade_accept'], '
									</a>
								</span>
								<span style="display: table-cell">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;join;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										<img class="icon" src="', $settings['images_url'], '/arena_accept.png" alt="" />
									</a>
								</span>
							</span>
							<span style="display: table-row;">								
								<span style="display: table-cell;">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;leave;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										', $txt['arcade_decline'], '
									</a>
								</span>
								<span style="display: table-cell;">
									<a style="text-decoration: none;" href="', $scripturl, '?action=arcade;sa=viewMatch;leave;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], '" class="arc_button floatleft clearfix">
										<img class="icon" src="', $settings['images_url'], '/arena_decline.png" alt="" />
									</a>
								</span>
							</span>
						</span>
					</span>';
	}

	echo '
				</div>
			</div>
			<div class="clear" style="padding-top: 35px;"><span></span></div>
		</div>';

	echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		var oArenaPanelToggle = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: ', empty($options['arena_panel_collapse']) ? 'false' : 'true', ',
			aSwappableContainers: [
				\'arena_panel\'
			],
			aSwapImages: [
				{
					sId: \'arena_panel_toggle\',
					srcExpanded: smf_images_url + \'/collapse.gif\',
					altExpanded: ', JavaScriptEscape($txt['upshrink_description']), ',
					srcCollapsed: smf_images_url + \'/expand.gif\',
					altCollapsed: ', JavaScriptEscape($txt['upshrink_description']), '
				}
			],
			oThemeOptions: {
				bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
				sOptionName: \'arena_panel_collapse\',
				sSessionVar: ', JavaScriptEscape($context['session_var']), ',
				sSessionId: ', JavaScriptEscape($context['session_id']), '
			},
			oCookieOptions: {
				bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
				sCookieName: \'arenapanelupshrink\'
			}
		});
	// ]]></script>';
}

function template_arcade_arena_view_match()
{
	global $scripturl, $txt, $context, $settings, $user_info, $modSettings;

	echo '
	<div class="floatleft" style="width: 48%;padding: 0 0.5em;">
		<table cellspacing="1" class="playerlist">
			<tr class="catbg">
				<th class="first_th">', $txt['arcade_position'], '</th>
				<th>', $txt['arcade_member'], '</th>
				<th>', $txt['match_status'], '</th>
				<th class="last_th">', $txt['arcade_score'], '</th>
			</tr>';

	foreach ($context['match']['players'] as $player)
	{
		echo '
			<tr class="windowbg">
				<td width="15">', $player['rank'], '.</td>
				<td class="windowbg2">
					<span class="floatleft">', $player['link'], '</span>
					<span class="floatright">';

		if ($player['can_kick'])
			echo '
						<a href="', $player['kick_url'], '"><img src="', $settings['images_url'], '/arena_decline.png" alt="" /></a>';

		echo '
					</span>
				</td>
				<td>', $player['status'], '</td>
				<td>', $player['score'], '</td>
			</tr>';
	}

	echo '
		</table>
	</div>

	<div class="floatright" style="width: 48%;padding: 0 0.5em;">
		<table cellspacing="1" class="gameslist">
			<tr class="catbg">
				<th class="first_th">', $txt['arcade_rounds'], '</th>
				<th class="last_th">', $txt['arcade_game_name'], '</th>
			</tr>';

	foreach ($context['match']['rounds'] as $round)
	{
		echo '
			<tr class="windowbg">
				<td width="15">', $round['round'], '.</td>';

		if ($round['select'] && !$round['can_play'])
			echo '
				<td class="windowbg2">', $round['name'], '</td>';
		elseif ($round['select'] && $round['can_play'])
			echo '
				<td class="windowbg2"><a href="', $round['play_url'], '">', $round['name'], '</a></td>';
		elseif ($round['can_select'])
			echo '
				<td class="windowbg2"><a href="', $round['url'], '">', $txt['select_game'], '</a></td>';
		else
			echo '
				<td class="windowbg2"></td>';

		echo '
			</tr>';
	}

	echo '
		</table>
	</div>
	<br class="clear" />';
}

function template_arcade_arena_view_match_below()
{
	global $scripturl, $txt, $context, $settings, $user_info, $modSettings;
	echo '
		<div style="padding-top: 45px;"><span></span></div>
	</div>	
	<span class="lowerframe"><span></span></span>';

}

function template_arcade_arena_new_match()
{
	global $scripturl, $txt, $context, $settings, $user_info, $modSettings;

	echo '
	<form action="', $scripturl, '?action=arcade;sa=newMatch2" method="post">
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		<input type="hidden" name="segnum" value="', $context['form_sequence_number'], '" />
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_new_match'], '
			</h3>
		</div>';

	if (!empty($context['errors']))
		echo '
		<div class="windowbg">
			<div style="color: red; margin: 1ex 0 2ex 3ex;" id="error_list">
				', implode('<br />', $context['errors']), '
			</div>
		</div>';

	echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div style="padding: 0 0.5em">
				<div class="windowbg2">
					<table width="100%"><tr>
						<td width="150"><label for="match_name">', $txt['arcade_match_name'], '</label></td>
						<td><input type="text" name="match_name" id="match_name"  value="', $context['match']['name'], '" style="width: 99%"/></td>
					</tr><tr>
						<td><label for="game_mode">', $txt['game_mode'], '</label></td>
						<td>
							<select name="game_mode" id="game_mode">
								<option value="normal"', $context['match']['game_mode'] == 'normal' ? ' selected="selected"' : '', '>', $txt['game_mode_normal'], '</option>
								<option value="knockout"', $context['match']['game_mode'] == 'knockout' ? ' selected="selected"' : '', '>', $txt['game_mode_knockout'], '</option>
							</select><br />
						</td>
					</tr><tr>
						<td valign="top">', $txt['players'], '</td>
						<td>
							<input type="text" name="player" id="player" size="25" />
							<input class="button_submit" name="player_submit" value="', $txt['player_add'], '" onclick="return oPlayerSuggest.onSubmit();" type="submit">
							<div id="player_container"></div>
							<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?rc1"></script>
							<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
								var oPlayerSuggest = new smc_AutoSuggest({
									sSelf: \'oPlayerSuggest\',
									sSessionVar: \'', $context['session_var'], '\',
									sSessionId: \'', $context['session_id'], '\',
									sSuggestId: \'player\',
									sControlId: \'player\',
									sSearchType: \'member\',
									bItemList: true,
									sPostName: \'players_list\',
									sURLMask: \'action=profile;u=%item_id%\',
									sItemListContainerId: \'player_container\',
									aListItems: [';

	foreach ($context['players'] as $member)
		echo '
										{
											sItemId: ', JavaScriptEscape($member['id']), ',
											sItemName: ', JavaScriptEscape($member['name']), '
										}', $member['id'] == $context['last_player_id'] ? '' : ',';

		echo '
									]
								});
							// ]]></script>
							<noscript>';

	foreach ($context['players'] as $member)
		echo '
								<div>
									<input type="hidden" name="players_list[]" value="', $member['id'], '" />
									<a href="', $scripturl, '?action=profile;u=', $member['id'], '" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">', $member['name'], '</a>
									', $user_info['id'] == $member['id'] ? '' : '<input type="image" name="delete_player" value="' . $member['id'] . '" src="' . $settings['images_url'] . '/pm_recipient_delete.gif" alt="' . $txt['player_remove'] . '" />', '
								</div>';

	echo '
							</noscript>
						</td>
					</tr><tr>
						<td><label for="num_players">', $txt['num_players'], '</label></td>
						<td><input type="text" name="num_players" id="num_players" value="', $context['match']['num_players'], '" size="20" /><br />
						<span class="smalltext">', $txt['num_players_help'], '</span></td>
					</tr><tr>
						<td valign="top">', $txt['rounds'], '</td>
						<td>
							<input id="arenagame" style="width: 240px;" autocomplete="off" type="text" name="arenagame_input" value="" />
							<input class="button_submit" type="submit" name="arenagame_submit" value="', $txt['add_game'], '" onclick="return gameSelectorarenagame.onSubmit();" />
							<div id="suggest_arenagame" class="game_suggest"></div>
							<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
								var gameSelectorarenagame = new gameSelector("', $context['session_id'], '", "arenagame");
							// ]]></script>';

	foreach ($context['match']['rounds'] as $i => $game)
	{
		$game = $context['games'][$game];

		echo '
							<div id="suggest_template_arenagame_', $i, '">
								<input type="hidden" name="rounds[', $i, ']" value="', $game['id'], '" />
								<a href="', $scripturl, '?action=arcade;game=', $game['id'], '" id="game_link_to_', $i, '" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">', $game['name'], '</a>
								<input type="image" name="delete_round" value="', $i, '" onclick="return gameSelectorarenagame.deleteItem(', $i, ');" src="', $settings['images_url'], '/pm_recipient_delete.gif" alt="', $txt['player_remove'], '" /></a>
							</div>';
	}

	echo '
							<div id="suggest_template_arenagame" style="visibility: hidden; display: none;">
								<input type="hidden" name="rounds[]" value="::GAME_ID::" />
								<a href="', $scripturl, '?action=arcade;game=::GAME_ID::" id="game_link_to_::ROUND::" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">::GAME_NAME::</a>
								<input type="image" onclick="return \'::DELETE_ROUND_URL::\'" src="', $settings['images_url'], '/pm_recipient_delete.gif" alt="', $txt['game_remove'], '" />
							</div>
						</td>
					</tr></table>
				</div>
				<div class="windowbg2" style="text-align: right">
					<input class="button_submit" type="submit" name="continue" value="', $txt['arcade_continue'], '" />
				</div>
			</div>
		</div>
		<span class="botslice"><span></span></span>
	</div>
	<br />
	</form>';
}

?>