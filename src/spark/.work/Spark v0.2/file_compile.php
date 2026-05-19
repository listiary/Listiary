<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/File_Compile.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName <filename>";
	
	
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//print ASCII art baner
	printBanner();
	
	// Grab the first argument from the command line: php view-file.php "file_name"
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
	
	// Execute the task
	$output = File_Compile($fileName, "/../_configs/config.php");
	
	// Output the log narrative
	echo $output["log"];
	echo NEW_LINE;
	
	// If we need to see the "Result" data in the console:
	// if (!empty($output["result"])) 
	// {
	// 	echo NEW_LINE . "--- DATA OUTPUT - 'filename' ---" . NEW_LINE;
	// 	print_r($output["result"]["filename"]);
	// 	echo NEW_LINE . NEW_LINE;
	// 	echo "--- DATA OUTPUT - 'content' ---" . NEW_LINE;
	// 	print_r($output["result"]["content"]); 
	// 	echo NEW_LINE . NEW_LINE;
	// }

	// Exit with a proper status code
	if ($output["success"]) 
	{
		exit(0); // All good
	} 
	else 
	{
		exit(1); // Notify the system that the script failed
	}