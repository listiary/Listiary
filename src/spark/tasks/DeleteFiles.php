<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	
	//List the files in the database
	function DeleteFiles($configRelativePath = "/../_configs/config.php"): array {
		
		$log = "";
		$data = [];
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
			
			// Delete files in database
			$sql = "DELETE FROM `describe_documents`";
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$log .= "Error deleting files: " . mysqli_error($link) . NEW_LINE;
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			// Get the number of rows that were successfully deleted
			$deleted_count = mysqli_affected_rows($link);
			$log .= "Successfully deleted $deleted_count files." . NEW_LINE;

			// return
			$log .= "SCRIPT SUCCEEDED";
			mysqli_close($link);
			return ["success" => true, "log" => $log, "result" => $deleted_count];
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}