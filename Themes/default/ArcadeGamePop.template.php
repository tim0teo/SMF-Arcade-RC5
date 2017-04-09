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
	void arcadePopupTemplate()
		- Popup template for Game in post, bbc, php portal block or regular popup.

	void pophighscoreTemplate()
		- Specific high score template for popup, bbc or php portal block
*/

function arcadePopupTemplate()
{
	global $scripturl, $txt, $context, $settings, $boardurl, $modSettings;
	$context['show_pm_popup'] = !empty($context['show_pm_popup']) ? true : false;

	echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
   <head>
	<meta http-equiv="Content-Type" content="text/html; charset=',$context['character_set'],'" />
	<meta name="description" content="', $context['forum_name_html_safe'], '" />
	<meta name="keywords" content="', $context['game']['internal_name'], '" />
    <title>', $context['forum_name_html_safe'], '</title>
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?rc1"></script>
	<script type="text/javascript">
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_charset = "', $context['character_set'], '";
		var ajax_notification_text = "', $txt['ajax_in_progress'], '";
		var ajax_notification_cancel_text = "', $txt['modify_cancel'], '";
		var stopit;
	</script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade.js?rc2"></script>
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css" />
    <style type="text/css">
         body
         {
            padding: 0px 0px 0px 0px; background: transparent;
			width: ', ($context['game']['submit_system'] == 'html5' ? ($context['game']['width'] + 50) : ($context['game']['width'] + 20)), 'px;
			height: ', ($context['game']['submit_system'] == 'html5' ? ($context['game']['height'] + 70) : ($context['game']['height'] + 20)), 'px;
			overflow: hidden;
         }
         body, td, th, .normaltext
         {
            font-size: x-small;
         }
         .smalltext
         {
            font-size: xx-small;
         }
		 .bodyblock
		 {
			 background-color: transparent;
		 }
		 .gamepop1
		 {
			overflow: hidden;
			min-height: 100vh;
			min-width: 100vw;
			width: ', ($context['game']['submit_system'] == 'html5' ? ($context['game']['width'] + 50) : ($context['game']['width'] + 20)), 'px;
			height: ', ($context['game']['submit_system'] == 'html5' ? ($context['game']['height'] + 100) : ($context['game']['height'] + 20)), 'px;
		 }
		 .gamepop2
		 {
			display: inline-block;
			overflow: hidden;
			border: 0px;
			min-height: 100vh;
			width: ', ($context['game']['submit_system'] == 'html5' ? ($context['game']['width'] + 50) : ($context['game']['width'] + 20)), 'px;
			height: ', ($context['game']['submit_system'] == 'html5' ? ($context['game']['height'] + 100) : ($context['game']['height'] + 20)), 'px;
		 }
		 .gamepop3
		 {
			overflow: hidden;
			min-height: 100vh;
			height: 100vh;
			width: 100vw;
		 }
      </style>
   </head>';
	$check_block = !empty($_REQUEST['block']) ? (int)$_REQUEST['block'] : 0;
	if ($check_block == 1)
	{
		echo '
<!--[if IE]>
    <body class="windowbg" id="html_page1">
		<div style="text-align:center;overflow: hidden;">
<![endif]-->
<!--[if !IE]><!-->
	<body class="bodyblock" id="html_page1">
		<div style="background-color: transparent;text-align:center;overflow: hidden;">
<!--<![endif]-->';
	}
	else
	{
		echo '
	<body class="windowbg" id="html_page1">
		<div style="text-align:center;overflow: hidden;">';
	}

	echo '
		<div style="margin-top:0ex;text-align: center; font-style: italic; font-weight: bold; font-size: 8pt;"><a href="javascript:location.reload(true);">'.$context['game']['name'].'</a></div><br />';

	echo '
		<div style="position:absolute;margin-right: auto; left:2px; right: 2px; bottom:0.5px; top:3ex;overflow: hidden;">';


	if ($context['game']['submit_system'] == 'html5')
		echo '
		<form id="gameForm" action="', $scripturl, '?action=arcade;game=', $context['game']['id'], ';sa=html5Game;pop=1;" method="post">
			<input type="hidden" id="score" name="score" />
			<input type="hidden" id="game" name="game" value="', $context['game']['id'], '" />
			<input type="hidden" id="time" name="time" value="', time(), '" />
			<input type="hidden" id="gameexit" name="gameexit" value="0" />
			<input type="hidden" id="noSmfScore" name="noSmfScore" value="', $txt['arcade_noSmfScore'], '" />
			<input type="hidden" id="gameSmfToken" name="gameSmfToken" value="', $_SESSION['arcade_html5_token'][1], '" />
			<input type="hidden" id="html5" name="html5" value="1" />
			<input type="hidden" id="popup" name="popup" value="1" />
			<input type="hidden" id="game_name" name="game_name" value="', $context['game']['internal_name'], '" />
		</form>
		<div class="gamepop1" id="gamearea1">
			<div class="gamepop2">
				<object type="text/html" class="gamepop3" data="' . $modSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '">
				</object>
			</div>
			', !$context['arcade']['can_submit'] ? '<br /><strong>' . $txt['arcade_cannot_save'] . '</strong>' : '', '
			<div syle="padding-bottom: 40px;"><span style="display: none;">&nbsp;</span></div></div></div>
		</div>';
	else
		echo '
		<div id="gamearea1" class="gamepop1">
			', $context['game']['html']($context['game'], true), '
			', !$context['arcade']['can_submit'] ? '<br /><strong>' . $txt['arcade_cannot_save'] . '</strong>' : '', '
			<div syle="padding-bottom: 40px;"><span style="display: none;">&nbsp;</span></div></div></div>
		</div>	';

	echo '
	<script type="text/javascript">
		window.onload = function()
		{
			var docbody = parent.document.getElementsByTagName("body")[0];
			docbody.height = "100vh";
			docbody.width = "100vw":
			self.focus();
		}
		function escGameSmf() {
			window.location = "' . $scripturl . '?action=arcade;sa=highscore;pop=1;game=' . $context['game']['id'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
			var width = document.getElementById("scoresdiv").offsetWidth;
			var height = document.getElementById("scoresdiv").offsetHeight;
			window.resizeTo(width+20,height+100);
			self.focus();
		}
	</script>
	</body>
</html>';
}

