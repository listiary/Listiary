<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//Upload a single file to the DB
	function PutJsonNoCloseSqli($fileName, $fileContents, $configRelativePath = "/_configs/config.php") {
		
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
			
			// 2. Check fileContents (Is it a string? Is it the right size?)
			$minLength = 3;
			$maxLength = 500000; // ~500KB or approx 150-200 pages of code
			$isInvalid = false;
			if (!is_string($fileContents))
			{
				$log .= "Invalid content format: Expected a string." . NEW_LINE;
				$isInvalid = true;
			} 
			elseif (strlen($fileContents) < $minLength) 
			{
				$log .= "File content is too short (Minimum {$minLength} characters)." . NEW_LINE;
				$isInvalid = true;
			} 
			elseif (strlen($fileContents) > $maxLength) 
			{
				$log .= "File content is too large (Maximum {$maxLength} characters)." . NEW_LINE;
				$isInvalid = true;
			}
			if ($isInvalid) 
			{
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
			
			//put file contents
			$nameEscaped = mysqli_real_escape_string($link, $fileName);
			$contentEscaped = mysqli_real_escape_string($link, $fileContents);
			$sql = "INSERT INTO `compiled_documents` (`filename`, `content`) VALUES ('$nameEscaped', '$contentEscaped')";

			//check result
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				if (mysqli_errno($link) == 1062)
				{
					$log .= "Error: A file named '{$fileName}' already exists in the database." . NEW_LINE;
				}
				else
				{
					$log .= "Error uploading file: " . mysqli_error($link) . NEW_LINE;
				}
				$log .= "SCRIPT FAILED";
				//mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			else
			{
				//return
				$log .= "SCRIPT SUCCEEDED";
				//mysqli_close($link);
				return ["success" => true, "log" => $log, "result" => true];
			}
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}