<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

/*  Above reports list  */
function template_arcade_reports_above()
{
	global $txt, $context, $settings;
	$context['arcade']['reports'] = array();

	echo '
	<div style="text-align:left;">
		<h3 class="catbg">
			<span class="left"></span>
			<span class="right"></span>
			<span style="float:left;border:0px;background: url(', $settings['actual_theme_url'], '/images/theme/main_block.png) no-repeat 0% -160px;">&nbsp;</span>
			<span>', $txt['pdl_admin_reports'], '</span>
		</h3>
	</div>';

}

/*  Reports List  */
function template_arcade_reports()
{
	global $scripturl, $txt, $context, $settings, $sourcedir, $modSettings;
	if (empty($modSettings['arcadeEnableDownload']))
		$modSettings['arcadeEnableDownload'] = false;

	echo '
	<div style="text-align:center;"><br /><br />
		<h3 class="catbg2 smalltext" style="text-align:center;">
			<span class="left"></span>
			<span class="right"></span>
			<span style="float:left;border:0px;background: url(', $settings['actual_theme_url'], '/images/theme/main_block.png) no-repeat 0% -160px;">&nbsp;</span>
			', $txt['arcade_pdl_reps'], '
		</h3><br /><br /><br />
	</div>
	<div class="centertext" style="text-align:center;"><br />
		<form method="post" action="', $context['post_url'] ,'" accept-charset="' . $context['character_set'] . '">
			<table cellspacing="2" cellpadding="5" class="table_grid centertext" border="4">
				<tr>
					<th>', $txt['pdl_test'], '</th>
					<th>', $txt['pdl_reports_id'], '</th>
					<th>', $txt['pdl_reports_name'], '</th>
					<th>', $txt['pdl_reports_userid'], '</th>
					<th>', $txt['pdl_reports_year'], '</th>
					<th>', $txt['pdl_reports_day'], '</th>';

	if ($modSettings['arcadeEnableDownload'] == true)
		echo '
					<th>', $txt['pdl_dl_status'], '</th>
					<th>', $txt['pdl_reports_dcount'], '</th>
					<th>', $txt['pdl_reports_toggle'], '</th>';

	echo '
					<th>', $txt['pdl_reports_delete'], '</th>
				</tr>';

	foreach ($context['arcade']['game_reports'] as $game)
	{
		$play = '<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $game['gameid'] . '">' . $txt['pdl_listplay'] . '</a>';
		$status = $txt['pdl_dl_enabled'];
		if ((int)$game['disable'] > 0)
			$status = $txt['pdl_dl_disabled'];

		echo '
				<tr>
					<th>', $play, '</th>
					<th>', $game['gameid'], '</th>
					<th><a href="', $game['edit_game'], '">', $game['name'], '</a></th>
					<th><a href="', $game['user_profile'], '">', $game['user_name'], '</a></th>
					<th>', $game['year'], '</th>
					<th>', $game['day'], '</th>';

		if ($modSettings['arcadeEnableDownload'] == true)
			echo '
					<th>', $status, '</th>
					<th>', $game['count'], '</th>
					<th><input type="checkbox" name="toggle[]" value="' . $game['gameid'] . '" class="check" /></th>';


		echo '
					<th><input type="checkbox" name="delete[]" value="' . $game['gameid'] . '" class="check" /></th>
				</tr>';
	}

		echo '
			</table><br /><br />
			<table border="0" cellspacing="0" cellpadding="4" width="100%">
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="text-align:right;">
						<strong>',$txt['pdl_maintain1'],'</strong>&nbsp;
						<input type="checkbox" name="maintain" value="' . $txt['pdl_maintain2'] . '" class="check" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="submit" name="'. $txt['pdl_submit']. '" value="'. $txt['pdl_submit']. '"'. (!empty($context['save_disabled']) ? ' disabled="disabled"' : ''). ' />
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="'. $context['session_id']. '" />
		</form>
	</div>';
}

/* Forum copyright */
function template_arcade_reports_below()
{
	/* Add more logo's and breaks as required */
	global $txt;
	//echo '<div style="text-align:center">', $txt['pdl_arcade_copyright'], '<br /></div>';
}
?>