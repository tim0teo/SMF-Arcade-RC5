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
	global $scripturl, $txt, $context, $settings, $boardurl;
	$context['show_pm_popup'] = !empty($context['show_pm_popup']) ? true : false;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
   <head><meta http-equiv="Content-Type"
content="text/html;charset=',$context['character_set'],'" />
		<meta name="description" content="', $context['page_title_html_safe'], '" />
	<meta name="keywords" content="', $context['meta_keywords'], '" />
    <title>', $context['forum_name_html_safe'], '</title>
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?rc1"></script>
	<script type="text/javascript">
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_charset = "', $context['character_set'], '";', $context['show_pm_popup'] ? '
		if (confirm("' . $txt['show_personal_messages'] . '"))
			window.open(smf_prepareScriptUrl(smf_scripturl) + "action=pm");' : '', '
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
   </head>';
	$check_block = !empty($_REQUEST['block']) ? (int) $_REQUEST['block'] : 0;
	if ($check_block == 1)
	{
		echo '
<!--[if IE]>
    <body class="windowbg" id="html_page1">
		<div style="text-align:center;">
<![endif]-->
<!--[if !IE]><!-->
	<body style="background-color: transparent;" id="html_page1">
    <div style="background-color: transparent;text-align:center;">
<!--<![endif]-->';
	}
	else
	{
		echo '<body class="windowbg" id="html_page1">
      <div style="text-align:center;">';
	}

	echo '<div style="margin-top:0ex;text-align: center; font-style: italic; font-weight: bold; font-size: 8pt;"><a href="javascript:location.reload(true);">'.$context['game']['name'].'</a></div><br />';

	echo '<div style="position:absolute;margin-right: auto; left:2px; right: 2px; bottom:0.5px; top:3ex;">';


	echo '<div id="gamearea1" style="overflow: hidden;">
			', $context['game']['html']($context['game'], true), '
			', !$context['arcade']['can_submit'] ? '<br /><strong>' . $txt['arcade_cannot_save'] . '</strong>' : '', '
		<br /><br /><br /><br /><br /></div></div><br />';
	echo '</div>
   </body>
</html>';
}

