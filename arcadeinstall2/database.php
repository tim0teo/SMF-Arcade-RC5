<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $db_prefix, $smcFunc, $sourcedir;

$inserts = '';

$skin_settings = array(
		'skin_showcatchamps' => 1,
		'skin_latest_scores' => 5,
		'skin_latest_champs' => 5,
		'skin_latest_games' => 5,
		'skin_most_popular' => 5,
		'skin_avatar_size' => 65,
);

foreach ($skin_settings as $key => $value)

     $result = $smcFunc['db_query']
('','INSERT IGNORE INTO {db_prefix}settings VALUES ({string:variable}, {int:value})',
		array(
			'variable' => $key,
			'value' => $value,
			)
		);


if ($result === false)
        echo '<b>Error:</b> Database modifications failed!';
?>