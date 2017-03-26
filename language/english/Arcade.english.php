<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
global $txt, $modSettings, $boardurl, $scripturl;

// Titles
$txt['arcade_game_list'] = 'Games';
$txt['arcade_game_play'] = 'Arcade - Playing %s';
$txt['arcade_view_highscore'] = 'Arcade - Viewing Highscores of %s';
$txt['arcade_stats_title'] = 'Arcade - Statistics';
$txt['arcade_user_stats_title'] = 'Arcade - Statistics for user %s';
$txt['arcade_arena_challenge_title'] = 'Arcade - Challenge user %s';
$txt['arcade_arena_new_match_title'] = 'Arcade - Create a New Match';
$txt['arcade_arena_view_match_title'] = 'Arcade Arena - Viewing match %s';
$txt['upshrink_description'] = !empty($txt['upshrink_description']) ? $txt['upshrink_description'] : 'Shrink or expand the header.';

// Arcade top box
$txt['arcade_search'] = 'Search';
$txt['search_favorites'] = 'From Favorites only';

// General
$txt['arcade_game_name'] = 'Game';
$txt['arcade_personal_best'] = 'Personal best';
$txt['arcade_champion'] = 'Champion';
$txt['arcade_stats'] = 'Statistics';
$txt['arcade_member'] = 'Member';
$txt['arcade_save'] = 'Save';
$txt['arcade_edit_game'] = 'Edit Game';
$txt['arcade_max_scores'] = 'You may have %d scores at same time';
$txt['arcade'] = 'Arcade';
$txt['arcadeSettings'] = 'Arcade Settings';
$txt['arcadeStats'] = 'Arcade Statistics';
$txt['sendArcadeChallenge'] = 'Arcade Challenge';
$txt['arcade_list_sort'] = 'Ascend / Descend';
$txt['arcade_list_popularity'] = 'Popularity';

// Arcade online and Who's online
$txt['arcade_online_title'] = 'Arcade Online';
$txt['arcade_login_title'] = 'Arcade Login';
$txt['arcade_login_top'] = 'Arcade Login';
$txt['arcade_online'] = 'Online';
$txt['arcade_online_unknown'] = '???';
$txt['arcade_info_who'] = 'Online: %1$d Guest%3$s, %2$d User%4$s';
$txt['who_arcade'] = 'Viewing Arcade index';
$txt['who_arcade_play'] = 'Playing <a href="' . $scripturl . '?action=arcade;sa=play;game=%d">%s</a> on Arcade';
$txt['who_arcade_highscore'] = 'Viewing highscores of <a href="' . $scripturl . '?action=arcade;sa=highscore;game=%d">%s</a> on Arcade';
$txt['who_arcade_match'] = 'Viewing Arcade Arena';
$txt['who_arcade_online'] = 'Viewing Arcade Online';
$txt['who_arcade_view_match'] = 'Viewing a match in the arena';
$txt['who_arcade_new_match'] = 'Starting a new match in the arena';
$txt['arcade_coalesce'] = 'Sort';
$txt['arcade_no_online_guests'] = 'There are currently no guests in the arcade';
$txt['arcade_no_online_members'] = 'There are currently no members in the arcade';
$txt['who_arcade_action'] = 'Arcade Action';

// Information Center
$txt['arcade_info_center'] = 'Arcade Information';
$txt['arcade_game_highlights'] = 'Did you know?';
$txt['arcade_game_with_longest_champion'] = '%s has been champion of %s for way too long?';
$txt['arcade_game_most_played'] = 'Our most played game is %s, have you played it?';
$txt['arcade_game_best_player'] = 'Did you know that %s has claimed way too many champions?';
$txt['arcade_game_we_have_games'] = 'Total of %s games in the arcade';
$txt['arcade_have_tried_these'] = 'Have you tried these?';
$txt['arcade_game_of_day'] = 'Game of Day';
$txt['arcade_latest_scores'] = 'Latest scores';
$txt['arcade_latest_score_item'] = '%4$s scored %3$s on <a href="%1$s">%2$s</a>';
$txt['arcade_users'] = 'Users In Arcade';


