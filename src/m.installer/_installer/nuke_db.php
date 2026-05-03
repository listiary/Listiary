<?php

	// Presets
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_commonlib.php";
	set_exception_handler('catchEx');

	try 
	{
		// Include the config
		if (!@include_once __DIR__ . "/../_configs/_config.php") 
		{
			throw new Exception("Missing main config file - '/../_configs/_config.php' ");
		}
		
		//check connection
		$link = connectDb();
		echo "Connection OK<br>";
		
		echo "Initiating database nuke ...<br>";
				
		// Disable foreign key checks so tables drop without relationship errors
		$link->query("SET FOREIGN_KEY_CHECKS = 0");
				
		// Drop all tables
		$result = $link->query("SHOW TABLES");
		if ($result)
		{
			while ($row = $result->fetch_array()) 
			{
				$tableName = $row[0];
				$link->query("DROP TABLE IF EXISTS `$tableName`");
				echo "DROP TABLE IF EXISTS executed on " . $tableName . "<br>";
			}
		}

		// Drop all views (in case your SQLs create any)
		$result = $link->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
		if ($result)
		{
			while ($row = $result->fetch_array()) 
			{
				$viewName = $row[0];
				$link->query("DROP VIEW IF EXISTS `$viewName`");
				echo "DROP VIEW IF EXISTS executed on " . $viewName . "<br>";
			}
		}

		// Re-enable foreign key checks
		$link->query("SET FOREIGN_KEY_CHECKS = 1");
		echo "Nuking complete - Database completely wiped.<br>";

		// Return
		echo "SCRIPT SUCCEEDED";
	}
	catch (Throwable $ex) 
	{
		// Optional: log (your handler also logs, so this is redundant but safe)
		// error_log($ex->getMessage());
		echo "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . "<br>";

		echo "Cleanup failed - Database might be in a partial state.<br>";
		echo "SCRIPT FAILED";
		exit;
	}