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
	global $scripturl, $txt, $context, $settings, $options, $modSettings, $options;

	if (!empty($context['arcade_tabs']))
	{
		echo '
	<span class="clear upperframe"><span></span></span>
	<div class="roundframe">
		<div class="innerframe">
			<div class="cat_bar">
				<h3 class="catbg" style="vertical-align: middle;">',
		(version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '
					<img id="arcade_toggle" class="floatright icon" src="' . $settings['images_url'] . '/collapse.gif' . '" alt="" title="' . $txt['upshrink_description'] . '" style="cursor: pointer;margin: 10px 5px 0 0;" />' : '
					<span id="arcade_toggle" class="floatright icon' . (empty($options['arcade_panel_collapse']) ? ' toggle_up' : ' toggle_down') . '" title="' . $txt['upshrink_description'] . '" style="cursor: pointer;margin: 10px 5px 0 0;"></span>'), '
					<span style="clear: right;">', $context['arcade_tabs']['title'], '</span>
				</h3>
			</div>
			<div id="arcade_panel" class="plainbox"', empty($options['arcade_panel_collapse']) ? '' : ' style="display: none;"', '>';

		if (!empty($context['arcade']['notice']))
			echo '
				<span class="arcade_notice">', $context['arcade']['notice'], '</span><br />';

		echo '
				<form action="', $scripturl, '?action=arcade;sa=search" method="post">
					<input id="gamesearch" style="width: 240px;" type="text" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '" /> <input class="button_submit" type="submit" value="', $txt['arcade_search'], '" />
					<div id="suggest_gamesearch" class="game_suggest"></div>
					<div id="search_extra">
						<input type="checkbox" id="favorites" name="favorites" value="1"', !empty($context['arcade_search']['favorites']) ? ' checked="checked"' : '', ' class="check" /> <label for="favorites">', $txt['search_favorites'], '</label>
					</div>
					<script type="text/javascript"><!-- // --><![CDATA[
				var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
			// ]]></script>
				</form>
			</div>
			<div id="adm_submenus">
				<ul class="dropmenu">';

		// Print out all the items in this tab.
		foreach ($context['arcade_tabs']['tabs'] as $tab)
			echo '
					<li>
						<a href="', $tab['href'], '" class="', !empty($tab['is_selected']) ? 'active ' : '', 'firstlevel">
							<span class="firstlevel">', $tab['title'], '</span>
						</a>
					</li>';

		echo '
				</ul>
			</div>
		</div>
	</div>
	<span class="lowerframe"><span></span></span>
	<script type="text/javascript"><!-- // --><![CDATA[
		var oArcadeHeaderToggle = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: ', empty($options['arcade_panel_collapse']) ? 'false' : 'true', ',
			aSwappableContainers: [
				\'arcade_panel\'
			],', (version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '
			aSwapImages: [
				{
					sId: \'arcade_toggle\',
					srcExpanded: smf_images_url + \'/collapse.gif\',
					altExpanded: ' . JavaScriptEscape($txt['upshrink_description']) . ',
					srcCollapsed: smf_images_url + \'/expand.gif\',
					altCollapsed: ' . JavaScriptEscape($txt['upshrink_description']) . '
				}
			],' : '
			aSwapImages: [
				{
					sId: \'arcade_toggle\',
					altExpanded: ' . JavaScriptEscape($txt['upshrink_description']) . ',
					altCollapsed: ' . JavaScriptEscape($txt['upshrink_description']) . '
				}
			],'), '
			oThemeOptions: {
				bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
				sOptionName: \'arcade_panel_collapse\',
				sSessionVar: ', JavaScriptEscape($context['session_var']), ',
				sSessionId: ', JavaScriptEscape($context['session_id']), '
			},
			oCookieOptions: {
				bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
				sCookieName: \'arcadeupshrink\'
			}
		});', (version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '' : '
		var checkArcadeSearchContainer = readArcadeCookie("checkArcadeSearchContainer") != "" ? readArcadeCookie("checkArcadeSearchContainer") : document.getElementById("arcade_panel").style.display;
		if (checkArcadeSearchContainer === "none")
		{
			$("#arcade_toggle").toggleClass("toggle_down", true);
			writeArcadeCookie("checkArcadeSearchContainer", "", 1);
		}
		else
		{
			$("#arcade_toggle").toggleClass("toggle_up", true);
			writeArcadeCookie("checkArcadeSearchContainer", "none", 1);
		}'), '
	// ]]></script>';
	}
}