// Game list
$txt['arcade_no_games'] = 'No games available for playing';
$txt['arcade_play'] = 'Play';
$txt['arcade_no_scores'] = 'No scores recorded';
$txt['arcade_random_game'] = 'Random game';
$txt['arcade_viewscore'] = 'Highscores';
$txt['arcade_add_favorites'] = 'Add to favorites';
$txt['arcade_remove_favorite'] = 'Remove from favorites';
$txt['arcade_no_highscore'] = 'This game doesn\'t support highscores';
$txt['arcade_show_all'] = 'Show all games';
$txt['arcade_favorite_removed'] = 'Game was removed from favorites!';
$txt['arcade_favorite_added'] = 'Game was added to favorites!';
$txt['arcade_favorites_only'] = 'Show favorites only';
$txt['arcade_number_pages'] = 'Pages:';

// Play
$txt['arcade_no_flash'] = 'You have not installed Adobe Flash Player, you need install it before you can play, you also need to have javascript enabled. <a href="http://www.adobe.com/go/getflashplayer" target="_blank">Install<\/a>';
$txt['arcade_no_javascript'] = 'You need to enable javascript in order to play games.';
$txt['arcade_please_wait'] = 'Please wait while checking session...';
$txt['arcade_session_check_ok'] = 'Session check successful!';
$txt['arcade_save_score'] = 'Save score';

// Highscores
$txt['arcade_no_comment'] = 'No comment';
$txt['arcade_comment'] = 'Comment';
$txt['arcade_score'] = 'Score';
$txt['arcade_position'] = 'Position';
$txt['arcade_time'] = 'Time';
$txt['arcade_this_is_your_best'] = 'This is your best score!';
$txt['arcade_you_are_now_champion'] = 'You are now champion of this game!';
$txt['arcade_edit'] = 'Edit';
$txt['arcade_submit_score'] = 'Thank you for playing!';
$txt['arcade_highscores'] = 'Highscores';
$txt['arcade_score_saved'] = 'Score was saved to database!';
$txt['arcade_rating_saved'] = 'Rating saved';
$txt['arcade_duration'] = 'Duration';
$txt['arcade_enter_name'] = 'Enter you name to save score';
$txt['arcade_when'] = 'Score recorded at %s.<br />Time taken: %s';
$txt['arcade_comment_saved'] = 'Comment saved!';

// Quick Management
$txt['arcade_delete_selected'] = 'Delete Selected';
$txt['arcade_are_you_sure'] = 'Are you sure you want to do this?';

// Arena
$txt['arcade_arena'] = 'Arena';
$txt['arcade_newMatch'] = 'New Match';

// List of matches
$txt['arcade_no_matches'] = 'There are no matches in Arena';
$txt['match_name'] = 'Name';
$txt['match_status'] = 'Status';
$txt['match_players'] = 'Players';
$txt['match_round'] = 'Round';

// Match Status
$txt['arcade_arena_player_invited'] = 'Invited';
$txt['arcade_arena_player_waiting'] = 'Waiting';
$txt['arcade_arena_player_played'] = 'Played';
$txt['arcade_arena_player_knockedout'] = 'Knocked out';
$txt['arcade_arena_waiting_players'] = 'Waiting for Players';
$txt['arcade_arena_started'] = 'In Progress';
$txt['arcade_arena_waiting_other_players'] = 'Waiting for Match to start';
$txt['arcade_arena_not_played'] = 'You haven\'t played yet';
$txt['arcade_arena_not_other_played'] = 'Waiting for other players';
$txt['arcade_arena_dropped'] = 'Knocked out';
$txt['arcade_arena_complete'] = 'Completed';

// View Match
$txt['arcade_startMatch'] = 'Start';
$txt['arcade_cancelMatch'] = 'Cancel';
$txt['arcade_joinMatch'] = 'Join';
$txt['arcade_leaveMatch'] = 'Leave';
$txt['match_not_found'] = 'Match not found';
$txt['arcade_rounds'] = 'Rounds';
$txt['arcade_players'] = 'Players';
$txt['arcade_accept'] = 'Accept';
$txt['arcade_decline'] = 'Decline';

// Invite to a Match
$txt['arcade_invite'] = 'Invite';
$txt['arcade_invite_user'] = 'Invite user to a match';
$txt['invite_to_existing'] = 'Invite to existing match';
$txt['arcade_create_new'] = 'Create a New Match';

