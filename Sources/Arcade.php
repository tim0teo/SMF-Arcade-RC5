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

/*	This file handles Arcade and loads required files.

	void Arcade()
		- Loads correct file based on subaction

	void loadArcade([mode = normal])
		- Initializes Arcade
		- Called by Arcade() and Arcade Admin functions
*/

function Arcade()
{
	global $context, $scripturl, $txt, $sourcedir, $modSettings;

	// Do we have permission?
	isAllowedTo('arcade_view');

	// Load Arcade
	loadArcade('normal');

	// Fatal error if Arcade is disabled
	if (empty($modSettings['arcadeEnabled']))
		fatal_lang_error('arcade_disabled', false);

	// Information for actions (file, function, [permission])
	$subActions = array(
		// ArcadeArena.php
		'arena' => array('ArcadeArena.php', 'ArcadeMatchList'),
		'newMatch' => array('ArcadeArena.php', 'ArcadeNewMatch', 'arcade_create_match'),
		'newMatch2' => array('ArcadeArena.php', 'ArcadeNewMatch2', 'arcade_create_match'),
		'viewMatch' => array('ArcadeArena.php', 'ArcadeViewMatch'),
		// ArcadeList.php
		'list' => array('ArcadeList.php', 'ArcadeList'),
		'suggest' => array('ArcadeList.php', 'ArcadeXMLSuggest'),
		'search' => array('ArcadeList.php', 'ArcadeList'),
		'rate' => array('ArcadeList.php', 'ArcadeRate'),
		'favorite' => array('ArcadeList.php', 'ArcadeFavorite'),
		// ArcadeGame.php
		/* Game Popup in Iframe  */
		'popup' => array('ArcadePopup-smf2.php', 'ArcadePopup'),
		'play' => array('ArcadeGame.php', 'ArcadePlay', 'arcade_play'),
		'highscore' => array('ArcadeGame.php', 'ArcadeHighscore'),
		'save' => array('ArcadeGame.php', 'ArcadeSave_Guest'),
		// ArcadeStats.php
		'stats' => array('ArcadeStats.php', 'ArcadeStatistics'),
		'submit' => array('ArcadeGame.php', 'ArcadeSubmit'),
		// Advanced
		'download' => array('ArcadeDownload.php', 'ArcadeDownload'),
		'report' => array('ArcadeReport.php', 'ArcadeReport'),
		// IBP Submit
		'ibpverify' => array('Submit-ibp.php', 'ArcadeVerifyIBP'),
		'ibpsubmit2' => array('ArcadeGame.php', 'ArcadeSubmit'),
		'ibpsubmit3' => array('ArcadeGame.php', 'ArcadeSubmit'),
		// v2 Submit
		'v2Start' => array('Submit-v2game.php', 'ArcadeV2Start'),
		'v2Hash' => array('Submit-v2game.php', 'ArcadeV2Hash'),
		'v2Score' => array('Submit-v2game.php', 'ArcadeV2Score'),
		'v2Submit' => array('ArcadeGame.php', 'ArcadeSubmit'),
		// v3Arcade
		'vbSessionStart' => array('Submit-v3arcade.php', 'ArcadeVbStart'),
		'vbPermRequest' => array('Submit-v3arcade.php', 'ArcadeVbPermRequest'),
		'vbBurn' => array('ArcadeGame.php', 'ArcadeSubmit'),
	);

	if (empty($modSettings['arcadeArenaEnabled']))
		unset($subActions['arena'], $subActions['newMatch'], $subActions['newMatch2'], $subActions['viewMatch']);

	// Fix for broken games which do not send sa/do=submit
	if (isset($_POST['game']) && isset($_POST['score']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'submit';
	// Short urls like index.php?game=1 or index.php/game,1.html
	elseif (isset($_REQUEST['game']) && is_numeric($_REQUEST['game']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'play';
	elseif (isset($_REQUEST['match']) && is_numeric($_REQUEST['match']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'viewMatch';
	// Let Custom ("php games") do ajax/etc magic
	elseif (isset($_REQUEST['game']) && isset($_REQUEST['xml']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'custData';

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	$context['curved'] = strpos($modSettings['smfVersion'], '2.1') !== false ? true : $context['curved'];
	$context['arcade_tabs'] = array(
		'title' =>  $txt['arcade'],
		'tabs' => array(
			array(
				'href' => $scripturl . '?action=arcade',
				'title' => $txt['arcade'],
				'is_selected' => in_array($_REQUEST['sa'], array('play', 'list', 'highscore', 'submit', 'search')),
			),
		),
	);

	if (!empty($modSettings['arcadeArenaEnabled']))
		$context['arcade_tabs']['tabs'][] = array(
			'href' => $scripturl . '?action=arcade;sa=arena',
			'title' => $txt['arcade_arena'],
			'is_selected' => in_array($_REQUEST['sa'], array('arena', 'newMatch', 'newMatch2', 'viewMatch')),
		);

	$context['arcade_tabs']['tabs'][] = array(
		'href' => $scripturl . '?action=arcade;sa=stats',
		'title' => $txt['arcade_stats'],
		'is_selected' => in_array($_REQUEST['sa'], array('stats')),
	);

	if (!in_array($_REQUEST['sa'], array('highscore', 'comment')) && isset($_SESSION['arcade']['highscore']))
		unset($_SESSION['arcade']['highscore']);

	// Check permission if needed
	if (isset($subActions[$_REQUEST['sa']][2]))
		isAllowedTo($subActions[$_REQUEST['sa']][2]);

	require_once($sourcedir . '/' . $subActions[$_REQUEST['sa']][0]);
	!isset($_SESSION['current_cat']) ? $_SESSION['current_cat'] = 'all' : '';
    isset($_REQUEST['category']) ? $_SESSION['current_cat'] = $_REQUEST['category'] : $_REQUEST['category'] = $_SESSION['current_cat'];
	$_REQUEST['category'] = !empty($_REQUEST['current_cat']) ? ArcadeSpecialChars($_REQUEST['current_cat']): $_SESSION['current_cat'];
	$subActions[$_REQUEST['sa']][1]();
}


function template_main()
{
	/*
	** This patch function stops the template error for some games
	** Just leave it empty
	*/
}


function loadArcade($mode = 'normal', $index = '')
{
	global $db_prefix, $scripturl, $txt, $modSettings, $context, $settings, $sourcedir, $user_info;
	global $smcFunc, $boarddir, $arcade_version, $arcade_lang_version;

	if(!function_exists('loadClassFile'))
		require_once($sourcedir . '/Subs-ArcadeClass.php');

    /* Are we using the curve or curve type theme?  */
    file_exists($settings['actual_theme_dir'] . '/images/theme/main_block.png') ? $context['curved'] = true : $context['curved'] = false;


	if (!empty($arcade_version))
		return;

	$arcade_version = '2.5 RC4';
	$arcade_lang_version = '2.5';

	$context['arcade'] = array();
	require_once($sourcedir . '/Subs-Arcade.php');
	require_once($sourcedir . '/Subs-ArcadeSkinA.php');
	require_once($sourcedir . '/Subs-ArcadeSkinB.php');

	// Load language
	loadLanguage('Arcade');
	loadLanguage('ArcadeSkinA');

	// Permission query
	arcadePermissionQuery();

	// Normal mode
	if ($mode == 'normal' || $mode == 'arena')
	{
		if (empty($modSettings['arcadeEnabled']))
			return false;

		$modSettings['arcadeSkin'] = !empty($modSettings['arcadeSkin']) ? $modSettings['arcadeSkin'] : false;

		// add new skins to this array
		$current_skin = array(
			'Arcade',
			'ArcadeSkinA',
			'ArcadeSkinB'
		);

		$skinId = !empty($modSettings['arcadeSkin']) ? (int)$modSettings['arcadeSkin'] : 0;
		$skinId = !empty($current_skin[$skinId]) ? $skinId : 0;
		loadTemplate($current_skin[$skinId], array('forum', 'arcade'));
		$user_info['arcade_settings'] = loadArcadeSettings($user_info['id']);
		$context['games_per_page'] = !empty($user_info['arcade_settings']['gamesPerPage']) ? $user_info['arcade_settings']['gamesPerPage'] : $modSettings['gamesPerPage'];
		$context['scores_per_page'] = !empty($user_info['arcade_settings']['scoresPerPage']) ? $user_info['arcade_settings']['scoresPerPage'] : $modSettings['scoresPerPage'];

		// Arcade javascript & css
		$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade.js?rc4"></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade-skin-a.js?rc4"></script>
		<link href="' . $settings['default_theme_url'] . '/css/arcade-skin-b.css?rc4" rel="stylesheet" type="text/css" />';

		// Add Arcade to link tree
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=arcade',
			'name' => $txt['arcade'],
		);

		// What I can do?
		$context['arcade']['can_play'] = allowedTo('arcade_play');
		$context['arcade']['can_favorite'] = !empty($modSettings['arcadeEnableFavorites']) && !$user_info['is_guest'];
		$context['arcade']['can_rate'] = !empty($modSettings['arcadeEnableRatings']) && !$user_info['is_guest'];
		$context['arcade']['can_submit'] = allowedTo('arcade_submit');
		$context['arcade']['can_comment_own'] = allowedTo('arcade_comment_own');
		$context['arcade']['can_comment_any'] = allowedTo('arcade_comment_any');
		$context['arcade']['can_admin_arcade'] = allowedTo('arcade_admin');
		$context['arcade']['can_create_match'] = allowedTo('arcade_create_match');
		$context['arcade']['can_join_match'] = allowedTo('arcade_join_match');

		// Or can I? (do I have enought posts etc.)
		PostPermissionCheck();

		// Finally load Arcade Settings
		LoadArcadeSettings();

		if (!isset($_REQUEST['xml']))
			$context['template_layers'][] = 'Arcade';
	}
	elseif ($mode == 'profile')
		loadTemplate('ArcadeProfile', array('arcade', 'forum'));

	// Admin mode
	elseif ($mode == 'admin')
	{
		loadTemplate('ArcadeAdmin');
		loadLanguage('ArcadeAdmin');

		$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade.js?rc4"></script>';

		// Update games database?
		if (file_exists($boarddir . '/Games.xml'))
		{
			loadClassFile('Class-Package.php');

			$games = new xmlArray(file_get_contents($boarddir . '/Games.xml'));
			$database = $games->path('smf/database');
			$database = $database->to_array();
			$xmlGames = $games->set('smf/game');

			if (!empty($modSettings['arcadeGameDatabaseVersion']) && $modSettings['arcadeGameDatabaseVersion'] > $database['version'])
				break;

			$games = array();

			foreach ($xmlGames as $game)
				$games[] = array(
					$game->fetch('id'),
					$game->fetch('name'),
					$game->fetch('description'),
					$game->fetch('url/info'),
					$game->fetch('url/download'),
				);

			$smcFunc['db_insert']('replace',
				'{db_prefix}arcade_game_info',
				array(
					  'internal_name' => 'string',
					  'game_name' => 'string',
					  'description' => 'string',
					  'info_url' => 'string',
					  'download_url' => 'string',
				),
				$games,
				array('internal_name')
			);

			updateSettings(array(
				'arcadeGameDatabaseVersion' => $database['version'],
				'arcadeGameDatabaseUpdate' => $database['update'],
			));

			@unlink($boarddir . '/Games.xml');
		}

		$context['template_layers'][] = 'ArcadeAdmin';
		$context['page_title'] = $txt['arcade_admin_title'];
	}
}
?>