function template_arcade_login()
{
	global $context, $scripturl, $txt, $user_info, $modSettings;

	// message to tell guests that they must log in
	if ($context['arcade_smf_version'] == 'v2.0')
	{
		echo '
		<div style="padding-top: 25px;"><span></span></div>
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<div class="centertext" style="border: 1px solid;padding: 5px;border-radius: 3px;width: 30%">
				<div class="cat_bar">
					<h3 class="catbg centertext">', $txt['arcade_email_' . $context['arcade_sub'] . '_error'], '</h3>
				</div>
				<div class="windowbg">
					<div class="padding">
						<div class="noticebox">', $txt['arcade_email_' . $context['arcade_sub'] . '_error_msg'], '</div>
						<div style="display: table;border: 0px;" class="centertext ssi_table">
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right;"><label for="user">', $txt['username'], ':</label>&nbsp;</div>
								<div style="display: table-cell;"><input type="text" id="user" name="user" size="30" value="', $user_info['username'], '" class="input_text" /></div>
							</div>
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right;"><label for="passwrd">', $txt['password'], ':</label>&nbsp;</div>
								<div style="display: table-cell;"><input type="password" name="passwrd" id="passwrd" size="30" class="input_password" /></div>
							</div>';

		// Open ID?
		if (!empty($modSettings['enableOpenID']))
			echo '
							<div style="display: table-row;">
								<div class="centertext" style="display: table-cell;width: 100%;"><strong>&mdash;', $txt['or'], '&mdash;</strong></div>
							</div>
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right;"><label for="openid_url">', $txt['openid'], ':</label>&nbsp;</div>
								<div style="display: table-cell;"><input type="text" name="openid_identifier" id="openid_url" class="input_text openid_login" size="17" /></div>
							</div>';

		echo '
							<div style="display: table-row;">
								<div style="display: table-cell;"><input type="hidden" name="cookielength" value="-1" /></div>
								<div class="centertext" style="display: table-cell;"><input type="submit" value="', $txt['login'], '" class="button_submit" /></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<div style="padding-top: 25px;"><span></span></div>';
	}
	else
	{
		echo '
		<div style="padding-top: 25px;"><span></span></div>
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<div class="centertext" style="border: 1px solid;padding: 5px;border-radius: 3px;width: 30%;">
				<div class="cat_bar">
					<h3 class="catbg centertext">', $txt['arcade_email_' . $context['arcade_sub'] . '_error'], '</h3>
				</div>
				<div class="windowbg">
					<div class="padding">
						<div class="noticebox">', $txt['arcade_email_' . $context['arcade_sub'] . '_error_msg'], '</div>
						<div style="display: table;border: 0px;" class="centertext ssi_table">
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right; border-spacing: 1px"><label for="user">', $txt['username'], ':</label>&nbsp;</div>
								<div style="display: table-cell;"><input type="text" id="user" name="user" size="30" value="', $user_info['username'], '" class="input_text"></div>
							</div>
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right; border-spacing: 1px;"><label for="passwrd">', $txt['password'], ':</label>&nbsp;</div>
								<div style="display: table-cell;"><input type="password" name="passwrd" id="passwrd" size="30" class="input_password"></div>
							</div>
							<div style="display: table-row;">
								<div style="display: table-cell;">
									<input type="hidden" name="cookielength" value="-1">
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
									<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '">
								</div>
								<div style="display: table-cell;"><input type="submit" value="', $txt['login'], '" class="button_submit"></div>
							</div>
						</div>
					</div>
				</div>
			</div>	
		</form>
		<div style="padding-top: 25px;"><span></span></div>';
	}
}


function template_arcade_below()
{
	global $arcade_version;

	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
	<div id="arcade_bottom" class="smalltext" style="text-align: center;">
		Powered by: <a href="http://web-develop.ca/index.php?page=arcade_license_BSD2" target="_blank">SMF Arcade ', $arcade_version, '</a> &copy; 2004-2017
	</div>';

}

?>