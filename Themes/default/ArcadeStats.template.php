<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_statistics()
{
	global $scripturl, $txt, $context, $settings;

	echo '
	<div class="title_bar" style="opacity: 0.7;height: 2em;-moz-border-radius: 5px;border-radius: 5px;">
		<h3 class="titlebg centertext" style="-moz-border-radius: 5px;border-radius: 5px;">
			<img style="padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['images_url'], '/gold.gif" alt="" />
			<span class="mediumtext">', $txt['arcade_stats'], '&nbsp;&nbsp;</span>
			<img style="padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['images_url'], '/gold.gif" alt="" />
		</h3>
	</div><br />';

	$alternate = false;

	// Most played games
	if (!empty($context['arcade']['statistics']['play']) > 0)
	{
		echo '
	<div class="', !$alternate ? 'floatleft' : 'floatright', '" style="width: 48%;">
		<div>
			<h3>
				<img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" />
				', $txt['arcade_most_played'], '
			</h3>
		</div>
		<div class="windowbg2 smalltext" style="padding-left: 5px;padding-right: 15px;">
			<span class="topslice"><span></span></span>
			<div class="content">
				<span class="stats">';

		foreach ($context['arcade']['statistics']['play'] as $game)
		{
			echo '
					<span>
						', $game['link'], '
					</span>
					<span>';

			if (!empty($game['precent']))
				echo '
						<span class="left"></span>
							<span style="width: ', $game['precent'], 'px;" class="stats_bar"></span>
						<span class="right"></span>';

			echo '
						<span style="float: right;">' . $game['plays'] . '</span>
					</span><br />';
		}


		echo '
				</span>
				<div class="clear"></div>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';

		$alternate = !$alternate;

		if (!$alternate)
			echo '
		<div class="clear"></div>';
	}

	// Most active in arcade
	if (!empty($context['arcade']['statistics']['active']))
	{
		echo '
	<div class="', !$alternate ? 'floatleft' : 'floatright', '" style="width: 48%;">
		<div>
			<h3>
				<img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" />
				', $txt['arcade_most_active'], '
			</h3>
		</div>
		<div class="windowbg2 smalltext" style="padding-left: 5px;padding-right: 15px;">
			<span class="topslice"><span></span></span>
			<div class="content">
				<span class="stats">';


		foreach ($context['arcade']['statistics']['active'] as $game)
		{
			echo '
					<span>
						', $game['link'], '
					</span>
					<span>';

			if (!empty($game['precent']))
				echo '
						<span class="left"></span>
							<span style="width: ', $game['precent'], 'px;" class="stats_bar"></span>
						<span class="right"></span>';

			echo '
						<span style="float: right;">' . $game['scores'] . '</span>
					</span><br />';
		}

		echo '
				</span>
				<div class="clear"></div>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';


		$alternate = !$alternate;

		if (!$alternate)
			echo '
		<div class="clear"></div>';
	}

	// Top rated games
	if (!empty($context['arcade']['statistics']['rating']))
	{
		echo '
	<div class="', !$alternate ? 'floatleft' : 'floatright', '" style="width: 48%;padding-top: 15px;">
		<div>
			<h3>
				<img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" />
				', $txt['arcade_best_games'], '
			</h3>
		</div>
		<div class="windowbg2 smalltext" style="padding-left: 5px;padding-right: 15px;">
			<span class="topslice"><span></span></span>
			<div class="content">
				<span class="stats">';

		foreach ($context['arcade']['statistics']['rating'] as $game)
		{
			echo '
					<span>
						', $game['link'], '
					</span>
					<span>';

			if (!empty($game['precent']))
				echo '
						<span class="left"></span>
							<span style="width: ', $game['precent'], 'px;" class="stats_bar"></span>
						<span class="right"></span>';

			echo '
						<span style="float: right;">' . $game['rating'] . '</span>
					</span><br />';
		}

		echo '
				</span>
				<div class="clear"></div>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';

		$alternate = !$alternate;

		if (!$alternate)
			echo '
		<div class="clear"></div>';
	}

	// Best players by champions
	if (!empty($context['arcade']['statistics']['champions']))
	{
		echo '
	<div class="', !$alternate ? 'floatleft' : 'floatright', '" style="width: 48%;padding-top: 5px;">
		<div>
			<h3>
				<img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" />
				', $txt['arcade_best_players'], '
			</h3>
		</div>
		<div class="windowbg2 smalltext" style="padding-left: 5px;padding-right: 15px;">
			<span class="topslice"><span></span></span>
			<div class="content">
				<span class="stats">';

		foreach ($context['arcade']['statistics']['champions'] as $member)
		{
			echo '
					<span>
						', $member['link'], '
					</span>
					<span>';

			if (!empty($member['precent']))
				echo '
						<span class="left"></span>
							<span style="width: ', $member['precent'], 'px;" class="stats_bar"></span>
						<span class="right"></span>';

			echo '
						<span style="float: right;">' . $member['champions'] . '</span>
					</span><br />';
		}

		echo '
				</span>
				<div class="clear"></div>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';

		$alternate = !$alternate;

		if (!$alternate)
			echo '
		<div class="clear"></div>';
	}

	if (!empty($context['arcade']['statistics']['longest']))
	{
		echo '
	<div class="', !$alternate ? 'floatleft' : 'floatright', '" style="width: 48%;padding-top: 15px;">
		<div>
			<h3>
				<img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" />
				', $txt['arcade_longest_champions'], '
			</h3>
		</div>
		<div class="windowbg2 smalltext" style="padding-left: 5px;padding-left: 15px;">
			<span class="topslice"><span></span></span>
			<div class="content">
				<span class="stats">';

		foreach ($context['arcade']['statistics']['longest'] as $game)
		{
			echo '
					<span>
						', $game['member_link'], ' (', $game['game_link'], ')
					</span>
					<span>';

			if (!empty($game['precent']))
				echo '
						<span class="left"></span>
							<span style="width: ', $game['precent'], 'px;" class="stats_bar"></span>
						<span class="right"></span>';

			echo '
						<span style="float: right;">', $game['current'] ? '<strong>' . $game['duration'] . '</strong>' : $game['duration'], '</span>
					</span><br />';
		}

		echo '
				</span>
				<div class="clear"></div>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';

		$alternate = !$alternate;

		if (!$alternate)
			echo '
		<div class="clear"></div>';
	}

	if ($alternate)
			echo '
		<div class="clear"></div>';
	echo '
		<div class="smalltext" style="text-align:left;padding:16px 0px 3px 4px;">
			<ul class="dropmenu">';
	// Print out all the items in this tab.
	foreach ($context['arcade_tabs']['tabs'] as $tab)
	{
		echo '
				<li>
					<a href="', $tab['href'], '" class="', !empty($tab['is_selected']) ? 'active ' : '', 'firstlevel">
						<span class="firstlevel">', $tab['title'], '</span>
					</a>
				</li>';
	}
	echo '
			</ul>
		</div><br /><br />';
}
?>