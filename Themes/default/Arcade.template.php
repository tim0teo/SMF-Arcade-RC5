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
	<div class="cat_bar">
		<h3 class="catbg">
			<span class="floatleft">', $context['arcade_tabs']['title'], '</span>',
		(version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '
			<img id="arcade_toggle" class="floatright" src="' . $settings['images_url'] . '/collapse.gif' . '" alt="" title="' . $txt['upshrink_description'] . '" style="margin: 0 1ex; display: none;vertical-align: bottom;" />' : '
			<span id="arcade_toggle" class="floatright' . (empty($options['arcade_panel_collapse']) ? ' toggle_up' : ' toggle_down') . '" title="' . $txt['upshrink_description'] . '" style="margin: 0; vertical-align: bottom;"></span>'), '
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
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
			// ]]></script>
		</form>
	</div>
	<div id="adm_submenus"><ul class="dropmenu">';

		// Print out all the items in this tab.
		foreach ($context['arcade_tabs']['tabs'] as $tab)
			echo '
		<li>
			<a href="', $tab['href'], '" class="', !empty($tab['is_selected']) ? 'active ' : '', 'firstlevel">
				<span class="firstlevel">', $tab['title'], '</span>
			</a>
		</li>';

		echo '
	</ul></div>
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

	echo '
	<div id="arcade_top">';
}

function template_arcade_below()
{
	global $arcade_version;

	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
	</div>

	<div id="arcade_bottom" class="smalltext" style="text-align: center;">
		Powered by: <a href="http://web-develop.ca/index.php?page=arcade_license_BSD2" target="_blank">SMF Arcade ', $arcade_version, '</a> &copy; 2004-2015
	</div>';

}

?>