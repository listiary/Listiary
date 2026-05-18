<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/Action_DeleteArticle.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName <filename>";
	
	
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//print ASCII art baner
	printBanner();
	
	// Grab the first argument from the command line: php delete-file.php "file_name"
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
	
	// Confirm the dangerous operation we are about to perform
    echo "!!! WARNING !!!" . NEW_LINE;
    echo "This will permanently delete article '$fileName' from the database." . NEW_LINE;
    echo "Do you want to proceed (y/n)?" . NEW_LINE;
    $confirmation = trim(readline());
    if (strtolower($confirmation) !== "y")
	{
        echo "Aborted. Nothing was deleted." . NEW_LINE;
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
	$output = Action_DeleteArticle($fileName, "/../_configs/config.php");
	
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