<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	
	//Find how many times each file is referenced
	function Database_Find_Orphans($configRelativePath = "/_configs/config.php"): array {
		
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
			$sql = "SELECT `filename`, `related_filename` FROM `housekeeping_filename_related`;";
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$log .= "Error fetching filenames: " . mysqli_error($link);
				return ["success" => false, "log" => $log, "result" => null];
			}

			//loop through files
			$fileCounts = [];
            while ($row = mysqli_fetch_assoc($result)) 
            {
                $relatedFile = $row['related_filename'];
                if (isset($fileCounts[$relatedFile])) $fileCounts[$relatedFile]++;
				else $fileCounts[$relatedFile] = 1;
            }

			
			 // Show results
			foreach ($fileCounts as $file => $count) 
            {
                $log .= "'{$file}' - {$count}" . NEW_LINE;
            }
			
			// return
			$log .= "SCRIPT SUCCEEDED";
			mysqli_close($link);
			return ["success" => true, "log" => $log, "result" => $fileCounts];
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}