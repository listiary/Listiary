<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_commonlib.php";
	set_exception_handler('catchEx');



	// Test if our main config is properly configured and we have an empty DB
	function TestMainConfig($configRelativePath): string {
		
		$output = "";
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
			$output .= "Selected Server: " . $sqlServerName . "<br>";
			$output .= "Selected Database: " . $dbName . "<br>";
			
			//check connection
			$link = connectDb();
			$output .= "Connection OK<br>";
			
			//read test
			$link->query("SELECT 1");
			$output .= "Read access OK<br>";
			
			//write test
			$link->query("CREATE TEMPORARY TABLE __perm_test (id INT)");
			$link->query("DROP TEMPORARY TABLE __perm_test");
			$output .= "Write access OK<br>";
			
			//db empty test
			$isEmpty = false;
			$result = $link->query("SHOW TABLES");
			if ($result->num_rows === 0) 
			{
				$output .= "Database is EMPTY - OK<br>";
				$isEmpty = true;
			} 
			else 
			{
				$output .= "Database has " . $result->num_rows . " tables.<br>";
				$output .= "Fail - you need an empty database to proceed<br>";
				$isEmpty = false;
			}
			
			if($isEmpty) $output .= "SCRIPT SUCCEEDED";
			else $output .= "SCRIPT FAILED";
			return $output;
		}
		catch (Throwable $ex) 
		{
			// Optional: log (your handler also logs, so this is redundant but safe)
			//error_log($ex->getMessage());

			$output .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . "<br>";
			$output .= "SCRIPT FAILED";
			return $output;
		}
	}