<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');



	// Test if our main config is properly configured and we have an empty DB
	function TestDatabaseConfig($configRelativePath = "/utils/config.php"): array {
		
		$log = "";
		$data = [];
		try 
		{
			//include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
		
			//config info
			$sqlServerName = DB_SERVER_PUBLIC;
			$dbName = DB_NAME_PUBLIC;
			$log .= "Selected Server: " . $sqlServerName . NEW_LINE;
			$log .= "Selected Database: " . $dbName . NEW_LINE;
			
			//check connection
			$link = connectDb();
			$log .= "Connection OK" . NEW_LINE;
			
			//read test
			$link->query("SELECT 1");
			$log .= "Read access OK" . NEW_LINE;
			
			//write test
			$link->query("CREATE TEMPORARY TABLE __perm_test (id INT)");
			$link->query("DROP TEMPORARY TABLE __perm_test");
			$log .= "Write access OK" . NEW_LINE;
			
			//db empty test
			$isEmpty = false;
			$result = $link->query("SHOW TABLES");
			if ($result->num_rows === 0) 
			{
				$log .= "Database is EMPTY - OK" . NEW_LINE;
				$isEmpty = true;
			} 
			else 
			{
				$log .= "Database has " . $result->num_rows . " tables." . NEW_LINE;
				$log .= "Fail - you need an empty database to proceed" . NEW_LINE;
				$isEmpty = false;
			}
			
			mysqli_close($link);
			if($isEmpty) 
			{
				$log .= "SCRIPT SUCCEEDED";
				return ["success" => true, "log" => $log, "result" => true];
			}
			else 
			{
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}
		}
		catch (Throwable $ex) 
		{
			// Optional: log (your handler also logs, so this is redundant but safe)
			//error_log($ex->getMessage());

			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")"  . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}