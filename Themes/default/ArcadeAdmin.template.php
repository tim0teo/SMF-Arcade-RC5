<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_admin_main()
{
	global $context, $settings, $options, $txt, $modSettings, $arcade_version;

	echo '
	<div style="width: 49%" class="floatleft">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_latest_news'], '
			</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div id="arcade_news" style="overflow: auto; height: 18ex; padding: 0.5em;">', sprintf($txt['arcade_unable_to_connect'], 'web-develop.ca'), '</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<div style="width: 49%" class="floatright">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_status'], '
			</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div style="overflow: auto; height: 18ex; padding: 0.5em;">
				', $txt['arcade_installed_version'], ': <span id="arcade_installed_version">', $arcade_version, '</span><br />
				', $txt['arcade_latest_version'], ': <span id="arcade_latest_version">???</span>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<br style="clear: both" />
	<script type="text/javascript"><!-- // --><![CDATA[
		function setArcadeNews()
		{
			if (typeof(window.arcadeNews) == "undefined" || typeof(window.arcadeNews.length) == "undefined")
					return;

				var str = "<div style=\"margin: 4px; font-size: 0.85em;\">";

				for (var i = 0; i < window.arcadeNews.length; i++)
				{
					str += "\n	<div style=\"padding-bottom: 2px;\"><a href=\"" + window.arcadeNews[i].url + "\">" + window.arcadeNews[i].subject + "</a> on " + window.arcadeNews[i].time + "</div>";
					str += "\n	<div style=\"padding-left: 2ex; margin-bottom: 1.5ex; border-top: 1px dashed;\">"
					str += "\n		" + window.arcadeNews[i].message;
					str += "\n	</div>";
				}

				setInnerHTML(document.getElementById("arcade_news"), str + "</div>");
		}

		function setArcadeVersion()
		{
			if (typeof(window.arcadeCurrentVersion) == "undefined")
				return;

			setInnerHTML(document.getElementById("arcade_latest_version"), window.arcadeCurrentVersion);
		}
	// ]]></script>
	<script type="text/javascript" src="http://web-develop.ca/Themes/default/scripts/arcade_news.js?v=', urlencode($arcade_version), '" defer="defer"></script>';
}

function template_arcade_admin_maintenance()
{
	global $scripturl, $txt, $context, $settings;

	if ($context['maintenance_finished'])
		echo '
	<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
		', $txt['arcade_maintain_done'], '
	</div>';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['arcade_maintenance'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
			<div style="padding: 0.5em;">
				<ul>
					<li><a href="', $scripturl , '?action=admin;area=arcademaintenance;maintenance=fixScores;' . $context['session_var'] . '=', $context['session_id'], '">', $txt['arcade_maintenance_fixScores'], '</a></li>
					<li><a href="', $scripturl , '?action=admin;area=arcademaintenance;maintenance=updateGamecache;' . $context['session_var'] . '=', $context['session_id'], '">', $txt['arcade_maintenance_updateGamecache'], '</a></li>
					<li><a href="', $scripturl , '?action=admin;area=arcademaintenance;maintenance=onlinePurge;' . $context['session_var'] . '=', $context['session_id'], '">', $txt['arcade_maintenance_onlinePurge'], '</a></li>
					<li><a href="', $scripturl , '?action=admin;area=arcademaintenance;maintenance=downloadPurge;' . $context['session_var'] . '=', $context['session_id'], '">', $txt['arcade_maintenance_downloadPurge'], '</a></li>
				</ul>
			</div>
		<span class="botslice"><span></span></span>
	</div>';

}

