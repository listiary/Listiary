<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_commonlib.php";
	set_exception_handler('catchEx');
	
	
	//Output the contents of a single file
	function ViewFile($fileName, $configRelativePath) {
		
		$output = "";
		try 
		{
			//check filename
			if (!preg_match('/^[a-zA-Z0-9._-]+$/', $fileName))
			{
				$output .= "Invalid fileName. Only letters, digits, '.', '-', '_' are allowed.<br>";
				$output .= "SCRIPT FAILED";
				return $output;
			}
	
			// Include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
			
			//check connection
			$link = connectDb();
			$output .= "Connection OK<br>";
			
			//get file contents
			$nameEscaped = mysqli_real_escape_string($link, $fileName);
			$sql = "SELECT `filename`, `content` FROM `describe_documents` WHERE `filename` = '$nameEscaped';";
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$output .= "Error fetching file: " . mysqli_error($link) . "<br>";
				$output .= "SCRIPT FAILED";
				return $output;
			}
			
			//report
			if ($row = mysqli_fetch_assoc($result))
			{
				//fetch
				$filename = $row['filename'];
				$content  = $row['content'];
				
				//output
				$output .=  "File '$filename' :<br>";
				$output .= "--------------------------------------------<br>";
				$output .= "<pre style='background-color: #e8f5e9; color: #000;'>" . htmlspecialchars($content) . "</pre>";
				$output .= "--------------------------------------------<br>";

				//return
				$output .= "SCRIPT SUCCEEDED";
				mysqli_close($link);
				return $output;
			}
			else
			{
				$output .= "File '$nameEscaped' not found in database.<br>";
				$output .= "SCRIPT FAILED";
				mysqli_close($link);
				return $output;
			}
		}
		catch (Throwable $ex) 
		{
			$output .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . "<br>";
			$output .= "SCRIPT FAILED";
			return $output;
		}
	}