// New match
$txt['arcade_new_match'] = 'Create a New Match';
$txt['arcade_match_name'] = 'Name';
$txt['game_mode'] = 'Game Mode';
$txt['game_mode_normal'] = 'Normal';
$txt['game_mode_knockout'] = 'Knockout';
$txt['players'] = 'Players';
$txt['player_add'] = 'Add player';
$txt['player_remove'] = 'Remove Player';
$txt['num_players'] = 'Number of Players';
$txt['num_players_help'] = 'Means how many players can join this match at maximum. Must be greater than or equal to number of players invited here';
$txt['rounds'] = 'Rounds';
$txt['add_game'] = 'Add Game';
$txt['game_remove'] = 'Remove Game';
$txt['arcade_continue'] = 'Continue';

// Ago
$txt['arcade_secs'] = 'seconds';
$txt['arcade_weeks'] = 'weeks';
$txt['arcade_days'] = 'days';
$txt['arcade_hours'] = 'hours';
$txt['arcade_mins'] = 'minutes';
$txt['arcade_under_minute_ago'] = 'Under minute ago';
$txt['arcade_unknown'] = 'unknown';

// Statistics
$txt['arcade_longest_champions'] = 'Longest champions';
$txt['arcade_best_games'] = 'Best Games (by rating)';
$txt['arcade_best_players'] = 'Best Players (by champions)';
$txt['arcade_most_played'] = 'Most played games';
$txt['arcade_most_active'] = 'Most active players';
$txt['arcade_member_stats'] = 'Arcade Statistics';
$txt['arcade_champion_in'] = 'Currently Champion in';
$txt['arcade_games'] = 'games';
$txt['arcade_rated_game'] = 'Number of Games rated';
$txt['arcade_average_rating'] = 'Average rating';
$txt['arcade_member_best_scores'] = 'Personal best scores';

// User settings
$txt['arcade_usersettings_desc'] = 'You can change your Arcade settings from this page.';
$txt['arcade_notifications'] = 'Notifications';
$txt['arcade_user_gamesPerPage'] = 'Games Per Page';
$txt['arcade_user_gamesPerPage_default'] = 'Default (%d)';
$txt['arcade_user_scoresPerPage'] = 'Scores Per Page';
$txt['arcade_user_scoresPerPage_default'] = 'Default (%d)';
$txt['arcade_user_default'] = 'Default (%s)';
$txt['arcade_user_skin'] = 'Select Skin';
$txt['arcade_user_list'] = 'Select List';

