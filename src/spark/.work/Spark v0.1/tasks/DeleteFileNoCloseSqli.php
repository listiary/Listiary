<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	//Output the contents of a single file
	function DeleteFileNoCloseSqli($fileName, $configRelativePath = "/_configs/config.php") {
		
		$log = "";
		try 
		{
			//check filename
			if (!verifyDescribeFilename($fileName))
			{
				$log .= "Invalid fileName. Only letters, digits, '.', '-', '_' are allowed." . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}
	
			// Include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
			
			//check connection
			$link = connectDb();
			$log .= "Connection OK" . NEW_LINE;
			
			//get file contents
			$nameEscaped = mysqli_real_escape_string($link, $fileName);
			$sql = "DELETE FROM `describe_documents` WHERE `filename` = '$nameEscaped';";
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$log .= "Error deleting file: " . mysqli_error($link) . NEW_LINE;
				$log .= "SCRIPT FAILED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			//report
			$deletedRows = mysqli_affected_rows($link);
			if ($deletedRows === 0)
			{
				$log .= "No file named '$nameEscaped' was found in the database." . NEW_LINE;
				$log .= "SCRIPT FAILED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			else
			{
				$log .= "Ok - deleted $deletedRows rows.";
				$log .= "SCRIPT SUCCEEDED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => $deletedRows];
			}
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}