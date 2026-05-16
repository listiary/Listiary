<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/Action_CreateStub.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName <item_text> <namespace>";
	

	
	//print ASCII art baner
	printBanner();
	
	// Grab the first argument from the command line
	$itemText = "";
	if (isset($argv[1]))
	{
		$itemText = $argv[1];
	} 
	else 
	{
		echo "Error: Please provide an item text." . NEW_LINE . $usage . NEW_LINE;
		exit(1);
	}
	
	// Grab the second argument from the command line
	$namespace = "";
	if (isset($argv[2]))
	{
		$namespace = $argv[2];
	} 
	else 
	{
		echo "Error: Please provide a namespace." . NEW_LINE . $usage . NEW_LINE;
		exit(1);
	}
	
	//check namespace
	if (!verifyDescribeNamespace($namespace))
	{
		echo "Invalid Namespace. Only letters, digits, '.', '-', '_' are allowed." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	
	// Execute the task
	$output = Action_CreateStub($itemText, $namespace, "/../_configs/config.php");
	
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