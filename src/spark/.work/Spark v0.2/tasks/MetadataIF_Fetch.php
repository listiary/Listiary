<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//Output the contents of a single metadata entry
	//IF metadata stands for item Id to Files
	//it describes what files contain each public item Id
	function MetadataIF_Fetch($itemId, $configRelativePath = "/_configs/config.php") {
		
		$log = "";
		$data = [];
		try 
		{
			//check filename
			if (!verifyDescribeItemId($itemId))
			{
				$log .= "Invalid Item Id. Only letters, digits, '.', '-', '_' are allowed." . NEW_LINE;
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
			$idEscaped = mysqli_real_escape_string($link, $itemId);
			$sql = "SELECT `filename`, `item_id` FROM `housekeeping_itemid_filename` WHERE `item_id` = '$idEscaped';";
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$log .= "Error fetching item id: " . mysqli_error($link) . NEW_LINE;
				$log .= "SCRIPT FAILED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			//report
			$count = 0;
			$log .= NEW_LINE . "----------------------------" . NEW_LINE;
			$log .= "{$idEscaped} ->" . NEW_LINE . NEW_LINE;
			while ($row = mysqli_fetch_assoc($result)) 
			{
				$filename = $row['filename'];
				$data[] = $filename;
				if($count > 0) $log .= "(.ds)," . NEW_LINE;
				$log .= "    " . $filename;
				$count++;
			}
			$log .= "(.ds);" . NEW_LINE . "----------------------------" . NEW_LINE . NEW_LINE;
			
			//return
			$log .= "SCRIPT SUCCEEDED";
			mysqli_close($link);
			return ["success" => false, "log" => $log, "result" => $data];
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}