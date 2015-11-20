<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.5
 * @license http://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
addPDLcolumn1();
addPDLcolumn2();
deleteFile();

return;

/* Check if the column exists */
function checkFieldPDL($tableName,$columnName)
{
	$checkTable = false;
	$checkTable = check_table_existsPDL($tableName);
	if ($checkTable == true)
	{
		global $db_prefix, $smcFunc;
		$check = false;
		$checkval = false;
		$check = $smcFunc['db_query']('', "DESCRIBE {$db_prefix}$tableName $columnName");
		$checkval = $smcFunc['db_num_rows']($check);
		$smcFunc['db_free_result']($check);
		if ($checkval > 0)
			return true;
	}

	return false;
}

/*  Returns amount of columns in a table  */
function checkTablePDL($tableName)
{
	$checkTable = false;
	$checkTable = check_table_existsPDL($tableName);
	if ($checkTable == true)
	{
		global $db_prefix, $smcFunc;
		$check = false;
		$checkval = false;
		$check = $smcFunc['db_query']('', "DESCRIBE {$db_prefix}$tableName");
		$checkval = $smcFunc['db_num_rows']($check);
		$smcFunc['db_free_result']($check);
		if ($checkval > 0)
			return $checkval;
	}

	return false;
}

/*  Check if table exists  */
function check_table_existsPDL($table)
{
	global $db_prefix, $smcFunc;
	$check = false;
	$checkval = false;
	$check = $smcFunc['db_query']('', "SHOW TABLES LIKE '{$db_prefix}$table'");
	$checkval = $smcFunc['db_num_rows']($check);
	$smcFunc['db_free_result']($check);
	if ($checkval >0)
		return true;

	return false;
}

/* Add extra needed tables/columns if they do not exist */
function addPDLcolumn1()
{
	global $smcFunc, $db_prefix;
	$table = 'arcade_pdl1';
	$z = check_table_existsPDL($table);
	if ($z == false)
	{
		$result = $smcFunc['db_query']('', "CREATE TABLE {$db_prefix}{$table}
			(`id_member` int(11) NOT NULL, `count` int(11) NOT NULL, `year` varchar(255) NOT NULL, `day` varchar(255) NOT NULL, `latest_year` varchar(255) NOT NULL, `latest_day` varchar(255) NOT NULL, `permission` int(11) NOT NULL, PRIMARY KEY (`id_member`))");
	}
	else
	{
		$columns = array('id_member', 'count', 'year', 'day', 'latest_year', 'latest_day', 'permission');
		$types = array('int(11) NOT NULL', 'int(11) NOT NULL', 'varchar(255) NOT NULL', 'varchar(255) NOT NULL' , 'varchar(255) NOT NULL', 'varchar(255) NOT NULL', 'int(11) NOT NULL');
		$table = 'arcade_pdl1';
		$count = 0;
		foreach ($columns as $column)
		{
			$a = checkFieldPDL($table,$column);
			$type = $types[$count];
			    if ($a == false)
				{
					$request = $smcFunc['db_query']('', "ALTER TABLE {$db_prefix}$table
						ADD $column $type");
				}
			$count++;

		}
	}
}

function addPDLcolumn2()
{
	global $smcFunc, $db_prefix, $boarddir;
	$table = 'arcade_pdl2';
	$z = check_table_existsPDL($table);
	if ($z == false)
	{
		$result = $smcFunc['db_query']('', "CREATE TABLE {$db_prefix}{$table}
			(`pdl_gameid` int(11) NOT NULL, `game_name` varchar(255) NOT NULL, `report_day` varchar(255) NOT NULL, `report_year` varchar(255) NOT NULL, `user_id` int(11) NOT NULL, `report_id` int(11) NOT NULL, `download_count` int(11) NOT NULL, `download_disable` int(11) NOT NULL, PRIMARY KEY (`pdl_gameid`))");
	}
	else
	{
		$columns = array('pdl_gameid', 'game_name', 'report_day', 'report_year', 'user_id', 'report_id', 'download_count', 'download_disable');
		$types = array('int(11) NOT NULL', 'varchar(255) NOT NULL', 'varchar(255) NOT NULL', 'varchar(255) NOT NULL' ,'int(11) NOT NULL', 'int(11) NOT NULL', 'int(11) NOT NULL', 'int(11) NOT NULL');
		$table = 'arcade_pdl2';
		$count = 0;
		foreach ($columns as $column)
		{
			$a = checkFieldPDL($table,$column);
			$type = $types[$count];
			    if ($a == false)
				{
                    $request = $smcFunc['db_query']('', "ALTER TABLE {$db_prefix}$table
                    ADD $column $type");
				}
			$count++;

		}
	}
}

function deleteFile()
{
	if (file_exists('games_download/index.php'))
		@unlink('games_download/index.php');

	return;
}
?>