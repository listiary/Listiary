<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	
	//List the files in the database
	function Jsons_Count($configRelativePath = "/../_configs/config.php"): array {
		
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
			
			// Count files in database
			$sql = "SELECT COUNT(*) AS total_files FROM `compiled_documents`";
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$log .= "Error fetching file count: " . mysqli_error($link) . NEW_LINE;
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			// Fetch the single row containing our count
			$row = mysqli_fetch_assoc($result);
			$count = (int)$row['total_files'];
			$log .= "Total files counted: " . $count . NEW_LINE;

			// return
			$log .= "SCRIPT SUCCEEDED";
			mysqli_close($link);
			return ["success" => true, "log" => $log, "result" => $count];
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}