function template_arcade_admin_maintenance_highscore()
{
	global $scripturl, $txt, $context, $settings;


	echo '
	<form name="category" action="', $scripturl, '?action=admin;area=arcademaintenance;sa=highscore" method="post">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_maintenance'], ' - ', $txt['arcade_maintenance_highscore'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
				<div style="padding: 0.5em;">
					<div>
						<input type="radio" name="score_action" value="older" />', $txt['arcade_remove_scores_older_than'], ' <input name="age" value="30" /> ', $txt['arcade_remove_scores_days'], '
					</div>
					<div>
						<input type="radio" name="score_action" value="all" />', $txt['arcade_remove_all_scores'], '
					</div>
					<div style="margin: 1ex;text-align: right;">
						<input class="button_submit" type="submit" name="clear_score" value="', $txt['arcade_remove_now'], '" />
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>

		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_arcade_admin_maintenance_category()
{
	global $scripturl, $txt, $context, $settings;

	if ((!empty($_REQUEST['maintenance'])) && $_REQUEST['maintenance'] == 'done')
		echo '
	<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
		', $txt['arcade_maintain_done'], '
	</div>';

	echo '
	<form name="category" action="', $scripturl, '?action=admin;area=arcademaintenance;sa=category" method="post">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_maintenance'], ' - ', $txt['arcade_maintenance_category'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
				<div style="padding: 0.5em;">
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="cat_action" value="undefault" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cats_undefault'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="cat_action" value="default" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cats_default'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="cat_action" value="peruse" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cats_peruse'], '</span>
					</div>
					<div style="padding-bottom: 3px;padding-top: 15px;">
						<span style="padding-right: 5px;">', $txt['arcade_admin_opt_cat_title'], '</span>
						', ArcadeAdminCategoryDropdown(), '
					</div>
					<div style="margin: 1ex;text-align: right;">
						<input class="button_submit" type="submit" name="clear_score" value="', $txt['arcade_commence_now'], '" />
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>

		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_arcade_admin_category_list()
{
	global $scripturl, $txt, $context, $settings;

	echo '
	<form name="category" action="', $scripturl, '?action=admin;area=arcadecategory;save" method="post">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_categories'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div style="padding: 0.5em;">
				<div class="centertext" style="border: 0px;width: 100%;">';

	foreach ($context['arcade_category'] as $category)
	{
		echo '
					<div style="display: table-row;width: 100%;">
						<span class="centertext" style="display: table-cell;padding: 4px;vertical-align: top;width: 15%;margin-top: 5px;">
							<input id="cat', $category['id'], '" type="checkbox" name="category[', $category['id'], ']" value="', $category['id'], '" style="check" />
						</span>
						<span style="display: table-cell;width: 25%;text-align: left;vertical-align: top;margin-top: 5px;padding: 4px;">
							<input type="text" name="category_order[', $category['id'], ']" value="', $category['order'], '" style="width: 100%;" />
						</span>
						<span style="display: table-cell;width: 60%;padding: 4px;vertical-align: top;">
							<a href="', $category['href'], '">', $category['name'], '</a>
						</span>
					</div>';
	}

	echo '
				</div>
				<input class="button_submit" type="submit" name="save_settings" value="', $txt['arcade_save_category'], '" />
			</div>
			<span class="botslice"><span></span></span>
		</div>

		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_arcade_admin_category_edit()
{
	global $scripturl, $txt, $context, $settings;

	echo '
	<form name="category" action="', $scripturl, '?action=admin;area=arcadecategory;sa=save" method="post">
		<input type="hidden" name="category" value="', $context['category']['id'], '" />
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_categories'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div style="padding: 0.5em;">
				<div style="border: 0px;width: 100%;" class="centertext">
					<div style="display: table-row;width: 100%;">
						<span style="text-align:left;display: table-cell;padding: 4px;width: 20%;">', $txt['category_name'], '</span>
						<span style="display: table-cell;padding: 20px 4px 4px 4px;width: 80%;">
							<input size="50" type="text" name="category_name" value="', $context['category']['name'], '" />
						</span>
					</div>
					<div style="display: table-row;width: 100%;">
						<span style="text-align:left;display: table-cell;width: 20%;padding: 4px;">', $txt['arcade_category_permission_allowed'], '</span>
						<span style="display: table-cell;padding: 20px 4px 4px 4px;width: 80%;">';

	foreach ($context['groups'] as $group)
		echo '
							<label for="groups_', $group['id'], '">
								<input style="display: inline;" type="checkbox" name="groups[]" value="', $group['id'], '" id="groups_', $group['id'], '"', $group['checked'] ? ' checked="checked"' : '', ' class="check" />
								<span', $group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['pgroups_post_group'] . '"' : '', '>', $group['name'], '</span>
							</label>';

	echo '
							<br /><br /><span style="padding-top: 20px;"></span><i>', $txt['check_all'], '</i> <input type="checkbox" onclick="invertAll(this, this.form, \'groups[]\');" class="check" /><br />
							<br />
						</span>
					</div>
				</div>
				<input class="button_submit" type="submit" name="save_settings" value="', $txt['arcade_save_category'], '" />
			</div>
			<span class="botslice"><span></span></span>
		</div>

		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_arcadeadmin_above()
{
	global $scripturl, $txt, $modSettings, $context, $settings, $arcade_version;
}

function template_arcadeadmin_below()
{
	global $arcade_version;

	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
	<div id="arcade_bottom" class="smalltext" style="text-align: center;">
		Powered by: <a href="http://web-develop.ca/index.php?page=arcade_license_BSD2" target="_blank">SMF Arcade ', $arcade_version, '</a> &copy; 2004-2017
	</div>';

}

?>