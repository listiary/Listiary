<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/DeleteFiles.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName [<config_name>]";
	
	
	
	// Grab the first argument from the command line: php z_ListFiles.php "wiki_one"
	// $wikiName = $argv[1] ?? 'default'; 
	// $configPath = "/_config_{$wikiName}.php";
	
	//print ASCII art baner
	printBanner();

	// Confirm the dangerous operation we are about to perform
    echo "!!! WARNING !!!" . NEW_LINE;
    echo "This will permanently delete file '$fileName' from the database." . NEW_LINE;
    echo "Do you want to proceed (y/n)?" . NEW_LINE;
    $confirmation = trim(readline());
    if (strtolower($confirmation) !== "y")
	{
        echo "Aborted. Nothing was deleted." . NEW_LINE;
        exit(1);
    }
	
	// Execute the task
	$output = DeleteFiles("/../_configs/config.php");
	
	// Output the log narrative
	echo $output["log"];
	echo NEW_LINE;
	
	// If we need to see the "Result" data in the console:
	// if (!empty($output["result"])) 
	// {
		// echo "--- DATA OUTPUT ---" . NEW_LINE;
		// print_r($output["result"]);
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