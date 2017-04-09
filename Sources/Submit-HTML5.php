<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	!!!
*/

// Get Game
function ArcadeHTML5GetGame()
{
	return GetGameInfo($_POST['game_name']);
}

function ArcadeHTML5Game()
{
	global $scripturl, $sourcedir, $context, $txt;

	require_once($sourcedir . '/ArcadeGame.php');

	if (isset($_POST['gameexit']) && is_numeric($_POST['gameexit']))
		$gameexit = (int)$_POST['gameexit'];

	if (isset($_POST['game']) && is_numeric($_POST['game']))
		$gameid = (int)$_POST['game'];
	else
		fatal_lang_error('arcade_submit_error', false);

	if (isset($_POST['score']) && is_numeric($_POST['score']))
		$score = (float) $_POST['score'];
	else
		fatal_lang_error('arcade_submit_error', false);

	if (isset($_POST['time']) && is_numeric($_POST['time']))
		$time = (int)$_POST['time'] > 0 ? (int)$_POST['time'] : time();
	else
		fatal_lang_error('arcade_submit_error', false);

	if (isset($_POST['game_name']))
		$gameName = ArcadeSpecialChars(strtolower(trim($_POST['game_name'])));
	else
		fatal_lang_error('arcade_submit_error', false);

	if (isset($_POST['gamesessid']) && !empty($_SESSION['arcade_html5_token']))
	{
		$gameToken = ArcadeSpecialChars($_POST['gamesessid']);
		$initialToken = $_SESSION['arcade_html5_token'];
		unset($_SESSION['arcade_html5_token']);
	}
	else
		fatal_lang_error('arcade_submit_error_session', false);

	if ($initialToken[1] == $gameToken)
	{
		$context['game'] = getGameInfo($gameid, false);
		$cheating = CheatingCheck();

		if (empty($cheating) && empty($_SESSION['arcade_check_' . $context['game']['id']]))
		{
			// 30 minutes max for playing a game
			if (empty($gameexit) && (time() - $initialToken[0]) > 1800)
			{
				fatal_lang_error('arcade_submit_error_session', false);
				return false;
			}

			$_SESSION['arcade_check_' . $context['game']['id']] = 'saved';
			if (empty($gameexit))
				ArcadeSubmit();

			if (isset($_POST['popup']) && $_POST['popup'] == 1)
				redirectexit($scripturl . '?action=arcade;sa=highscore;pop=1;game=' . $gameid. ';#commentform3');
			else
				redirectexit($scripturl . '?action=arcade;sa=highscore;game=' . $gameid. ';#commentform3');

		}
		elseif(!empty($_SESSION['arcade_check_' . $context['game']['id']]))
		{
			if (isset($_POST['popup']) && $_POST['popup'] == 1)
				redirectexit($scripturl . '?action=arcade;sa=highscore;pop=1;game=' . $gameid. ';#commentform3');
			else
				redirectexit($scripturl . '?action=arcade;sa=highscore;game=' . $gameid. ';#commentform3');
		}
		else
			fatal_lang_error('arcade_submit_error', false);
	}
	else
		fatal_lang_error('arcade_submit_error_session', false);

	return false;
}

// Get Score
function ArcadeHTML5Submit(&$game, $session_info)
{
	if (isset($_POST['score']) && is_numeric($_POST['score']))
		$score = (float) $_POST['score'];
	else
		return false;

	$cheating = CheatingCheck();

	return array(
		'cheating' => $cheating,
		'score' => $score,
		'start_time' => $session_info['start_time'],
		'duration' => time() - $session_info['start_time'],
		'end_time' => time(),
	);
}

function ArcadeHTML5Play(&$game, &$session_info)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc;

	// We store this session to check cheating later
	$session_info = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);
}

function ArcadeHTML5XMLPlay(&$game, &$session_info)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc;

	// We store this session to check cheating later
	$session_info = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);

	return true;
}

function ArcadeHTML5Html(&$game, $auto_start = true)
{
	global $txt, $context, $settings, $modSettings, $scripturl;

	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/swfobject.js" defer="defer"></script>
	<div id="game" style="margin: auto; width: ', $game['width'], 'px; height: ', $game['height'], 'px; ">
		', $txt['arcade_no_javascript'], '
	</div>

	<script type="text/javascript" defer="defer"><!-- // --><![CDATA[
		var play_url = smf_scripturl + "?action=arcade;sa=play;xml";
		var running = false;

		function arcadeRestart()
		{
			running = false;

			setInnerHTML(document.getElementById("game"), "', addslashes($txt['arcade_please_wait']), '");

			var i, x = new Array();

			x[0] = "game=', $game['id'] . '";
			x[1] = "', $context['session_var'], '=', $context['session_id'], '";

			arcadeAjaxSend(play_url, x.join("&"), ArcadeStart);

			return false;
		}

		function ArcadeStart()
		{
			if (running)
				return;

			running = true;

			setInnerHTML(document.getElementById("game"), "', addslashes($txt['arcade_html5']), '");
			var so = document.write("<object type="text/html" style="overflow: hidden;height: ' . ((int)$game['height'] + 50) . 'px;width: ' . ((int)$game['width'] + 50) . 'px;" data="' . $modSettings['gamesUrl'] . '/' . $game['directory'] . '/' . $game['file'] . '"></object>");
			so.write("game");
			so.document.close();

			return true;
		}
		window.onload = function() {
			document.getElementsByTagName("body")[0].style.overflow = "hidden";
			var divelement = document.getElementById("game");
			divelement.width = "100vw";
			divelement.height = "100vh";
			scrollTo(document.body, divelement.offsetTop, 100);
		};
		function escGameSmf() {
			window.location = "' . $scripturl . '?action=arcade;sa=highscore;game=' . $game['id'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
		}
		', $auto_start ? 'addLoadEvent(arcadeRestart);' : '', '
	// ]]></script>';
}

?>