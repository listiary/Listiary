<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_commonlib.php";
	set_exception_handler('catchEx');
	
	
	//List the files in the database
	//Set maxfiles to > 0 if you need to limit the number of files fetched
	function ListFiles($configRelativePath, $maxFiles = -1): string {
		
		$output = "";
		try 
		{
			// Include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
			
			//check connection
			$link = connectDb();
			$output .= "Connection OK<br>";
			
			// Fetch files from database
			$sql = "SELECT `filename` FROM `describe_documents`";
			if ($maxFiles > 0)
			{
				$sql .= " LIMIT $maxFiles";
			}
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$output .= "Error fetching filenames: " . mysqli_error($link);
				return $output;
			}
			
			 // Show results
			$output .=  "File listing:<br>";
			$output .= "--------------------------------------------<br>";
			while ($row = mysqli_fetch_assoc($result)) 
			{
				$filename = $row['filename'];
				$output .= "$filename<br>";
			}
			$output .= "--------------------------------------------<br>";
			
			// return
			$output .= "SCRIPT SUCCEEDED";
			mysqli_close($link);
			return $output;
		}
		catch (Throwable $ex) 
		{
			$output .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . "<br>";
			$output .= "SCRIPT FAILED";
			return $output;
		}
	}