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

function template_arcade_above()
{
	global $settings, $context, $txt, $modSettings, $scripturl, $db_count, $user_info;

	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'highscore')
	{
		echo '
	<div class="cat_bar" style="clear: both;position: relative;bottom: -15px;">
		<h3 class="catbg centertext">
			<span class="centertext" style="clear: left;width: 100%;vertical-align: middle;">', $txt['arcade_title'], '</span>
		</h3>
	</div>';
		return;
	}

	echo '
	<div style="display: none;" id="arcadeHiddenInfo">
		<div id="pausecontent0">', ArcadeInfoBestPlayers(5), '</div>
		<div id="pausecontent1">', ArcadeInfoNewestGames(5), '</div>
		<div id="pausecontent2">', Arcade3champsBlock(5), '</div>
		<div id="pausecontent3">', ArcadeInfoMostPlayed(5), '</div>
		<div id="pausecontent4">', ArcadeInfoLongestChamps(5), '</div>
		<div id="pausecontent5">', ArcadeGOTDBlock(), '</div>
		<div id="pausecontent6">', ArcadeRandomGameBlock(), '</div>
	</div>
	<div><span style="display: none;">&nbsp;</span></div>
	<div class="cat_bar">
		<h3 class="catbg centertext">
			<span class="centertext" style="clear: left;width: 100%;vertical-align: middle;">', $txt['arcade_title'], '</span>
		</h3>
	</div>
	', $context['arcade_smf_version'] == 'v2.1' ? '
	<div class="up_contain windowbg" style="padding: 0px;border: 0px;">' :
	'<span class="clear upperframe"><span>&nbsp;</span></span>
	<div class="roundframe">', '
		<div style="display: inline;border: 0px;">';

	$curr = 1;
	$selected = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? ' selected="selected"' : ' selected';
	echo '
			<table class="bordercolor"  style="border: 0px;width: 100%;border-spacing: 1px;border-collapse: separate;">', (!empty($context['arcade']['notice']) ? '
				<tr class="windowbg2">
					<td colspan="3" class="centertext alert" style="padding: 5px;">' . $context['arcade']['notice'] . '</td>
				</tr>' : '');

	//start arcade news
	if (!empty($modSettings['arcadeNewsFader']))
	{
		//CACHE NEWS FADER
		if (($cacheFader = ArcadeInfoFader()) !== '')
		{
			echo'
				<tr>
					<td style="height: 50px;padding: 5px;" class="windowbg2" colspan="3">
						<div style="display: none;">', $cacheFader, '</div>
						<div class="centertext">
							<script type="text/javascript"><!-- // --><![CDATA[
								var delay = 10000;
								var maxsteps=30;
								var stepdelay=40;
								var startcolor= new Array(255,255,255);
								var endcolor=new Array(0,0,0);
								var fcontent=new Array();
								var arcadeNewzDiv = arcadeNewsDiv();
								begintag=arcadeNewzDiv[0];
								fcontent = arcadeNewsFader(', (!empty($modSettings['arcadeNewsNumber']) ? (int)$modSettings['arcadeNewsNumber'] : 0), ');
								closetag=arcadeNewzDiv[1];
								var fwidth=\'100%\';
								var fheight=\'30px\';
								var ie4=document.all&&!document.getElementById;
								var DOM2=document.getElementById;
								var index=0;
								function changecontent(){
									if (index>=fcontent.length)
										index=0
									if (DOM2){
										document.getElementById("fscroller").innerHTML=begintag+fcontent[index]+closetag
										setTimeout("changecontent()", delay);
									}
									else if (ie4)
										document.all.fscroller.innerHTML=begintag+fcontent[index]+closetag;
									index++;
								}
								if (ie4||DOM2)
									document.write(arcadeIe4(fwidth, fheight));
								if (window.addEventListener)
									window.addEventListener("load", changecontent, false)
								else if (window.attachEvent)
									window.attachEvent("onload", changecontent)
								else if (document.getElementById)
									window.onload=changecontent
							// ]]></script>
						</div>
					</td>
				</tr>';
		}
	}

	echo '
				<tr>
					<td class="windowbg2" style="padding: 5px;vertical-align: top;width: 17%;border-bottom-left-radius: 0px;border-bottom-right-radius: 0px;">
						<table style="border-collapse: collapse;width: 100%;border: 0px;">
							<tr>
								<td style="padding: 1px;">
									<div class="centertext"><span style="font-style: italic;"><strong>', $txt['arcade_info'], '</strong></span></div>
								</td>
							</tr>
							<tr>
								<td style="padding: 1px;">
									<div style="display: inline;" class="middletext" id="arcadescroller">
										<script type="text/javascript"><!-- // --><![CDATA[
											var pausecontent=new Array();

											', ArcadeInfoPanelBlock(), '
											function pauseescroller(content, divId, divClass, delay)
											{
												this.content=content;
												this.tickerid=divId;
												this.delay=delay;
												this.mouseoverBol=0;
												this.hiddendivpointer=1;
												document.write(arcadeInfoScroll(divId, divClass, content));
												var escrollerinstance=this;
												if (window.addEventListener)
													window.addEventListener("load", function(){escrollerinstance.initialize();}, false);
												else if (window.attachEvent)
													window.attachEvent("onload", function(){escrollerinstance.initialize();});
												else if (document.getElementById)
													setTimeout(function(){escrollerinstance.initialize();}, 500);
											}
											pauseescroller.prototype.initialize=function(){
												this.tickerdiv=document.getElementById(this.tickerid);
												this.visiblediv=document.getElementById(this.tickerid+"1");
												this.hiddendiv=document.getElementById(this.tickerid+"2");
												this.visibledivtop=parseInt(pauseescroller.getCSSpadding(this.tickerdiv));
												this.visiblediv.style.width=this.hiddendiv.style.width=this.tickerdiv.offsetWidth-(this.visibledivtop*2)+"px";
												this.getinline(this.visiblediv, this.hiddendiv);
												this.hiddendiv.style.visibility="visible";
												var escrollerinstance=this;
												document.getElementById(this.tickerid).onmouseover=function(){escrollerinstance.mouseoverBol=1};
												document.getElementById(this.tickerid).onmouseout=function(){escrollerinstance.mouseoverBol=0};
												if (window.attachEvent)
													window.attachEvent("onunload", function(){escrollerinstance.tickerdiv.onmouseover=escrollerinstance.tickerdiv.onmouseout=null;});
												else if (window.addEventListener)
													window.addEventListener("unload", function(){escrollerinstance.tickerdiv.onmouseover=escrollerinstance.tickerdiv.onmouseout=null;}, false);
												else if (document.getElementById)
													setTimeout(function(){escrollerinstance.tickerdiv.onmouseover=escrollerinstance.tickerdiv.onmouseout=null;}, escrollerinstance.delay);

												setTimeout(function(){escrollerinstance.animateup();}, escrollerinstance.delay);
											}

											pauseescroller.prototype.animateup=function(){
												var escrollerinstance=this;
												if (parseInt(this.hiddendiv.style.top)>(this.visibledivtop+5))
												{
													this.visiblediv.style.top=parseInt(this.visiblediv.style.top)-5+"px";
													this.hiddendiv.style.top=parseInt(this.hiddendiv.style.top)-5+"px";
													setTimeout(function(){escrollerinstance.animateup();}, 50);
												}
												else
												{
													this.getinline(this.hiddendiv, this.visiblediv);
													this.swapdivs();
													setTimeout(function(){escrollerinstance.setmessage();}, this.delay);
												}
											}
											pauseescroller.prototype.swapdivs=function(){
												var tempcontainer=this.visiblediv;
												this.visiblediv=this.hiddendiv;
												this.hiddendiv=tempcontainer;
											}
											pauseescroller.prototype.getinline=function(div1, div2){
												div1.style.top = this.visibledivtop + "px";
												div2.style.top = Math.max(div1.parentNode.offsetHeight, div1.offsetHeight) + "px";
											}
											pauseescroller.prototype.setmessage=function(){
												var escrollerinstance=this;
												if (this.mouseoverBol==1)
													setTimeout(function(){escrollerinstance.setmessage();}, 200);
												else
												{
													var i=this.hiddendivpointer;
													var ceiling=this.content.length;
													this.hiddendivpointer = (i+1>ceiling-1)? 0 : i+1;
													this.hiddendiv.innerHTML=this.content[this.hiddendivpointer];
													this.animateup();
												}
											}
											pauseescroller.getCSSpadding=function(tickerobj){
												if (window.getComputedStyle)
													return window.getComputedStyle(tickerobj, "").getPropertyValue("padding-top");
												else if (tickerobj.currentStyle)
													return tickerobj.currentStyle["paddingTop"];
												else
													return 0;
											}
											new pauseescroller(pausecontent, "pescroller1", "someclass", 6000);
											var myscroller = document.getElementById("pescroller1");
											myscroller.style.height = "220px";
											myscroller.style.borderWidth = "0px";
											myscroller.style.padding = "5px";
											myscroller.style.position = "relative";
											myscroller.style.overflow = "hidden";
											myscroller.className = "someclass";
										// ]]></script>
										<div><span style="display: none;">&nbsp;</span></div>
									</div>
								</td>
							</tr>
						</table>
					</td>
					<td class="windowbg" style="vertical-align: top;padding: 5px;border-bottom-left-radius: 0px;border-bottom-right-radius: 0px;">
						<div class="centertext">
							<table style="border: 0px;width: 100%;border-collapse: collapse;">
								<tr>
									<td style="padding: 1px;">
										<div class="centertext"><span style="font-style: italic;"><strong>', $txt['arcade_u_b_1'], '&nbsp;', $user_info['name'], '</strong></span></div>
									</td>
								</tr>
								<tr>
									<td style="padding: 1px;height: 155px;">
										<div class="centertext">
											', (!empty($context['user']['avatar']['href']) ? '<img style="width: ' . $context['arcade_user_avatar'][0] . 'px;height: ' . $context['arcade_user_avatar'][1] . 'px;" alt="&nbsp;" src="' . $context['user']['avatar']['href'] . '" />' : '<img style="border: 0px;width:' . (!empty($modSettings['skin_avatar_sizeb_width']) && (int)$modSettings['skin_avatar_sizeb_width'] > 0 ? $modSettings['skin_avatar_sizeb_width'] . 'px;' : '50px;') . 'height: ' . (!empty($modSettings['skin_avatar_sizeb_height']) && (int)$modSettings['skin_avatar_sizeb_height'] > 0 ? $modSettings['skin_avatar_sizeb_height'] . 'px;' : '50px;') . '" src="' . $settings['default_images_url'] . '/arc_icons/noavatar.gif" alt="&nbsp;" title="' . $txt['arcade_info_defavatar'] . '"/>') , '
											<div><span style="display: none;">&nbsp;</span></div>
											<div><span style="display: none;">&nbsp;</span></div>
											<div class="smalltext">
												<a href="', $scripturl, '?action=arcade;favorites">
													<img style="border: 0px;width: 15px;height: 15px;" src="', $settings['default_images_url'], '/arc_icons/arcade_cat1.gif" alt="&nbsp;" title="' . $txt['arcade_info_fav'] . '" /> ', $txt['arcade_u_b_2'], '
													<img style="border: 0px;width: 15px;height: 15px;" src="' . $settings['default_images_url'] . '/arc_icons/arcade_cat1.gif" alt="&nbsp;" title="' . $txt['arcade_info_fav'] . '" />
												</a>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<td style="padding: 1px;" class="smalltext">
										<div class="centertext">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</div>
									</td>
								</tr>
								<tr>
									<td style="padding: 1px;">
										<form accept-charset="', $context['character_set'], '" class="smalltext" style="padding: 0; margin: 0; margin-top: 5px; text-align: center;" name="arcade_shout" action="', $scripturl, '?action=arcade;sa=shout" method="post">
											<input size="105" maxlength="100" onkeypress="submitShoutOnEnter(this, event);" class="largetext" name="the_shout" style="width: 80%;margin-top: 1ex; height: 25px;" />
											<div><span style="display: none;">&nbsp;</span></div>
											<input style="margin-top: 4px;" class="mediumtext" type="submit" name="shout" value="', $txt['arcade_shout'], '" />
										</form>
									</td>
								</tr>
							</table>
						</div>
					</td>
					<td class="windowbg2" style="padding: 5px;vertical-align: top;width: 17%;border-bottom-left-radius: 0px;border-bottom-right-radius: 0px;">
						<table class="centertext" style="table-layout: fixed;width: 100%;border-collapse: collapse;">
							<tr>
								<td class="centertext" style="padding: 0px;">
									<span style="font-style: italic;"><strong>', $txt['arcade_shouts'], '</strong></span>
								</td>
							</tr>
							<tr>
								<td style="padding: 0px;">
									<div class="smalltext" style="width: 99%; height: 250px; overflow: auto;">
										', ArcadeInfoShouts(), '
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="3" class="windowbg2" style="vertical-align: top;width: 100%;border-top-left-radius: 0px;border-top-right-radius: 0px;">
						<table style="width: 100%;border-collapse: collapse;border: 0px;">
							<tr>
								<td style="padding-bottom: 15px;padding-top: 0px;vertical-align: top;" class="centertext" colspan="3">
									<span style="font-style: italic;"><strong>', $txt['arcade_search'], '</strong></span>
								</td>
							</tr>
							<tr>
								<td style="width: 17%;"><span style="display: none;">&nbsp;</span></td>
								<td style="padding: 3px;" class="centertext">
									<div style="clear: both;display: inline;float: left;padding-left: 30px;">
										<form name="search" action="', $scripturl, '?action=arcade;sa=search" method="post" onsubmit="return empty();">
											<input maxlength="100" style="width: 180px;" id="gamesearch" type="text" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '" />
										</form>
									</div>
									<div id="quick_div" class="smalltext centertext" style="display: inline;">', $txt['arcade_search_text'], '</div>
									<div style="clear: right;display: inline;float: right;padding-right: 30px;">
										<form action="', $scripturl, '?action=arcade;sa=list" method="post" id="sortgames">
											<select style="width: 180px;" name="sortby" onchange="this.form.submit();">
												<option value="reset">', $txt['arcade_list_games'], '</option>
												<option value="a2z"' . ($_SESSION['arcade_sortby'] === 'a2z' ? $selected : '') . '>', $txt['arcade_nameAZ'], '</option>
												<option value="z2a"' . ($_SESSION['arcade_sortby'] === 'z2a' ? $selected : '') . '>', $txt['arcade_nameZA'], '</option>
												<option value="age"' . ($_SESSION['arcade_sortby'] === 'age' ? $selected : '') . '>', $txt['arcade_LatestList'], '</option>
												<option value="plays"' . ($_SESSION['arcade_sortby'] === 'plays' ? $selected : '') . '>', $txt['arcade_g_i_b_3'], '</option>
												<option value="champs"' . ($_SESSION['arcade_sortby'] === 'champs' ? $selected : '') . '>', $txt['arcade_g_i_b_8'], '</option>
												<option value="plays_reverse"' . ($_SESSION['arcade_sortby'] === 'plays_reverse' ? $selected : '') . '>', $txt['arcade_LeastPlayed'], '</option>
												<option value="cats"' . ($_SESSION['arcade_sortby'] === 'cats' ? $selected : '') . '>', $txt['arcade_category'], '</option>
												<option value="rating"' . ($_SESSION['arcade_sortby'] === 'rating' ? $selected : '') . '>', $txt['arcade_rating_sort'], '</option>', (!$user_info['is_guest'] ? '
												<option value="favorites"' . ($_SESSION['arcade_sortby'] === 'favorites' ? $selected : '') . '>' . $txt['arcade_personal_best'] . '</option>' : ''), '
												<option value="champion"' . ($_SESSION['arcade_sortby'] === 'champion' ? $selected : '') . '>', $txt['arcade_champion'], '</option>
											</select>
										</form>
									</div>
								</td>
								<td style="width: 17%;"><span style="display: none;">&nbsp;</span></td>
							</tr>
							<tr>
								<td class="centertext" style="padding: 3px;" colspan="3"><hr /></td>
							</tr>
							<tr>
								<td style="clear: both;padding-top: 0px;padding-bottom: 15px;vertical-align: top;" class="centertext" colspan="3">
									<span style="font-style: italic;"><strong>', $txt['arcade_Gamecategory'], '</strong></span>
								</td>
							</tr>
						</table>
						<table style="width: 100%;border-collapse: collapse;border: 0px;">
							<tr>';

	//START CACHE - get the categories from the cache else query them anew
	if (!empty($modSettings['enable_arcade_cache']))
	{
		if (($cacheCats = cache_get_data('arcade_cats', 604800)) == null)
		{
			$cats = category_games();
			echo '
								<td style="padding: 3px;width: 25%;text-align: left;">&nbsp;&nbsp;
									<a href="', $scripturl, '?action=arcade;sa=list;sortby=age;">
										<img class="icon" style="vertical-align: middle;border: 0px;width: ', $context['arcade_defiant']['cat_width'],'px;height: ', $context['arcade_defiant']['cat_height'], 'px;" src="', $settings['default_images_url'], '/arc_icons/cat_new.gif" alt="&nbsp;" title="', $txt['arcade_info_showlate'], '"/>
										<span>&nbsp;', $txt['arcade_LatestGames'], '&nbsp;(', $modSettings['gamesPerPage'], ')</span>
									</a>
								</td>';
			foreach($cats as $id => $tmp)
			{
				if ($curr % $context['arcade_defiant']['per_line'] == 0)
				{
					echo '
							</tr>
							<tr>';
				}
				echo'
								<td style="padding: 3px;width: 25%;text-align: left;">&nbsp;&nbsp;
									<a href="', $scripturl, '?action=arcade;category=', $tmp['id_category'], '">
										<img class="icon" style="vertical-align: middle;border: 0px;width: ',$context['arcade_defiant']['cat_width'],'px;height: ',$context['arcade_defiant']['cat_height'],'px;" src="', $settings['default_images_url'], '/arc_icons/', $tmp['cat_icon'], '" alt="&nbsp;" title="', sprintf($txt['arcade_info_showcat'], $tmp['category_name']), '" />
										<span style="vertical-align: middle;">&nbsp;', $tmp['category_name'], '&nbsp;(', $tmp['games'], ')</span>
									</a>
								</td>';
				$curr++;
			}
			cache_put_data('arcade_cats', $cats, 604800);
		}
		else
		{
			echo '
								<td style="padding: 3px;width: 25%;text-align: left;">&nbsp;&nbsp;
									<a href="',$scripturl,'?action=arcade;sa=list;sortby=age;">
										<img class="icon" style="vertical-align: middle;border: 0px;width: ',$context['arcade_defiant']['cat_width'],'px;height: ',$context['arcade_defiant']['cat_height'],'px;" src="', $settings['default_images_url'], '/arc_icons/cat_new.gif" alt="&nbsp;" title="', $txt['arcade_info_showlate'], '"/>
										&nbsp;', $txt['arcade_LatestGames'], '&nbsp;(', $modSettings['gamesPerPage'], ')
									</a>
								</td>';
			foreach($cacheCats as $id => $tmp)
			{
				if ($curr % $context['arcade_defiant']['per_line'] == 0)
				{
					echo '
							</tr>
							<tr>';
				}
				echo'
								<td style="padding: 3px;width: 25%;text-align: left;">&nbsp;&nbsp;
									<a href="', $scripturl, '?action=arcade;category=', $tmp['id_category'], '">
										<img class="icon" style="vertical-align: middle;border: 0px;width: ',$context['arcade_defiant']['cat_width'],'px;height: ',$context['arcade_defiant']['cat_height'],'px;" src="',$settings['default_images_url'], '/arc_icons/', $tmp['cat_icon'], '" alt="&nbsp" title="', sprintf($txt['arcade_info_showcat'], $tmp['category_name']), '" />
											&nbsp;', $tmp['category_name'], '&nbsp;(', $tmp['games'], ')
									</a>
								</td>';
				$curr++;
			}
		}
	}
	else
	{
		$cats = category_games();
		echo '
								<td style="padding: 3px;width: 25%;text-align: left;">&nbsp;&nbsp;
									<a href="',$scripturl,'?action=arcade;sa=list;sortby=age;">
										<img class="icon" style="vertical-align: middle;border: 0px;width: ',$context['arcade_defiant']['cat_width'],'px;height: ',$context['arcade_defiant']['cat_height'],'px;" src="',$settings['default_images_url'],'/arc_icons/cat_new.gif" alt="&nbsp;" title="', $txt['arcade_info_showlate'], '"/>
										&nbsp;', $txt['arcade_LatestGames'], '&nbsp;(', $modSettings['gamesPerPage'], ')
									</a>
								</td>';
		foreach($cats as $id => $tmp)
		{
			if ($curr % $context['arcade_defiant']['per_line'] == 0)
			{
				echo '
							</tr>
							<tr>';
			}
			echo'
								<td style="padding: 3px;width: 25%;text-align: left;">&nbsp;&nbsp;
									<a href="', $scripturl, '?action=arcade;category=', $tmp['id_cat'], '">
										<img class="icon" style="vertical-align: middle;border: 0px;width: ',$context['arcade_defiant']['cat_width'],'px;height: ',$context['arcade_defiant']['cat_height'],'px;" src="',$settings['default_images_url'], '/arc_icons/', $tmp['cat_icon'], '" alt="&nbsp;" title="', sprintf($txt['arcade_info_showcat'], $tmp['cat_name']), '" />
											&nbsp;', $tmp['cat_name'], '&nbsp;(', $tmp['games'], ')
									</a>
								</td>';
			$curr++;
		}
	}

	echo '
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<script type="text/javascript">
			function submitShoutOnEnter(inputElement, event) {
				if (event.keyCode == 13)
					inputElement.form.submit();
			}
			</script>
		</div>
	</div>', ($context['arcade_smf_version'] !== 'v2.1' ? '
	<span class="lowerframe"><span>&nbsp;</span></span>' : ''), '
	<div style="clear: both;display: inline;width: 100%;">
		<div style="display: inline;">
			', Arcade_DoToolBarStrip('index', 'left', ''), '
		</div>';

	if ($context['arcade']['stats']['games'] != 0)
			echo '
			<span class="smalltext" style="clear: right;padding:8px 7px 0px 0px;float: right;">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</span>';

	echo '
	</div>', ($context['arcade_smf_version'] == 'v2.1' ? '
	<span class="lowerframe"><span>&nbsp;</span></span>' : ''), '
	<div style="clear: both;padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
}

function template_arcade_below()
{
	global $txt;
	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
	<a id="bot"></a>', $txt['pdl_arcade_copyright'];
}
?>