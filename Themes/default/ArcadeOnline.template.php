 <?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_online()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Display the table header and linktree.
	echo '
	<div class="main_section" id="whos_online">
		<form action="', $scripturl, '?action=arcade;sa=online" method="post" id="whoFilter" accept-charset="', $context['character_set'], '">
			<div class="title_bar">
				<h4 class="titlebg margin_lower">', $txt['arcade_online_title'], '</h4>
			</div>
			<div class="topic_table" id="mlist">
				<div class="pagesection">
					<div class="pagelinks floatleft">', $txt['pages'], ': ', $context['page_index'], '</div>';
		echo '
					<div class="selectbox floatright">', $txt['who_show1'], '
						<select name="show_top" onchange="document.forms.whoFilter.show.value = this.value; document.forms.whoFilter.submit();">';

		foreach ($context['show_methods'] as $value => $label)
			echo '
							<option value="', $value, '" ', $value == $context['show_by'] ? $context['arcade_selected'] : '', '>', $label, '</option>';
		echo '
						</select>
						<noscript>
							<input type="submit" name="submit_top" value="', $txt['go'], '" class="button_submit" />
						</noscript>
					</div>
				</div>
				<table class="table_grid" style="border-spacing: 0;border-collapse: collapse;width: 100%;">
					<thead>
						<tr class="catbg">
							<th scope="col" class="lefttext first_th" style="width: 40%;">
								<a href="', $scripturl, '?action=arcade;sa=online;start=', $context['start'], ';show=', $context['show_by'], ';sort=user;', $context['arcade_join'] != 'disjoin' && $context['sort_by'] == 'user' ? 'join=disjoin;' : ($context['sort_by'] == 'user' ? 'join=join;' : ''), $context['sort_direction'] != 'down' && $context['sort_by'] == 'user' ? 'asc' : '', '" rel="nofollow">', $txt['who_user'], ' ', $context['sort_by'] == 'user' ? '<img src="' . $settings['images_url'] . '/sort_' . ($context['arcade_join'] != 'disjoin' ? 'up' : 'down') . '.gif" alt="" />' : '', '</a>
								<span style="padding-left: 5%;">
									<a href="', $scripturl, '?action=arcade;sa=online;start=', $context['start'], ';show=', $context['show_by'], ';sort=user', $context['sort_direction'] != 'down' && $context['sort_by'] == 'user' ? '' : ';asc', '" rel="nofollow">', $txt['arcade_coalesce'], ' ', $context['sort_by'] == 'user' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
								</span>
							</th>
							<th scope="col" class="lefttext" style="width: 10%;padding-left: 7px;"><a href="', $scripturl, '?action=arcade;sa=online;start=', $context['start'], ';show=', $context['show_by'], ';sort=time', $context['sort_direction'] == 'down' && $context['sort_by'] == 'time' ? ';asc' : '', '" rel="nofollow">', $txt['who_time'], ' ', $context['sort_by'] == 'time' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></th>
							<th scope="col" class="lefttext last_th" style="width: 50%;padding-left: 7px;">', $txt['who_arcade_action'], '</th>
						</tr>
					</thead>
					<tbody>';

	// For every member display their name, time and action (and more for admin).
	$alternate = 0;

	foreach ($context['members'] as $member)
	{
		// $alternate will either be true or false. If it's true, use "windowbg2" and otherwise use "windowbg".
		echo '
						<tr class="windowbg', $alternate ? '2' : '', '">
							<td style="width: 40%;">';

		echo '
								<span class="member', $member['is_hidden'] ? ' hidden' : '', '">
									', $member['is_guest'] ? $member['name'] : '<a href="' . $member['href'] . '" title="' . $txt['profile_of'] . ' ' . $member['name'] . '"' . (empty($member['color']) ? '' : ' style="color: ' . $member['color'] . '"') . '>' . $member['name'] . '</a>', '
								</span>';

		if (!empty($member['ip']))
			echo '
								(<a href="' . $scripturl . '?action=', ($member['is_guest'] ? 'trackip' : 'profile;area=tracking;sa=ip;u=' . $member['id']), ';searchip=' . $member['ip'] . '">' . $member['ip'] . '</a>)';

		echo '
							</td>
							<td style="white-space: nowrap;width: 10%;">', $member['time'], '</td>
							<td style="width: 50%;">', $member['action'], '</td>
						</tr>';

		// Switch alternate to whatever it wasn't this time. (true -> false -> true -> false, etc.)
		$alternate = !$alternate;
	}

	// No members?
	if (empty($context['members']))
	{
		echo '
						<tr class="windowbg2">
							<td colspan="3" class="centertext">
							', $txt['arcade_no_online_' . ($context['show_by'] == 'guests' ? 'guests' : 'members')], '
							</td>
						</tr>';
	}

	echo '
					</tbody>
				</table>
			</div>
			<div class="pagesection" style="clear: both;">
				<div class="pagelinks floatleft">', $txt['pages'], ': ', $context['page_index'], '</div>
			</div>
		</form>
	</div>
	<div style="clear: both;padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>';
}
?>