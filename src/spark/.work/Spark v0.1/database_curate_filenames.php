<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/Database_Curate_Filenames.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName <filename>";
	
	
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//print ASCII art baner
	printBanner();
	
	// Execute the task
	$output = Database_Curate_Filenames("/../_configs/config.php");
	
	// Output the log narrative
	echo $output["log"];
	echo NEW_LINE;
	
	// Show result data?

	// Exit with a proper status code
	if ($output["success"]) 
	{
		exit(0); // All good
	} 
	else 
	{
		exit(1); // Notify the system that the script failed
	}