// Errors
/*  Arcade - PDL Text Variables  */
$modSettings['arcadeDownPost'] = !empty($modSettings['arcadeDownPost']) ? $modSettings['arcadeDownPost'] : false;
$modSettings['pdl_DownMax'] = !empty($modSettings['pdl_DownMax']) ? $modSettings['pdl_DownMax'] : false;
$txt['pdl_error_tar'] = 'Unable to locate PEAR function';
$txt['pdl_error_perm'] = 'No Permission To Download Games';
$txt['pdl_error_disable'] = 'Download Feature Disabled';
$txt['pdl_error_post'] = 'Sorry, you need to have at least '. $modSettings['arcadeDownPost'] . ' posts in the forum to enable this feature!';
$txt['pdl_error_nogame'] = 'No Game Selected';
$txt['pdl_error_db'] = 'Game not in database';
$txt['pdl_error_dl'] = 'Downloading for this game has been disabled by the administrator.';
$txt['pdl_error_max'] = 'You have exceeded the daily download limit of ' . $modSettings['pdl_DownMax'];
$txt['arcade_submit_adjust_configure_log'] = 'Error while saving score for game "%s". Attempting to auto adjust submit system to "%s" to fix the error.';
$txt['pdl_zipfile1'] = 'The download file was NOT SPECIFIED.';
$txt['pdl_zipfile2'] = 'File not found.';
$txt['pdl_zipfile3'] = 'Could not locate the specified directory - ';
$txt['arcade_email_play_error'] = 'You need to log in for the arcade!';
$txt['arcade_email_play_error_msg'] = 'You will be redirected to the arcade after logging in.';
$txt['arcade_email_score_error'] = 'You need to log in to view high scores!';
$txt['arcade_email_score_error_msg'] = 'You will be redirected to the highscores after logging in.';
$txt['arcade_email_hs_error'] = 'You need to log in to view high scores because the Admin has disabled viewing scores for guests.';
$txt['arcade_online_error'] = 'The Arcade Online list has been disabled by the Administrator.';
$txt['arcade_disabled'] = 'Arcade is currently disabled by admin';
$txt['arcade_game_update_error'] = 'Unable to update game data';
$txt['arcade_scores_limit'] = 'Score was not saved because you already have maximum number of scores';
$txt['arcade_no_permission'] = 'Score was not saved because you do not have permission!';
$txt['arcade_saving_error'] = 'Score was not saved due to unknown error!';
$txt['arcade_game_not_found'] = 'Game was not found';
$txt['arcade_submit_error'] = 'An error occurred while saving score';
$txt['arcade_rate_error'] = 'Unable to save rating';
$txt['arcade_cannot_save'] = 'You are not allowed to save your scores!';
$txt['arcade_submit_error_session'] = 'Score was not saved because session is missing';
$txt['arcade_submit_error_configure_log'] = 'Error while saving score for game "%s". Submit system might be invalid, should be "%s" or user tried to cheat.';
$txt['arcade_notice_post_requirement'] = 'You don\'t meet post requirements to play.';
$txt['arcade_internal_error'] = 'An internal error occurred.';
$txt['arcade_no_invite'] = 'This member cannot be invited to Arena Match.';
$txt['arena_error_no_name'] = 'Match must have name';
$txt['arena_error_name_too_long'] = 'Name for match is too long';
$txt['arena_error_no_rounds'] = 'No rounds added';
$txt['arena_error_not_enough_players'] = 'There is not enough slots for players';
$txt['arena_error_invalid_rounds'] = 'Invalid Games selected for the match';

// Email notifications
$txt['arcade_notification_champion_pm'] = 'PM Arcade alerts';
$txt['arcade_notification_champion_email'] = 'Email Arcade alerts';
$txt['arcade_notification_new_champion_own'] = 'When someone takes championship from me';
$txt['arcade_notification_arena_invite_own_subject'] = 'You are invited to join a match';
$txt['arcade_notification_new_champion_any'] = 'When someone takes championship from anyone';
$txt['arcade_notification_arena_invite'] = 'When I\'m invited to join a match';
$txt['arcade_notification_arena_new_round'] = 'When new round begins on a Match';
$txt['arcade_notification_arena_match_end'] = 'When match I\'m participated in is finished';
$txt['view_cat'] = 'View By Category';
$txt['arcade_none_played'] = 'No games have been played';
$txt['arcade_title'] = 'Arcade';

