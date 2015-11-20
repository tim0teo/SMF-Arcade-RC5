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

// E-Arcade sub-routines
function Arcade_DoToolBarStrip($button_strip, $direction)
{
	global $modSettings, $txt;
	
	if (!empty($modSettings['arcadeTabs']))
		template_button_strip($button_strip, $direction);
	else
	{
		foreach ($button_strip as $tab)
		{
			echo '
			<a href="', $tab['url'], '">', $txt[$tab['text']], '</a>';

			if (empty($tab['is_last']))
				echo '&nbsp;|&nbsp;';
		}
	}
}
?>