function arcadePopHighscoreTemplate()
{
	global $scripturl, $txt, $context, $settings, $boardurl, $modSettings;

	if (empty($context['arcade']['buttons']))
		$context['arcade']['buttons'] = array();

	echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
   <head>
	<meta http-equiv="Content-Type" content="text/html; charset=',$context['character_set'],'" />
	<meta name="description" content="', $context['forum_name_html_safe'], '" />
	<meta name="keywords" content="', $context['game']['internal_name'], '" />
    <title>', $context['forum_name_html_safe'], '</title>
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?rc1"></script>
	<script type="text/javascript" src="', $settings['theme_url'], '/scripts/theme.js?rc1"></script>
	<script type="text/javascript">
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_charset = "', $context['character_set'], '";
		var ajax_notification_text = "', $txt['ajax_in_progress'], '";
		var ajax_notification_cancel_text = "', $txt['modify_cancel'], '";
		var stopit;
	</script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade.js?rc2"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade-skin-a.js?rc4"></script>
      <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css" />
      <style type="text/css">
         body
         {
            padding: 0px 0px 0px 0px; background: transparent;
			overflow: hidden;
			width: 100vw;
			min-width: 100vw;
         }
         body, td, th, .normaltext
         {
            font-size: x-small;
         }
         .smalltext
         {
            font-size: xx-small;
         }
      </style>
   </head>   ';
	$check_block = !empty($_REQUEST['block']) ? (int) $_REQUEST['block'] : 0;
	if ($check_block == 1)
	{
		echo '<!--[if IE]>
       <body class="windowbg" id="html_page1">
      <div style="text-align:center;overflow: hidden;">
      <![endif]-->

<!--[if !IE]><!-->
<body style="background-color: transparent;" id="html_page1">
      <div id="scoresdiv" style="background-color: transparent;text-align:center;overflow: hidden;">
<!--<![endif]-->';
	}
	else
	{
		echo '
	<body class="windowbg" id="html_page1">
		<div id="scoresdiv" style="text-align:center;overflow: hidden;">';
	}

	if (isset($context['arcade']['submit']))
	{
		if ($context['arcade']['submit'] == 'newscore') // Was score submitted
		{
			$score = &$context['arcade']['new_score'];
			echo '
		<div class="cat_bar" style="clear: both;position: relative;">
			<h3 class="catbg centertext">
				<span class="centertext" style="clear: left;vertical-align: middle;">', $txt['arcade_submit_score'], '</span>
			</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span>&nbsp;</span></span>
		<div style="padding: 0 0.5em;overflow: hidden;">';

			// No permission to save
			if (!$score['saved'])
				echo '
			<div>
				', $txt[$score['error']], '<br />
				<strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '
			</div>';

			else
			{
				echo '
			<div>
				', $txt['arcade_score_saved'], '<br />
				<strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '<br />';

				if ($score['is_new_champion'])
					echo '
				', $txt['arcade_you_are_now_champion'], '<br />';

				elseif ($score['is_personal_best'])
					echo '
				', $txt['arcade_this_is_your_best'], '<br />';

				if ($score['can_comment'])
					echo '
			</div>
			<div>
				<form name="commentform1" id="commentform1" action="', $scripturl, '?action=arcade;game=', $context['game']['id'], ';score=',  $score['id'], ';sa=highscore;pop=1;reload=' . mt_rand(1, 9999) . '" method="post">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="mynewscoreid" value="', $score['id'], '" />
					<input type="text" id="new_comment" name="new_comment" maxlength="50" size="30" />
					<input class="button_submit" onclick="myformxyz(\'commentform1\', ', $score['id'], ')" type="submit" name="csave" value="', $txt['arcade_save'], '" />
				</form>
			</div>';
			}

			echo '
		</div>
		<span class="botslice"><span>&nbsp;</span></span>
	</div>
	<br />';
		}
		elseif ($context['arcade']['submit'] == 'askname')
		{
			echo '
		<div style="text-align:left;">
			<h3 class="catbg">
			<span style="clear: both;float:left;border:0px;background: url(',$settings['actual_theme_url'],'/images/theme/main_block.png) no-repeat 0% -160px;">&nbsp;</span>
			<span>', $txt['arcade_submit_score'], '</span>
			</h3></div>
	<div class="windowbg2">
		<span class="topslice"><span>&nbsp;</span></span>
		<div style="padding: 0 0.5em">
			<form name="commentform2" id="commentform2" action="', $scripturl, '?action=arcade;sa=save" method="post" onsubmit="myformxyz(\'commentform2\'), 0">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="text" id="name" name="name" maxlength="20" />
				<input class="button_submit" onclick="myformxyz(\'commentform2\'), -1" type="submit" value="', $txt['arcade_save'], '" />
			</form>
		</div>
	</div><br />';
		}
	}
	echo '
	<div class="pagesection">
		<div class="align_left">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#bot"><b>' . $txt['go_down'] . '</b></a>' : '', '</div>
	</div>
	<form name="commentform3" id="commentform3" action="', $scripturl, '?action=arcade;sa=highscore;pop=1;reload=' . mt_rand(1, 9999) . '" method="post">
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		<input type="hidden" name="game" value="', $context['game']['id'], '" />
		<div style="text-align:left;">
		<h3 class="catbg">
			<span style="clear: both;float:left;border:0px;background: url(',$settings['actual_theme_url'],'/images/theme/main_block.png) no-repeat 0% -160px;">&nbsp;</span>
			<span>', $txt['arcade_highscores'], '</span>
			<span class="floatright">', '<a onclick="replaygame()" href="Javascript:void(0)">',$txt['arcade_replay'] ,'</a>' , '</span>

		</h3></div>
		<div class="score_table">
			<table cellspacing="0" class="table_grid" style="width: 100%;">
					<tr>';

	/* Is there games? */
	if (!empty($context['arcade']['scores']))
	{
			echo '
						<th scope="col" class="smalltext first_th" style="width: 5px;">', $txt['arcade_position'], '</th>
						<th scope="col" class="smalltext">', $txt['arcade_member'], '</th>
						<th scope="col" class="smalltext"> ', $txt['arcade_comment'], '</th>
						<th scope="col" class="smalltext', !$context['arcade']['can_admin_arcade'] ? ' last_th' : '', '">', $txt['arcade_score'], '</th>';

		if ($context['arcade']['can_admin_arcade'])
			echo '
						<th scope="col" class="smalltext last_th" align="center" style="width: 15px;"><input type="checkbox" onclick="invertAll(this, this.form, \'scores[]\');" class="check" /></th>';
	}
	else
	{
		echo '
						<th scope="col" class="smalltext first_th" style="width: 8%">&nbsp;</th>
						<th class="smalltext" colspan="', !$context['arcade']['can_admin_arcade'] ? '2' : '3', '"><strong>', $txt['arcade_no_scores'], '</strong></th>
						<th scope="col" class="smalltext last_th" style="width: 8%;">&nbsp;</th>';
	}

	echo '
					</tr>';

	$edit_button = '<span style="width: 16px;height: 16px;display: inline-block;background: url(' . $settings['default_theme_url'] . '/images/arcade_edit.gif) no-repeat;vertical-align: middle;"><span style="display: none;">&nbsp;</span></span>';
	foreach ($context['arcade']['scores'] as $score)
	{
		$div_con = addslashes(sprintf($txt['arcade_when'], $score['time'], duration_format($score['duration'])));

		echo '
					<tr class="', $score['own'] ? 'windowbg3' : 'windowbg', '"', !empty($score['highlight']) ? ' style="font-weight: bold;"' : '', '>
						<td class="windowbg2 centertext">', $score['position'], '</td>
						<td>', $score['member']['link'], '</td>
						<td style="width: 300px;" class="windowbg2">';

		if ($score['can_edit'] && empty($score['edit']))
			echo '
							<div id="comment', $score['id'], '" class="floatleft">', $score['comment'], '</div>
							<div id="edit', $score['id'], '" class="floatleft" style="display: none;">
								<input onkeydown="enterkey(event, ', $score['id'], ')" type="text" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" maxlength="50" />

								<input type="button" onclick="myformxyz(\'commentform3\', \'', $score['id'], '\');" name="csave" value="', $txt['arcade_save'], '" />							</div>
							<a id="editlink', $score['id'], '" onclick="arcadeCommentEdit(', $score['id'], ', ', $context['game']['id'], ', 0); myformxyz(\'commentform3\', \'', $score['id'], '\');" href="', $scripturl, '?action=arcade;pop=1;sa=highscore;game=', $context['game']['id'], ';edit;score=', $score['id'], ';reload=' . mt_rand(1, 9999) . '" class="floatright">', $edit_button, '</a>';
		elseif ($score['can_edit'] && !empty($score['edit']))
		{
			echo '
							<input type="hidden" name="score" value="', $score['id'], '" />
							<input type="text" name="new_comment" id="c', $score['id'], '" value="', $score['raw_comment'], '" maxlength="50" />
							<input onclick="myformxyz(\'commentform3\', 0);" class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />';
		}
		else
			echo $score['comment'];

		echo '
						</td>
						<td class="centertext">', $score['score'], '</td>';


		if ($context['arcade']['can_admin_arcade'])
			echo '
						<td class="windowbg2 centertext">
							<input type="checkbox" name="scores[]" value="', $score['id'], '" class="check" />
						</td>';

		echo '
					</tr>';
	}

	if ($context['arcade']['can_admin_arcade'])
	{
		echo '
				<tr>
					<td colspan="', $context['arcade']['can_admin_arcade'] ? '6' : '5', '" style="text-align: right;">
						<select name="qaction">
							<option value="">--------</option>
							<option value="delete">', $txt['arcade_delete_selected'], '</option>
						</select>
						<input value="', $txt['go'], '" onclick="return mycheckxyz()" class="button_submit" type="submit" />
					</td>
				</tr>';
	}

	echo '
			</table>
		</div>
	</form>';
	echo '
	</div>
	<script type="text/javascript">
		window.onload = function ()
		{
			var width = document.getElementById("scoresdiv").offsetWidth;
			var height = document.getElementById("scoresdiv").offsetHeight;
			window.resizeTo(width+20,height+100);
			self.focus();
		}
		function replaygame()
		{
			window.location.href = "' . $scripturl . '?action=arcade;sa=play;game=' . $context['game']['id'] . ';pop=1";
			window.resizeTo(' . ($context['game']['width'] + 20) . ',' . ($context['game']['height'] + 100) . ');
			self.focus();
		}
		function myformxyz(myform, myscore)
		{
			if (myscore > 0)
			{
				if (myform == "commentform3")
					var newercomment = document.forms[myform]["c" + myscore].value;
				else
				{
					var newercomment = document.forms[myform]["new_comment"].value;
					myform = "commentform3";
					myscore = document.forms[myform]["mynewscoreid"].value;
				}
				if (newercomment = "")
					newercomment = "', $txt['arcade_no_comment'], '";
				document.getElementById("comment" + myscore).innerHTML = newercomment;
				document.forms[myform]["c" + myscore].value = newercomment;
			}
			else if (myscore == -1)
			{
				var newguest = document.forms[myform]["name"].value;
				if (newguest == null || newguest == "")
				{
					alert("', $txt['arcade_comment_guestname'], '");
				}
				else
				{
					var checkguest = guestusername(newguest);
					if (checkguest)
					{
						document.forms[myform]["name"].value = newguest;
						document.getElementById(myform).submit();
						return true;
					}
				}

				return false;
			}
			document.getElementById(myform).submit();
		}
		function guestusername(newguestname)
		{
			var reg = new RegExp("[^a-zA-Z0-9]");
			if (reg.test(newguestname))
				alert("', $txt['arcade_comment_noguestname'], '");
			else
				return true;

			return false;
		}
		function mycheckxyz()
		{
			if (confirm(\'', $txt['arcade_are_you_sure'], '\'))
				return true;
			else
				return false;
		}
		function enterkey(event, myscore)
		{
			var code = (event.keyCode ? event.keyCode : event.which);
			if(code == 13) {
				var newercomment = document.getElementById("c" + myscore).value;
				document.getElementById("c" + myscore).value = newercomment;
				document.getElementById("commentform3").submit();
				return true;
			}
			return false;
		}
	</script>
   </body>
</html>';
	die();
}
?>