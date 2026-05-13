<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	
	//List the files in the database
	//Set maxfiles to > 0 if you need to limit the number of files fetched
	function Jsons_Download($configRelativePath = "/_configs/config.php", $maxFiles = -1): array {
		
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
			
			// Fetch files from database
			$sql = "SELECT `filename`, `content` FROM `compiled_documents`";
			if ($maxFiles > 0)
			{
				$sql .= " LIMIT $maxFiles";
			}
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$log .= "Error fetching files: " . mysqli_error($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			//get results
			$count = 0;
			while ($row = mysqli_fetch_assoc($result)) 
			{
				$filename = $row['filename'];
				$content = $row['content'];
				$data[] = ["filename" => $filename, "content"  => $content ];
				$count++;
			}
			$log .=  "Ok - fetched {$count} files." . NEW_LINE;
	
			// return
			$log .= "SCRIPT SUCCEEDED";
			mysqli_close($link);
			return ["success" => true, "log" => $log, "result" => $data];
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}