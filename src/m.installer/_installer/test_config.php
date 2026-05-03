<?php

	// Presets
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_commonlib.php";
	set_exception_handler('catchEx');


	try 
	{
		//include the config
		if (!@include_once __DIR__ . "/../_configs/_config.php") 
		{
			throw new Exception("Missing main config file - '/../_configs/_config.php' ");
		}
		
		// More Presets
		//startSecureSession();
	
		//config info
		$sqlServerName = DB_SERVER_PUBLIC;
		$dbName = DB_NAME_PUBLIC;
		echo "Selected Server: " . $sqlServerName . "<br>";
		echo "Selected Database: " . $dbName . "<br>";
		
		//check connection
		$link = connectDb();
		echo "Connection OK<br>";
		
		//read test
		$link->query("SELECT 1");
		echo "Read access OK<br>";
		
		//write test
		$link->query("CREATE TEMPORARY TABLE __perm_test (id INT)");
		$link->query("DROP TEMPORARY TABLE __perm_test");
		echo "Write access OK<br>";
		
		//db empty test
		$isEmpty = false;
		$result = $link->query("SHOW TABLES");
		if ($result->num_rows === 0) 
		{
			echo "Database is EMPTY - OK<br>";
			$isEmpty = true;
		} 
		else 
		{
			echo "Database has " . $result->num_rows . " tables.<br>";
			echo "Fail - you need an empty database to proceed<br>";
			$isEmpty = false;
		}
		
		if($isEmpty) echo "SCRIPT SUCCEEDED";
		else echo "SCRIPT FAILED";
	}
	catch (Throwable $ex) 
	{
		// Optional: log (your handler also logs, so this is redundant but safe)
		//error_log($ex->getMessage());

		echo "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . "<br>";
		echo "SCRIPT FAILED";
	}