<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/Config_Make.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName [<config_name>]";
	

	
	//print ASCII art baner
	printBanner();
	
	// Execute the task
	$output = Config_Make("_config.php", true);
	
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