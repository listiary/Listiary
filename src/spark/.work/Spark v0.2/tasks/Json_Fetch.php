<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//Output the contents of a single file
	function Json_Fetch($fileName, $configRelativePath = "/_configs/config.php") {
		
		$log = "";
		$data = [];
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
			$sql = "SELECT `filename`, `content` FROM `compiled_documents` WHERE `filename` = '$nameEscaped';";
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$log .= "Error fetching file: " . mysqli_error($link) . NEW_LINE;
				$log .= "SCRIPT FAILED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			//report
			if ($row = mysqli_fetch_assoc($result))
			{
				//fetch
				$filename = $row['filename'];
				$content  = $row['content'];
				
				//output
				$data["filename"] = $filename;
				$data["content"] = $content;

				//return
				$log .= "SCRIPT SUCCEEDED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => $data];
			}
			else
			{
				$log .= "File '$nameEscaped' not found in database." . NEW_LINE;
				$log .= "SCRIPT FAILED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}