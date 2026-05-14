<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');

	
	//Nuke a database, deleting everything from it.
	function Database_Wipe($configRelativePath = "/_configs/config.php"): array {
		
		$log = "";
		try 
		{
			// Include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
			
			//check connection
			$link = connectDb();
			$log .= "Connection OK" . NEW_LINE;
			$log .=  "Initiating database nuke ..." . NEW_LINE;

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
					$log .=  "DROP TABLE IF EXISTS executed on " . $tableName . NEW_LINE;
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
					$log .=  "DROP VIEW IF EXISTS executed on " . $viewName  . NEW_LINE;
				}
			}

			// Re-enable foreign key checks
			$link->query("SET FOREIGN_KEY_CHECKS = 1");
			$log .=  "Nuking complete - Database completely wiped." . NEW_LINE;

			// Return
			mysqli_close($link);
			$log .=  "SCRIPT SUCCEEDED";
			return ["success" => true, "log" => $log, "result" => true];
		}
		catch (Throwable $ex) 
		{
			// Optional: log (your handler also logs, so this is redundant but safe)
			// error_log($ex->getMessage());
			$log .=  "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .=  "Cleanup failed - Database might be in a partial state." . NEW_LINE;
			$log .=  "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}