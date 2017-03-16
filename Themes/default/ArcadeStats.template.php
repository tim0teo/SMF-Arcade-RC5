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
	<div style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>
	<div class="cat_bar">
		<h3 class="catbg centertext" style="vertical-align: middle;">
			<img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['images_url'], '/gold.gif" alt="" />
			<span class="mediumtext" style="padding: 0px 6px 0px 0px;vertical-align: middle;">', $txt['arcade_stats'], '</span>
			<img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['images_url'], '/gold.gif" alt="" />
		</h3>
	</div>
	', $context['arcade_smf_version'] == 'v2.1' ? '
	<div class="game_table up_contain windowbg">' :
	'<span class="clear upperframe"><span>&nbsp;</span></span>
	<div class="roundframe">', '
		<div class="innerframe" style="border-radius: 5px;">';

	$alternate = false;

	// Most played games
	if (!empty($context['arcade']['statistics']['play']) > 0)
	{
		echo '
			<div style="padding-top: 10px;"><span></span></div>
			<div class="', !$alternate ? 'floatleft' : 'floatright', '" style="width: 48%;">
				<div>
					<h3 style="border-bottom: 1px dotted;"><img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" /><span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_most_played'], '</span></h3>
				</div>
				<div class="smalltext" style="padding-left: 5px;padding-right: 15px;">
					<span class="topslice"><span></span></span>
					<div class="content">
						<span class="stats">';

		foreach ($context['arcade']['statistics']['play'] as $game)
		{
			echo '
							<span>', $game['link'], '</span>
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
					<h3 style="border-bottom: 1px dotted;">
						<img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" /><span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_most_active'], '</span>
					</h3>
				</div>
				<div class="smalltext" style="padding-left: 5px;padding-right: 15px;">
					<span class="topslice"><span></span></span>
					<div class="content">
						<span class="stats">';


		foreach ($context['arcade']['statistics']['active'] as $game)
		{
			echo '
							<span>', $game['link'], '</span>
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
				<div style="padding-top: 10px;">
					<h3 style="border-bottom: 1px dotted;">
						<img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" /><span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_best_games'], '</span>
					</h3>
				</div>
				<div class="smalltext" style="padding-left: 5px;padding-right: 15px;">
					<span class="topslice"><span></span></span>
					<div class="content">
						<span class="stats">';

		foreach ($context['arcade']['statistics']['rating'] as $game)
		{
			echo '
							<span>', $game['link'], '</span>
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
				<div style="padding-top: 10px;">
					<h3 style="border-bottom: 1px dotted;">
						<img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" /><span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_best_players'], '</span>
					</h3>
				</div>
				<div class="smalltext" style="padding-left: 5px;padding-right: 15px;">
					<span class="topslice"><span></span></span>
					<div class="content">
						<span class="stats">';

		foreach ($context['arcade']['statistics']['champions'] as $member)
		{
			echo '
							<span>', $member['link'], '</span>
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
				<div style="padding-top: 10px;">
					<h3 style="border-bottom: 1px dotted;">
						<img src="', $settings['images_url'], '/gold.gif" class="icon" alt="" /><span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_longest_champions'], '</span>
					</h3>
				</div>
				<div class="smalltext" style="padding-left: 5px;padding-left: 15px;">
					<span class="topslice"><span></span></span>
					<div class="content">
						<span class="stats">';

		foreach ($context['arcade']['statistics']['longest'] as $game)
		{
			echo '
							<span>', $game['member_link'], ' (', $game['game_link'], ')</span>
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
		</div>
	</div>
	<span class="lowerframe"><span></span></span>
	<div style="padding-top: 15px;"><span></span></div>';
}
?>