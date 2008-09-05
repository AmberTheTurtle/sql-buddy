<?php
/*

SQL Buddy - Web based MySQL administration
http://www.sqlbuddy.com/

ajaxsaveedit.php
- saves data to the database

MIT license

2008 Calvin Lough <http://calv.in>

*/

include "functions.php";

loginCheck();

if (isset($db))
	$conn->selectDB($db);

if ($_POST && isset($table))
{
	
	$insertChoice = "";
	
	if (isset($_POST['SB_INSERT_CHOICE']))
	{
		$insertChoice = $_POST['SB_INSERT_CHOICE'];
	}
	
	$structureSql = $conn->describeTable($table);
	
	if ($conn->getAdapter() == "mysql")
	{
		while ($structureRow = $conn->fetchAssoc($structureSql))
		{
			$pairs[$structureRow['Field']] = '';
		}
	}
	else if ($conn->getAdapter() == "sqlite")
	{
		foreach ($structureRow as $column)
		{
			$pairs[$column[0]] = '';
		}
	}
	
	foreach ($_POST as $key=>$value)
	{
		if ($key != "SB_INSERT_CHOICE")
		{	
			if (is_array($value))
			{
				$value = implode(",", $value);
			}
			
			$pairs[$key] = $conn->escapeString($value);
		}
	}
	
	if (isset($pairs))
	{
		
		if ($insertChoice != "INSERT")
		{
			$updates = "";
			
			foreach ($pairs as $keyname=>$value)
			{
				if ($conn->getAdapter() == "mysql")
				{
					$updates .= "`" . $keyname . "`='" . $value . "',";
				}
				else if ($conn->getAdapter() == "sqlite")
				{
					$updates .= "'" . $keyname . "'='" . $value . "',";
				}
			}
			
			$updates = substr($updates, 0, -1);
			
			if (isset($_GET['queryPart']))
				$queryPart = $_GET['queryPart'];
			else
				$queryPart = "";
			
			if ($conn->getAdapter() == "mysql")
			{
				$query = "UPDATE `$table` SET " . $updates . " " . $queryPart;
			}
			else if ($conn->getAdapter() == "sqlite")
			{
				$query = "UPDATE '$table' SET " . $updates . " " . $queryPart;
			}
			
		}
		else
		{
			$columns = "";
			$values = "";
			
			foreach ($pairs as $keyname=>$value)
			{
				
				if ($conn->getAdapter() == "mysql")
				{
					$columns .= "`" . $keyname . "`,";
				}
				else if ($conn->getAdapter() == "sqlite")
				{
					$columns .= "'" . $keyname . "',";
				}
				
				$values .= "'" . $value . "',";
			}
			
			$columns = substr($columns, 0, -1);
			$values = substr($values, 0, -1);
			
			if ($conn->getAdapter() == "mysql")
			{
				$query = "INSERT INTO `$table` ($columns) VALUES ($values)";
			}
			else if ($conn->getAdapter() == "sqlite")
			{
				$query = "INSERT INTO '$table' ($columns) VALUES ($values)";
			}
		}
		
		$conn->query($query) or ($mysqlError = $conn->error());
		
		echo "{\n";
		echo "    \"formupdate\": \"" . $_GET['form'] . "\",\n";
		echo "    \"errormess\": \"";
		if (isset($mysqlError))
			echo $mysqlError;
		echo "\"\n";
		echo '}';
		
	}
}

?>