// Arcade Advanced
$txt['arcade_download_gameplay'] = 'Download';
$txt['pdl_play'] = '<img src="Themes/default/images/arc_icons/pdl_play.gif" alt="Play" title="Play Game" />';
$txt['pdl_download_game'] = '<img src="Themes/default/images/arc_icons/pdl_download.gif" alt="Download" title="Download" />';
$txt['arcade_download_gameplay'] = '<img src="' . $boardurl . '/Themes/default/images/arc_icons/dl_btn.png" style="width: 70px;height: 18px;" title="Download" alt="Download"></img>';
$txt['pdl_erroricon'] = '<a href="'.$scripturl.'?action=arcade" target="_parent"><img src="'.$boardurl.'/Themes/default/images/arc_icons/arcade_popup_error.gif" alt="SMF ARCADE" title="SMF ARCADE" /></a><br />';
$txt['pdl_arcade_copyright'] = '<div id="arcade_bottom" class="smalltext" style="text-align: center;">Powered by: <a href="http://web-develop.ca/index.php?page=arcade_license_BSD2" target="_blank">SMF Arcade ' . $modSettings['arcadeVersion'] . '</a> &copy; 2004-2017</div>';
$txt['pdl_listplay'] = 'PLAY';
$txt['arcade_replay'] = 'Replay';
$txt['arcade_download_game'] = 'Download';
$txt['pdl_unassigned'] = 'Unassigned';
$txt['pdl_unlimited'] = 'Unlimited';
$txt['pdl_na'] = 'N/A';
$txt['pdl_gamedata'] = 'Game Information';
$txt['game_categories'] = 'Game Categories';
$txt['pdl']['help'] = 'Help';
$txt['arcade_post_help'] = 'Navigation: ';
$txt['arcade_post_description'] = 'Description: ';
$txt['pdl_button1'] = 'Download';
$txt['pdl_edit'] = 'Edit';
$txt['pdl_report'] = 'Report';
$txt['pdl_popplay'] = 'Play Popup';
$txt['pdl_counter'] = 'Total Downloads: ';
$txt['pdl_max_limit'] = 'Limit';
$txt['pdl_disabled'] = 'Arcade is disabled.';
$txt['pdl_notfound'] = 'Game not found in database.';
$txt['pdl_gamedisable'] = 'Game has been disabled.';
$txt['pdl_reports_toggle'] = 'Enable/Disable Download';
$txt['pdl_dl_status'] = 'Download Status';
$txt['pdl_dl_enabled'] = 'Enabled';
$txt['pdl_dl_disabled'] = 'Disabled';
$txt['pdl_yes'] = 'Yes';
$txt['view_cat'] = 'View By Category';
$txt['arcade_post'] = 'Try %#@$ from the Arcade';
$txt['pdl_down'] = '<br /><br /><br />';
/* If you want a button for downloads, omit the remark tags from the line below  */
/* $txt['pdl_button1'] = '<img src="' . $boardurl . '/Themes/default/images/arc_icons/dl_btn.png" style="70px;height: 18px;" alt="Download" title="Download" />'; */
$txt['show_pdl_report'] = 'View Report';
$txt['arcade_tour_tour'] = 'Tournament';
$txt['arcade_administrator'] = 'Admin';

// Arcade
$txt['arcade'] = 'Arcade';

// Core Features
$txt['core_settings_item_arcade'] = 'Arcade';
$txt['core_settings_item_arcade_desc'] = 'Enable Arcade section which allows users to play games and store their records for others to see.';

// Admin
$txt['arcade_admin'] = 'Arcade';
$txt['arcade_manage_games'] = 'Games';
$txt['arcade_manage_games_edit_games'] = 'Edit Games';
$txt['arcade_manage_games_install'] = 'Install Games';
$txt['arcade_manage_games_upload'] = 'Upload';
$txt['arcade_manage_category'] = 'Categories';
$txt['arcade_manage_category_list'] = 'List';
$txt['arcade_manage_category_new'] = 'New';
$txt['arcade_general'] = 'General';
$txt['arcade_general_information'] = 'Information';
$txt['arcade_general_settings'] = 'Settings';
$txt['arcade_general_permissions'] = 'Permissions';
$txt['arcade_maintenance'] = 'Maintenance';
$txt['arcade_maintenance_main'] = 'Main';
$txt['arcade_maintenance_highscore'] = 'Highscore';

// Moderation Log
$txt['modlog_ac_arcade_install_game'] = 'Installed Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_update_game'] = 'Updated Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_delete_game'] = 'Deleted Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_remove_scores'] = 'Removed {scores} scores from Game &quot;{game}&quot;';

// Profile
$txt['arcadeStats'] = 'Arcade Statistics';
$txt['arcadeChallenge'] = 'Arcade Arena';
$txt['sendArcadeChallenge'] = 'Send Challenge';
$txt['arcadeSettings'] = 'Arcade Settings';

// Errors if they can't do something
$txt['cannot_arcade_play'] = 'You are not allowed to play games!';
$txt['cannot_arcade_view'] = 'You are not allowed to access arcade.';
$txt['cannot_arcade_comment_own'] = 'You are not allowed to comment';
$txt['cannot_arcade_user_stats_any'] = 'You are not allowed to view statistics of any user';
$txt['cannot_arcade_user_stats_own'] = 'You are not allowed to view your statistics';

// Help
$txt['arcade_max_scores_help'] = 'Maximum scores that will be stored per member. (0 means unlimited)';
$txt['arcade_membergroups_help'] = 'These groups will be allowed to play and view highscores. Others will not see this game, only used if permission mode will use game permissions.';

