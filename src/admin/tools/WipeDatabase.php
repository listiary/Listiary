<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_commonlib.php";
	set_exception_handler('catchEx');

	
	//Nuke a database, deleting everything from it.
	function WipeDatabase($configRelativePath) {
		
		$output = "";
		try 
		{
			// Include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
			
			//check connection
			$link = connectDb();
			$output .= "Connection OK<br>";
			
			$output .=  "Initiating database nuke ...<br>";
					
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
					$output .=  "DROP TABLE IF EXISTS executed on " . $tableName . "<br>";
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
					$output .=  "DROP VIEW IF EXISTS executed on " . $viewName . "<br>";
				}
			}

			// Re-enable foreign key checks
			$link->query("SET FOREIGN_KEY_CHECKS = 1");
			$output .=  "Nuking complete - Database completely wiped.<br>";

			// Return
			$output .=  "SCRIPT SUCCEEDED";
		}
		catch (Throwable $ex) 
		{
			// Optional: log (your handler also logs, so this is redundant but safe)
			// error_log($ex->getMessage());
			$output .=  "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . "<br>";

			$output .=  "Cleanup failed - Database might be in a partial state.<br>";
			$output .=  "SCRIPT FAILED";
		}
		return $output;
	}