<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/MetadataIF_Fetch.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName <item_id>";
	
	
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//print ASCII art baner
	printBanner();
	
	// Grab the first argument from the command line: php view-file.php "file_name"
	$itemId = "";
	if (isset($argv[1]))
	{
		$itemId = $argv[1];
	}
	else 
	{
		echo "Error: Please provide an item id." . NEW_LINE . $usage . NEW_LINE;
		exit(1);
	}
	
	//check item id
	if (!verifyDescribeItemId($itemId))
	{
		echo "Invalid item id. Only letters, digits, '.', '-', '_' are allowed." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	
	// Execute the task
	$output = MetadataIF_Fetch($itemId, "/../_configs/config.php");
	
	// Output the log narrative
	echo $output["log"];
	echo NEW_LINE;

	// Exit with a proper status code
	if ($output["success"]) 
	{
		exit(0); // All good
	} 
	else 
	{
		exit(1); // Notify the system that the script failed
	}