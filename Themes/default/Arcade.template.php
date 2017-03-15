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

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext" style="vertical-align: middle;">
			<span style="clear: right;">', $txt['arcade'], '</span>
		</h3>
	</div>
	', $context['arcade_smf_version'] == 'v2.1' ? '
	<div class="up_contain windowbg">' :
	'<span class="clear upperframe"><span>&nbsp;</span></span>
	<div class="roundframe">', '
		<div class="innerframe">
			<div id="arcade_panel"', empty($options['arcade_panel_collapse']) ? '' : ' style="display: none;"', '>';

		if (!empty($context['arcade']['notice']))
			echo '
				<span class="arcade_notice">', $context['arcade']['notice'], '</span><br />';

		echo '
				<form action="', $scripturl, '?action=arcade;sa=search" method="post">
					<input id="gamesearch" style="width: 440px;" type="text" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '" />&nbsp;<input class="button_submit" type="submit" value="', $txt['arcade_search'], '" />
					<div id="suggest_gamesearch" class="game_suggest"></div>
					<div id="search_extra">
						<input type="checkbox" id="favorites" name="favorites" value="1"', !empty($context['arcade_search']['favorites']) ? ' checked="checked"' : '', ' class="check" /> <label for="favorites">', $txt['search_favorites'], '</label>
					</div>
					<script type="text/javascript"><!-- // --><![CDATA[
				var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
			// ]]></script>
				</form>
			</div>
		</div>
	</div>', ($context['arcade_smf_version'] !== 'v2.1' ? '
	<span class="lowerframe"><span>&nbsp;</span></span>' : ''), '
	<div style="width: 100%;display: inline;">
		<div style="display: inline;">', template_button_strip($context['arcade_tabs'], 'left', array()), '</div>';

	if ((!empty($context['arcade']['stats'])) && $context['arcade']['stats']['games'] != 0)
			echo '
		<div class="smalltext" style="clear: right;padding:8px 7px 0px 0px;float: right;display: inline;">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</div>';

	echo '
	</div>', ($context['arcade_smf_version'] == 'v2.1' ? '
	<span class="lowerframe"><span>&nbsp;</span></span>' : ''), '
	<div style="clear: both;padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>';
}

function template_arcade_login()
{
	global $context, $scripturl, $txt, $user_info, $modSettings;

	// message to tell guests that they must log in
	if ($context['arcade_smf_version'] == 'v2.0')
	{
		echo '
		<div style="padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<div class="centertext" style="border: 1px solid;padding: 5px;border-radius: 3px;width: 30%">
				<div class="cat_bar">
					<h3 class="catbg centertext">', $txt['arcade_email_' . $context['arcade_sub'] . '_error'], '</h3>
				</div>
				<div class="windowbg">
					<div class="padding">
						<div class="noticebox">', $txt['arcade_email_' . $context['arcade_sub'] . '_error_msg'], '</div>
						<div style="padding-top: 15px;"><span></span></div>
						<div style="display: table;border: 0px;" class="centertext ssi_table">
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right;"><label for="user">', $txt['username'], ':</label>&nbsp;</div>
								<div style="display: table-cell;"><input type="text" id="user" name="user" size="30" value="', $user_info['username'], '" class="input_text" /></div>
							</div>
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right;padding-top: 5px;"><label for="passwrd">', $txt['password'], ':</label>&nbsp;</div>
								<div style="display: table-cell;padding-top: 5px;"><input type="password" name="passwrd" id="passwrd" size="30" class="input_password" /></div>
							</div>';

		// Open ID?
		if (!empty($modSettings['enableOpenID']))
			echo '
							<div style="display: table-row;">
								<div class="centertext" style="display: table-cell;width: 100%;padding-top: 5px;"><strong>&mdash;', $txt['or'], '&mdash;</strong></div>
							</div>
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right;"><label for="openid_url">', $txt['openid'], ':</label>&nbsp;</div>
								<div style="display: table-cell;"><input type="text" name="openid_identifier" id="openid_url" class="input_text openid_login" size="17" /></div>
							</div>';

		echo '
							<div style="display: table-row;">
								<div style="display: table-cell;"><input type="hidden" name="cookielength" value="-1" /></div>
								<div class="centertext" style="display: table-cell;padding-top: 5px;"><input type="submit" value="', $txt['login'], '" class="button_submit" /></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<div style="padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>';
	}
	else
	{
		echo '
		<div style="padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<div class="centertext" style="border: 1px solid;padding: 5px;border-radius: 3px;width: 30%;">
				<div class="cat_bar">
					<h3 class="catbg centertext">', $txt['arcade_email_' . $context['arcade_sub'] . '_error'], '</h3>
				</div>
				<div class="windowbg">
					<div class="padding">
						<div class="noticebox">', $txt['arcade_email_' . $context['arcade_sub'] . '_error_msg'], '</div>
						<div style="padding-top: 15px;"><span></span></div>
						<div style="display: table;border: 0px;" class="centertext ssi_table">
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right; border-spacing: 1px"><label for="user">', $txt['username'], ':</label>&nbsp;</div>
								<div style="display: table-cell;"><input type="text" id="user" name="user" size="30" value="', $user_info['username'], '" class="input_text"></div>
							</div>
							<div style="display: table-row;">
								<div style="display: table-cell;text-align: right; border-spacing: 1px;padding-top: 5px;"><label for="passwrd">', $txt['password'], ':</label>&nbsp;</div>
								<div style="display: table-cell;padding-top: 5px;"><input type="password" name="passwrd" id="passwrd" size="30" class="input_password"></div>
							</div>
							<div style="display: table-row;">
								<div style="display: table-cell;">
									<input type="hidden" name="cookielength" value="-1">
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
									<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '">
								</div>
								<div style="display: table-cell;padding-top: 5px;"><input type="submit" value="', $txt['login'], '" class="button_submit"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<div style="padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>';
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