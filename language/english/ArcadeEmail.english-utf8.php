<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

// SMF 2.1 behaviour
// Highscore email notifications should be set to HTML
$txt['notification_arcade_new_champion_own_subject'] = 'You are no longer champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_own_body'] = '<div>You are no longer champion of {GAMENAME},</div>
<div style="padding-top: 5px;"> {champion.name} has beaten your score and is a new champion!</div>
<div style="padding-top: 5px;">To reclaim this title, {play.the.game} and get a score better than {champion.score}.</div>
<div style="padding-top: 15px;">You may opt to disable this notification from your profile settings.</div>
<div style="padding-top: 15px;">In order to save your score you must login.</div>
<div style="padding-top: 25px;">{REGARDS}</div>';

$txt['notification_arcade_new_champion_any_subject'] = '{old_champion.name} is no longer champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_any_body'] = '<div>{old_champion.name} is no longer champion of {GAMENAME},</div>
<div style="padding-top: 5px;">{champion.name} has beaten {old_champion.name}\'s score and is a new champion!</div>
<div style="padding-top: 15px;">You may opt to disable this notification from your profile settings.</div>
<div style="padding-top: 15px;">In order to save your score you must login.</div>
<div style="padding-top: 25px;">{REGARDS}</div>';

// Highscore PM notifications should be plain text only
$txt['notification_arcade_new_champion_ownPM_subject'] = 'You are no longer champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_ownPM_body'] = 'You are no longer champion of {GAMENAME},
{champion.name} has beaten your score and is a new champion!
To reclaim this title, {play.the.game} and get a score better than {champion.score}.

You may opt to disable this notification from: {ARCADE_SETTINGS_URL}


{REGARDS}';

$txt['notification_arcade_new_champion_anyPM_subject'] = '{old_champion.name} is no longer champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_anyPM_body'] = '{old_champion.name} is no longer champion of {GAMENAME},
{champion.name} has beaten {old_champion.name}\'s score and is a new champion!

You may opt to disable this notification from: {ARCADE_SETTINGS_URL}


{REGARDS}';

// Arena email notifications should be set to plain text
$txt['notification_arcade_arena_invite_subject'] = 'You are invited to join a match';
$txt['notification_arcade_arena_invite_body'] = 'You have been invited to join match "{MATCHNAME}" on Arcade Arena.
To accept or decline this offer, visit match\'s page in url below:
{MATCHURL}

If you want to, You can disable this notification from
{ARCADE_SETTINGS_URL}

{REGARDS}';

$txt['notification_arcade_arena_new_round_subject'] = '{MATCHNAME}: New Round begins';
$txt['notification_arcade_arena_new_round_body'] = 'New Round has begun on match "{MATCHNAME}".
Visit following url to play:
{MATCHURL}

If you want to, You can disable this notification from
{ARCADE_SETTINGS_URL}

{REGARDS}';

$txt['notification_arcade_arena_match_end_subject'] = '{MATCHNAME}: Finished';
$txt['notification_arcade_arena_match_end_body'] = 'Match "{MATCHNAME}" has been finished.
Visit following url to see results:
{MATCHURL}

If you want to, You can disable this notification from
{ARCADE_SETTINGS_URL}

{REGARDS}';

// SMF 2.0 behaviour
// Highscore email notifications should be set to HTML
$txt['emails']['notification_arcade_new_champion_own'] = array(
	'subject' => 'You are no longer champion of {GAMENAMESUB}',
	'body' => '<div>You are no longer champion of {GAMENAME},</div>
<div style="padding-top: 5px;"> {champion.name} has beaten your score and is a new champion!</div>
<div style="padding-top: 5px;">To reclaim this title, {play.the.game} and get a score better than {champion.score}.</div>
<div style="padding-top: 15px;">You may opt to disable this notification from: {ARCADE_SETTINGS_URL}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

$txt['emails']['notification_arcade_new_champion_any'] = array(
	'subject' => '{old_champion.name} is no longer champion of {GAMENAMESUB}',
	'body' => '<div>{old_champion.name} is no longer champion of {GAMENAME},</div>
<div style="padding-top: 5px;">{champion.name} has beaten {old_champion.name}\'s score and is a new champion!</div>
<div style="padding-top: 15px;">You may opt to disable this notification from: {ARCADE_SETTINGS_URL}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

// Highscore PM notifications should be plain text only
$txt['emails']['notification_arcade_new_champion_ownPM'] = array(
	'subject' => 'You are no longer champion of {GAMENAMESUB}',
	'body' => 'You are no longer champion of {GAMENAME},
{champion.name} has beaten your score and is a new champion!
To reclaim this title, {play.the.game} and get a score better than {champion.score}.

You may opt to disable this notification from: {ARCADE_SETTINGS_URL}


{REGARDS}');

$txt['emails']['notification_arcade_new_champion_anyPM'] = array(
	'subject' => '{old_champion.name} is no longer champion of {GAMENAMESUB}',
	'body' => '{old_champion.name} is no longer champion of {GAMENAME},
{champion.name} has beaten {old_champion.name}\'s score and is a new champion!

You may opt to disable this notification from: {ARCADE_SETTINGS_URL}


{REGARDS}');

// Arena email notifications should be set to plain text
$txt['emails']['notification_arcade_arena_invite'] = array(
	'subject' => 'You are invited to join a match',
	'body' => 'You have been invited to join match "{MATCHNAME}" on Arcade Arena.
To accept or decline this offer, visit match\'s page in url below:
{MATCHURL}

If you want to, You can disable this notification from
{ARCADE_SETTINGS_URL}

{REGARDS}');

$txt['emails']['notification_arcade_arena_new_round'] = array(
	'subject' => '{MATCHNAME}: New Round begins',
	'body' => 'New Round has begun on match "{MATCHNAME}".
Visit following url to play:
{MATCHURL}

If you want to, You can disable this notification from
{ARCADE_SETTINGS_URL}

{REGARDS}');

$txt['emails']['notification_arcade_arena_match_end'] = array(
	'subject' => '{MATCHNAME}: Finished',
	'body' => 'Match "{MATCHNAME}" has been finished.
Visit following url to see results:
{MATCHURL}

If you want to, You can disable this notification from
{ARCADE_SETTINGS_URL}

{REGARDS}');

// General replacements
$txt['arcade_pm_play_game'] = 'PLAY THE GAME';
$txt['arcade_pm_join_match'] = 'PLAY THE MATCH';
?>