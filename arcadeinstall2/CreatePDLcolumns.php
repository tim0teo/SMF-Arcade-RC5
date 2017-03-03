<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

db_extend('packages');
addPDLcolumn1();
addPDLcolumn2();
deleteFile();

return;

/* Check if the column exists */
function checkFieldPDL($tableName,$columnName)
{
	if (check_table_existsPDL($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		if (in_array($columnName, $check))
			return true;
	}

	return false;
}

/*  Returns amount of columns in a table  */
function checkTablePDL($tableName)
{
	global $smcFunc;

	if (check_table_existsPDL($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		return !empty($check) ? count($check) : false;
	}
	return false;
}

/*  Check if table exists  */
function check_table_existsPDL($table)
{
	global $db_prefix, $smcFunc;

	if ($smcFunc['db_list_tables'](false, $db_prefix . $table))
		return true;

	return false;
}

// Add extra needed tables/columns if they do not exist
function addPDLcolumn1()
{
	global $smcFunc;

	$columns = array(
		array(
			'name' => 'id_member',
			'type' => 'int',
			'size' => 10,
			'unsigned' => true,
			'auto' => false,
		),
		array(
			'name' => 'count',
			'type' => 'int',
			'size' => 10,
			'unsigned' => true,
		),
		array(
			'name' => 'year',
			'type' => 'varchar',
			'default' => '',
			'size' => 255,
		),
		array(
			'name' => 'day',
			'type' => 'varchar',
			'default' => '',
			'size' => 255,
		),
		array(
			'name' => 'latest_year',
			'type' => 'varchar',
			'default' => '',
			'size' => 255,
		),
		array(
			'name' => 'latest_day',
			'type' => 'varchar',
			'default' => '',
			'size' => 255,
		),
		array(
			'name' => 'permission',
			'type' => 'int',
			'size' => 10,
			'unsigned' => true,
		),
	);

	$indexes = array(
		array(
			'type' => 'primary',
			'columns' => array('id_member')
		),
	);

	if (!check_table_existsPDL('arcade_pdl1'))
	{
		$smcFunc['db_create_table']('{db_prefix}arcade_pdl1', $columns, $indexes, array(), 'ignore');
		return true;
	}

	$check = $smcFunc['db_list_columns'] ('{db_prefix}arcade_pdl1', false, array());
	$columns_ref = array('id_member', 'count', 'year', 'day', 'latest_year', 'latest_day', 'permission');
	$compare = array_diff($columns_ref, $check);
	foreach ($compare as $key => $add)
	{
		$smcFunc['db_add_column'](
			'{db_prefix}arcade_pdl1',
			$column[$key]
		);
	}
}

function addPDLcolumn2()
{
	global $smcFunc;

	$columns = array(
		array(
			'name' => 'pdl_gameid',
			'type' => 'int',
			'size' => 10,
			'unsigned' => true,
			'auto' => false,
		),
		array(
			'name' => 'gamename',
			'type' => 'varchar',
			'default' => '',
			'size' => 255,
		),
		array(
			'name' => 'report_day',
			'type' => 'varchar',
			'default' => '',
			'size' => 255,
		),
		array(
			'name' => 'report_year',
			'type' => 'varchar',
			'default' => '',
			'size' => 255,
		),
		array(
			'name' => 'user_id',
			'type' => 'int',
			'size' => 10,
			'unsigned' => true,
		),
		array(
			'name' => 'report_id',
			'type' => 'int',
			'size' => 10,
			'unsigned' => true,
		),
		array(
			'name' => 'download_count',
			'type' => 'int',
			'size' => 10,
			'unsigned' => true,
		),
		array(
			'name' => 'download_disable',
			'type' => 'int',
			'size' => 10,
			'unsigned' => true,
		),
	);

	$indexes = array(
		array(
			'type' => 'primary',
			'columns' => array('pdl_gameid')
		),
	);

	if (!check_table_existsPDL('arcade_pdl2'))
	{
		$smcFunc['db_create_table']('{db_prefix}arcade_pdl2', $columns, $indexes, array(), 'ignore');
		return true;
	}

	$check = $smcFunc['db_list_columns'] ('{db_prefix}arcade_pdl2', false, array());
	$columns_ref = array('pdl_gameid', 'game_name', 'report_day', 'report_year', 'user_id', 'report_id', 'download_count', 'download_disable');
	$compare = array_diff($columns_ref, $check);
	foreach ($compare as $key => $add)
	{
		$smcFunc['db_add_column'](
			'{db_prefix}arcade_pdl2',
			$column[$key]
		);
	}

	return true;
}

function deleteFile()
{
	if (file_exists('games_download/index.php'))
		@unlink('games_download/index.php');

	return;
}
?>