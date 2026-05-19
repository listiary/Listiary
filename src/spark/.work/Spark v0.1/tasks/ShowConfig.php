<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');



	// Test if our main config is properly configured and we have an empty DB
	function ShowConfig($configRelativePath = "/utils/config.php"): array {
		
		$log = "";
		try 
		{
			//include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
		
			//config info
			$log .= "Selected Server: " . DB_SERVER_PUBLIC . NEW_LINE;
			$log .= "Selected Database: " . DB_NAME_PUBLIC . NEW_LINE;
			$log .= "Selected User: " . DB_USERNAME_PUBLIC . NEW_LINE;
			$log .= "Password: ***********" . NEW_LINE;
			

			$log .= "SCRIPT SUCCEEDED";
			return ["success" => true, "log" => $log, "result" => true];
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