// Defiant specifics
$txt['arcade_topic_talk'] = 'Talk';
$txt['arcade_popplay'] = 'Play in Popup';
$txt['arcade_dviewscore'] = 'View Highscore';
$txt['arcade_you_are_first'] = 'You are champion of';
$txt['arcade_you_are_second'] = 'You have the second best score in';
$txt['arcade_you_are_third'] = 'You have the third best score in';
$txt['arcade_is_guest'] = 'Guest';
$txt['arcade_first'] = 'First';
$txt['arcade_second'] = 'Second';
$txt['arcade_third'] = 'Third';
$txt['arcade_dpages'] = 'Pages';
$txt['arcade_dgo_down'] = 'Go Down';
$txt['arcade_dgo_up'] = 'Go Up';
$txt['arcade_dhelp'] = 'Help';
$txt['arcade_defdescript'] = 'Description';
$txt['arcade_info'] = 'Arcade Information';
$txt['arcade_u_b_1'] = 'Welcome to the Arcade';
$txt['arcade_shouts'] = 'Arcade Shouts';
$txt['arcade_shout'] = 'Shout!';
$txt['arcade_shouted'] = 'Shouted - ';
$txt['arcade_shout_del'] = 'Delete this shout?';
$txt['arcade_shout_scored'] = 'Scored ';
$txt['arcade_shout_on'] = ' on ';
$txt['arcade_shout_pb'] = 'New personal best on ';
$txt['arcade_g_i_b_3'] = 'Most Played';
$txt['arcade_g_i_b_5'] = 'for';
$txt['arcade_g_i_b_6'] = 'Played';
$txt['arcade_g_i_b_7'] = 'times.';
$txt['arcade_g_i_b_8'] = 'Latest Champs';
$txt['arcade_g_i_b_9'] = 'has been champ of';
$txt['arcade_g_i_b_10'] = 'Most Played Games';
$txt['arcade_g_i_b_11'] = 'Longest Champs';
$txt['arcade_b3pb_1'] = 'Best Players';
$txt['arcade_b3pb_2'] = 'with';
$txt['arcade_b3pb_3'] = 'Wins';
$txt['arcade_u_b_2'] = 'Show my favorites';
$txt['arcade_list_games'] = '- List games by -';
$txt['arcade_nameAZ'] = 'Name A-Z';
$txt['arcade_nameZA'] = 'Name Z-A';
$txt['arcade_LeastPlayed'] = 'Least Played';
$txt['arcade_LatestList'] = 'Latest - All';
$txt['arcade_LeastPlayedGame'] = 'Least Played Games';
$txt['arcade_RatedGames'] = 'Highest Rated Games';
$txt['arcade_LatestGames'] = 'Latest Games';
$txt['arcade_Gamecategory'] = 'Game Categories';
$txt['arcade_plays'] = 'Plays';
$txt['arcade_play_again'] = 'Play Again';
$txt['arcade_play_other'] = 'Play Something Else';
$txt['arcade_rate_game'] = 'Rate';
$txt['arcade_champions_stats'] = 'Arcade Stats';
$txt['arcade_champions_cho'] = 'Champion of';
$txt['arcade_champions_play'] = 'Play';
$txt['arcade_champions_tro'] = 'Game Trophies';
$txt['arcade_champions_th'] = 'Trophies Held';
$txt['arcade_champions_tgp'] = 'Total Game Plays';
$txt['arcade_champions_tsp'] = 'Time Spent Playing';
$txt['is_champ_of'] = 'is champ of';
$txt['arcade_game'] = 'Game';
$txt['arcade_close'] = 'Close';
$txt['arcade_rating_sort'] = 'Rating';
$txt['arcade_topic_talk'] ='Talk';
$txt['arcade_topic_talk2'] ='Report errors or talk about';
$txt['arcade_quick_search'] = 'Search by name or List games';
$txt['arcade_info_fav'] = 'Show favorites';
$txt['arcade_info_showlate'] = 'Show Latest';
$txt['arcade_info_defavatar'] = 'Default Avatar';
$txt['arcade_info_showcat'] = 'Show %s';
$txt['arcade_guest_na'] = 'N/A';
$txt['arcade_search_text'] = 'Search by name or list games';
$txt['arcade_no_links'] = '********';
?>