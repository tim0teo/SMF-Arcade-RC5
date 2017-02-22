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
	<div class="centertext" style="padding-bottom: 3em;">
		<h3 class="catbg smalltext">
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
	<div class="centertext">
		<h3 class="catbg2 smalltext" style="text-align:center;border: 1px solid;">
			', $txt['arcade_pdl_reps'], '
		</h3>
	</div>
	<div class="centertext" style="text-align:center;border: 1px solid;">
		<form method="post" action="', $context['post_url'] ,'" accept-charset="' . $context['character_set'] . '">
			<table style="border-spacing: 2px;border-collapse: separate;border: 4px;" class="table_grid centertext">
				<tr>
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_test'], '</th>
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_reports_id'], '</th>
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_reports_name'], '</th>
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_reports_userid'], '</th>
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_reports_year'], '</th>
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_reports_day'], '</th>';

	if ($modSettings['arcadeEnableDownload'] == true)
		echo '
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_dl_status'], '</th>
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_reports_dcount'], '</th>
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_reports_toggle'], '</th>';

	echo '
					<th style="padding: 4px;border-bottom: 1px dotted;">', $txt['pdl_reports_delete'], '</th>
				</tr>';

	foreach ($context['arcade']['game_reports'] as $game)
	{
		$play = '<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $game['gameid'] . '">' . $txt['pdl_listplay'] . '</a>';
		$status = $txt['pdl_dl_enabled'];
		if ((int)$game['disable'] > 0)
			$status = $txt['pdl_dl_disabled'];

		echo '
				<tr>
					<th style="padding: 4px;">', $play, '</th>
					<th style="padding: 4px;">', $game['gameid'], '</th>
					<th style="padding: 4px;"><a href="', $game['edit_game'], '">', $game['name'], '</a></th>
					<th style="padding: 4px;"><a href="', $game['user_profile'], '">', $game['user_name'], '</a></th>
					<th style="padding: 4px;">', $game['year'], '</th>
					<th style="padding: 4px;">', $game['day'], '</th>';

		if ($modSettings['arcadeEnableDownload'] == true)
			echo '
					<th style="padding: 4px;">', $status, '</th>
					<th style="padding: 4px;">', $game['count'], '</th>
					<th style="padding: 4px;"><input type="checkbox" name="toggle[]" value="' . $game['gameid'] . '" class="check" /></th>';


		echo '
					<th style="padding: 4px;"><input type="checkbox" name="delete[]" value="' . $game['gameid'] . '" class="check" /></th>
				</tr>';
	}

		echo '
			</table><br /><br />
			<table style="border-collapse: collapse;border: 0px;width: 100%;">
				<tr>
					<td style="padding: 4px;">&nbsp;</td>
				</tr>
				<tr>
					<td style="padding: 4px;text-align:right;border: 1px solid;">
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