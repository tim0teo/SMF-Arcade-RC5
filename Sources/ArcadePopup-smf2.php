<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
/*
   This part can be used in a portal block, BBCode, button from arcade menu, etc.  using iframe
   although I do not suggest bbCodes because of too many possible pages open on your server from one ip
   which may cause your server firewall to block said ip for 24 hrs+
   The necessary iframe code is shown during install. If you need this code please visit:  http://web-develop.ca
*/
if (!defined('SMF'))
	die('Hacking attempt...');
/*   This file handles the popup in iframe option for the arcade.
      use iframe with url: '?action=arcade;sa=popup;game=GAMEID' - GAMEID is game id number, game name or game file name and represents which game will be displayed in the iframe.

   void ArcadePopup()
      - Play Game Popup in Iframe option for SMF Arcade v2.5
*/
function ArcadePopup()
{
	global $sourcedir, $scripturl, $db_prefix, $context, $smcFunc, $modSettings, $boardurl, $settings, $txt, $settings;
	$context['show_pm_popup'] = false;
	$arcadeplay = false;

	if (empty($modSettings['arcadeEnabled']))
		die($txt['pdl_down'].'<div style="text-align:center;">'.$txt['pdl_erroricon'].$txt['pdl_disabled'].'</div>');

	/* Do we have permission?  */
	isAllowedTo('arcade_view');
	$check1 = ($_SERVER["REQUEST_URI"]);
	$check2 = ';game=RAND';
	$check3 = ';game=PLAY';
	$checkrev2 = str_replace( '/index.php?action=arcade;sa=popup;game=', '', $check1);
	$checkrev3 = str_replace( '.swf', '', $checkrev2);
	$checkrev2 = str_replace( 'RAND', '', $checkrev3);
	$context['page_title'] = 'SMF Arcade Popup';
	$game1 = false;
	$game_enabled = false;
	$ok = 1;
	$game_name1 = $checkrev2.".swf";
	$game_name2 = $checkrev2;
	$game1 = (int)$game_name2;
	$id_of_game = (!empty($_REQUEST['game']) ? (int) $_REQUEST['game'] : 0);
	$gameInfo = array();
	$rax = array();
	$result = array();
	$request = array();
	$replay = array();
	$dimension = array();
	if (($game1 == false) && ($game_name2 == true))
	{
		$result = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.enabled, game.game_file
			FROM {db_prefix}arcade_games AS game
			WHERE game.enabled > 0
			ORDER BY game.id_game',
			array('amt' => $ok,)
		);

		while ($rax = $smcFunc['db_fetch_assoc']($result))
		{
			$gamex = 1;
			$gamey = 2;
			if (($rax['game_name']) && $game_name2)
			{
				$gamex = strtolower($rax['game_name']);
				$gamey = strtolower($game_name2);
			}

			if ($rax['game_name'] == $game_name2)
				$id_of_game = $rax['id_game'];
			elseif ($gamex == $gamey)
				$id_of_game = $rax['id_game'];
			elseif ($rax['game_file'] == $game_name1)
				$id_of_game = $rax['id_game'];

		}

		$smcFunc['db_free_result']($result);
	}

	if ((strstr($check1,$check3)))
	{
		$arcadeplay = true;
		$id_of_game = 0;
	}

	if ($id_of_game == 0)
		$check1 = ';game=RAND';

	$result = $smcFunc['db_query']('', '
		SELECT game.id_game, game.enabled
		FROM {db_prefix}arcade_games AS game
		WHERE enabled = 1
		ORDER BY RAND()
		LIMIT {int:amt}',
        array('amt' => $ok,)
	);

	while ($rax = $smcFunc['db_fetch_assoc']($result))
		$rand_game = $rax['id_game'];

	if ((strstr($check1,$check2)))
		$id_of_game = $rand_game;

	$smcFunc['db_free_result']($result);

	$id_of_game = (int)$id_of_game;
	if ($id_of_game < 1)
		$id_of_game = $rand_game;

	if (!$context['game'] = $id_of_game)
		die($txt['pdl_down'].'<div style="text-align:center;">'.$txt['pdl_erroricon'].$txt['pdl_notfound'].'</div>');

	$search1 = 'id_game ='. $id_of_game;

	/* query the db for game data */
	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.game_directory, game.game_file, game.thumbnail, game.enabled, game.extra_data
		FROM {db_prefix}arcade_games AS game
		WHERE ' . $search1 .' && enabled = 1
		ORDER BY game.id_game
		LIMIT 1',
		array('search' => $id_of_game,)
	);

	while ($gameInfo = $smcFunc['db_fetch_assoc']($request))
	{
		$dimension = !empty($gameInfo['extra_data']) ? unserialize($gameInfo['extra_data']) : array();
		$gamefile_name = $gameInfo['game_file'];
		$gamename_name = $gameInfo['game_name'];
		$gamedirectory = $gameInfo['game_directory'];
		$game_pic = $gameInfo['thumbnail'];
		$game_enabled = $gameInfo['enabled'];
		if (empty($dimension['width']))
			$dimension['width'] = 0;

		if (empty($dimension['height']))
			$dimension['height'] = 0;

		$game_width =  $dimension['width'];
		$game_height = $dimension['height'];
	}

	$smcFunc['db_free_result']($request);

	if ($game_enabled == true)
	{
		/* Check for subdirectory */
		if (!empty($gamedirectory))
			$gameurl = $modSettings['gamesUrl'] . '/' . $gamedirectory . '/';
		else
			$gameurl = $modSettings['gamesUrl'] . '/';

		$_REQUEST['game'] = (int)$id_of_game;
		$gameurl2 = str_replace($boardurl, "", $gameurl);
		$check_block = 0;
		$check_block = !empty($_REQUEST['block']) ? (int) $_REQUEST['block'] : 0;
		$extra = false;
		if ($check_block == 1)
			$extra = ';block=1';

		$goto = 'index.php?action=arcade;sa=play;game=' . $id_of_game . ';gamepopup=1' . $extra;
		$width2 = ((int)$game_width + 50);
		$height2 = ((int)$game_height + 50);
		/*  Please leave the arcade copyright displayed.
			Buttons, text and/or images can be added to the bottom of code but the iframe height may have to be increased to view them - example... ((int)$game_height+40)
		*/
		$dims = array();
		if ($arcadeplay == true)
			$goto = 'Themes/default/images/arc_icons/game_popup_saver.swf'; $game_width=700;$game_height=400;

		$dims['width'] = $game_width;
		$dims['height'] = $game_height;
		echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title>', $context['forum_name_html_safe'], '</title>
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css" />
		<style type="text/css">
			body {
				padding: 0px 0px 0px 0px; background: transparent;
			}
			body, td, th, .normaltext {
				font-size: x-small;
			}
			.smalltext {
				font-size: xx-small;
			}
		</style>
	</head>';

		$check_block = !empty($_REQUEST['block']) ? (int) $_REQUEST['block'] : 0;
		if ($check_block == 1)
			echo '
	<!--[if IE]>
		<body class="windowbg" id="html_page1">
			<div>
	<![endif]-->
	<!--[if !IE]><!-->
		<body style="background-color: transparent;" id="html_page1" class="clear">
			<div style="background-color: transparent;">
	<!--<![endif]-->';
		else
			echo '
	<body class="windowbg" id="html_page1">
			<div>';

		echo '
				<p style="text-align:center;">
					<iframe width="'.((int)$game_width+30).'" height="'.((int)$game_height+30).'" src="'.$goto.'" style="allowTransparency:true; marginwidth:0; marginheight:0; hspace:0; vspace:0; overflow:hidden;" scrolling="no" frameborder="0"></iframe>
				</p>
				<a href="javascript:location.reload(true);">'.$context['game']['name'].'</a>';
		echo '
			</div>
	</body>
</html>';
	}
	else
		die($txt['pdl_down'].'<div style="text-align:center;">'.$txt['pdl_erroricon'].$txt['pdl_gamedisable'].'</div>');

	die();
}
?>