<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/FetchFile.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName <filename> [<dest_path>]";
	
	
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//print ASCII art baner
	printBanner();
	
	// Grab the first argument from the command line: php download-file.php <file_name> [<dest_path>]
	$fileName = "";
	if (isset($argv[1]))
	{
		$fileName = $argv[1];
	} 
	else 
	{
		echo "Error: Please provide a filename." . NEW_LINE . $usage . NEW_LINE;
		exit(1);
	}
	
	//check filename
	if (!verifyDescribeFilename($fileName))
	{
		echo "Invalid fileName. Only letters, digits, '.', '-', '_' are allowed." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	
	// Grab the second argument from the command line or use default
	$destDir = __DIR__ . "\\_downloads\\";
	if (isset($argv[2]))
	{
		$destDir = $argv[2];
	}
	
	//check destination path
	if (!is_dir($destDir))
	{
		echo "The destination path '{$destDir}' does not exist or is not a directory." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	if (!is_writable($destDir)) 
	{
		echo "The destination path '{$destDir}' is not writable. Check your file permissions." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}	
	
	// Execute the task
	$output = FetchFile($fileName, "/../_configs/config.php");
	
	// Output the log narrative
	echo $output["log"];
	echo NEW_LINE;

	// write the file to disk
	$filename = $output["result"]["filename"];
	$content = $output["result"]["content"];
	$fullPath = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename . ".ds";
	$bytesWritten = file_put_contents($fullPath, $content);
	if ($bytesWritten !== false) 
	{
		echo NEW_LINE;
		echo "Saved {$bytesWritten} bytes to '{$fullPath}'." . NEW_LINE;
		echo NEW_LINE;
		exit(0);
	}
	else
	{
		echo NEW_LINE;
		echo "Failed to write data to '{$fullPath}'." . NEW_LINE;
		echo NEW_LINE;
		exit(1);
	}