function arcadePopHighscoreTemplate()
{
	global $scripturl, $txt, $context, $settings, $boardurl, $modSettings;

	if (empty($context['arcade']['buttons']))
		$context['arcade']['buttons'] = array();

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
   <head>
	<meta http-equiv="Content-Type" content="text/html;charset=',$context['character_set'],'" />
	<meta name="description" content="', $context['page_title_html_safe'], '" />
	<meta name="keywords" content="', $context['meta_keywords'], '" />
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
      <div style="text-align:center;">
      <![endif]-->

<!--[if !IE]><!-->
<body style="background-color: transparent;" id="html_page1">
      <div style="background-color: transparent;text-align:center;">
<!--<![endif]-->';
	}
	else
	{
		echo '
	<body class="windowbg" id="html_page1">
		<div style="text-align:center;">
		<script type="text/javascript">
			var highUrl = "', $scripturl, '?action=arcade;sa=highscore;game=', $context['game']['id'], '";
			window.opener.location.href = highUrl;
		</script>';
	}

	if (isset($context['arcade']['submit']))
	{
		if ($context['arcade']['submit'] == 'newscore') // Was score submitted
		{
			$score = &$context['arcade']['new_score'];
			echo '
		<div style="text-align:left;">
			<h3 class="catbg">
				<span class="left"></span>
				<span class="right"></span>
				<span style="float:left;border:0px;background: url(',$settings['actual_theme_url'],'/images/theme/main_block.png) no-repeat 0% -160px;">&nbsp;</span>
				<span>', $txt['arcade_submit_score'], '</span>
			</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
		<div style="padding: 0 0.5em">';

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
				<form action="', $scripturl, '?action=arcade;sa=highscore;game=', $context['game']['id'], ';score=',  $score['id'], '" method="post">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="p" value="0" />
					<input type="text" id="new_comment" name="new_comment" style="width: 95%;" />
					<input class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />
				</form>
			</div>';
			}

			echo '
		</div>
		<span class="botslice"><span></span></span>
	</div>
	<br />';
		}
		elseif ($context['arcade']['submit'] == 'askname')
		{
			echo '
		<div style="text-align:left;">
			<h3 class="catbg">
			<span class="left"></span>
			<span class="right"></span>
			<span style="float:left;border:0px;background: url(',$settings['actual_theme_url'],'/images/theme/main_block.png) no-repeat 0% -160px;">&nbsp;</span>
			<span>', $txt['arcade_submit_score'], '</span>
			</h3></div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
		<div style="padding: 0 0.5em">
			<form action="', $scripturl, '?action=arcade;sa=save" method="post">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="text" name="name" style="width: 95%;" />
				<input type="hidden" name="p" value="0" />
				<input class="button_submit" type="submit" value="', $txt['arcade_save'], '" />
			</form>
		</div>
	</div><br />';
		}
	}
	echo '
	<div class="pagesection">
		<div class="align_left">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#bot"><b>' . $txt['go_down'] . '</b></a>' : '', '</div>
	</div>
	<form name="score" action="', $scripturl, '?action=arcade;sa=highscore;" method="post">
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		<input type="hidden" name="p" value="0" />
		<input type="hidden" name="game" value="', $context['game']['id'], '" />
		<div style="text-align:left;">
		<h3 class="catbg">
			<span class="left"></span>
			<span class="right"></span>
			<span style="float:left;border:0px;background: url(',$settings['actual_theme_url'],'/images/theme/main_block.png) no-repeat 0% -160px;">&nbsp;</span>
			<span>', $txt['arcade_highscores'], '</span>
			<span class="floatright">', '<a href = "'.$scripturl . '?action=arcade;sa=play;game=' . $context['game']['id'] . ';gamepopup=1">',$txt['arcade_replay'] ,'</a>' , '</span>

		</h3></div>
		<div class="score_table">
			<table cellspacing="0" class="table_grid">
				<thead>
					<tr>';

	/* Is there games? */
	if (!empty($context['arcade']['scores']))
	{
			echo '
						<th scope="col" class="smalltext first_th" width="5">', $txt['arcade_position'], '</th>
						<th scope="col" class="smalltext">', $txt['arcade_member'], '</th>
						<th scope="col" class="smalltext"> ', $txt['arcade_comment'], '</th>
						<th scope="col" class="smalltext', !$context['arcade']['can_admin_arcade'] ? ' last_th' : '', '">', $txt['arcade_score'], '</th>';

		if ($context['arcade']['can_admin_arcade'])
			echo '
						<th scope="col" class="smalltext last_th" align="center" width="15"><input type="checkbox" onclick="invertAll(this, this.form, \'scores[]\');" class="check" /></th>';
	}
	else
	{
		echo '
						<th scope="col" class="smalltext first_th" width="8%">&nbsp;</th>
						<th class="smalltext" colspan="', !$context['arcade']['can_admin_arcade'] ? '2' : '3', '"><strong>', $txt['arcade_no_scores'], '</strong></th>
						<th scope="col" class="smalltext last_th" width="8%">&nbsp;</th>';
	}

	echo '
					</tr>
				</thead>
				<tbody>';

	$edit_button = '<span style="width: 16px;height: 16px;display: inline-block;background: url(' . $settings['default_theme_url'] . '/images/arcade_edit.gif) no-repeat;vertical-align: middle;"></span>';
	foreach ($context['arcade']['scores'] as $score)
	{
		$div_con = addslashes(sprintf($txt['arcade_when'], $score['time'], duration_format($score['duration'])));

		echo '
					<tr class="', $score['own'] ? 'windowbg3' : 'windowbg', '"', !empty($score['highlight']) ? ' style="font-weight: bold;"' : '', ' onmouseover="arcadeBox(\'', $div_con, '\')" onmousemove="arcadeBoxMove(event)" onmouseout="arcadeBox(\'\')">
						<td class="windowbg2" align="center">', $score['position'], '</td>
						<td>', $score['member']['link'], '</td>
						<td width="300" class="windowbg2">';

		if ($score['can_edit'] && empty($score['edit']))
			echo '
							<div id="comment', $score['id'], '" class="floatleft">
								', $score['comment'], '
							</div>
							<div id="edit', $score['id'], '" class="floatleft" style="display: none;">
								<input type="text" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;"  />
								<input type="hidden" name="p" value="0" />
								<input type="button" onclick="arcadeCommentEdit(', $score['id'], ', ', $context['game']['id'], ', 1); return false;" name="csave" value="', $txt['arcade_save'], '" />
							</div>
							<a id="editlink', $score['id'], '" onclick="arcadeCommentEdit(', $score['id'], ', ', $context['game']['id'], ', 0); return false;" href="', $scripturl, '?action=arcade;sa=highscore;game=', $context['game']['id'], ';edit;score=', $score['id'], '" class="floatright">', $edit_button, '</a>';
		elseif ($score['can_edit'] && !empty($score['edit']))
		{
			echo '
							<input type="hidden" name="score" value="', $score['id'], '" />
							<input type="hidden" name="p" value="0" />
							<input type="text" name="new_comment" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" />
							<input class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />';
		}
		else
			echo $score['comment'];

		echo '
						</td>
						<td align="center">', $score['score'], '</td>';


		if ($context['arcade']['can_admin_arcade'])
			echo '
						<td class="windowbg2" align="center">
							<input type="checkbox" name="scores[]" value="', $score['id'], '" class="check" />
						</td>';

		echo '
					</tr>';
	}

	echo '
			</tbody>';

	if ($context['arcade']['can_admin_arcade'])
	{
		echo '
			<tfoot>
				<tr>
					<td colspan="', $context['arcade']['can_admin_arcade'] ? '6' : '5', '" align="right">
						<select name="qaction">
							<option value="">--------</option>
							<option value="delete">', $txt['arcade_delete_selected'], '</option>
						</select>
						<input value="', $txt['go'], '" onclick="return document.forms.score.qaction.value != \'\' && confirm(\'', $txt['arcade_are_you_sure'], '\');" class="button_submit" type="submit" />
					</td>
				</tr>
			</tfoot>';
	}

	echo '
			</table>
		</div>
	</form>';
	echo '</div>
   </body>
</html>';
}
?>