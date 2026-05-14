<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/PutFileNoCloseSqli.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName [<config_name>] [<source_path>]";
	

	
	//print ASCII art baner
	printBanner();
	
	// Grab the second argument from the command line or use default
	$sourceDir = __DIR__ . "\\_downloads\\";
	if (isset($argv[2]))
	{
		$sourceDir = $argv[2];
	}
	
	//check source path
	if (!is_dir($sourceDir))
	{
		echo "The source path '{$sourceDir}' does not exist or is not a directory." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	if (!is_readable($sourceDir)) 
	{
		echo "The source path '{$sourceDir}' is not readable. Check your file permissions." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	echo "Starting files upload from dir: '{$sourceDir}'." . NEW_LINE; 

	// Find all files with the .ds extension in the directory
	$files = glob($sourceDir . "*.ds");
	if (empty($files)) 
	{
		echo "No .ds files found in '{$sourceDir}'." . NEW_LINE;
		exit(0); // Exit cleanly as there is nothing to do
	}
	echo "Found " . count($files) . " .ds file(s). Starting validation and upload..." . NEW_LINE;

	// Execute task
	$min = 3;
	$max = 500000;
	$succeeded = $failed = $skipped = 0;
	foreach ($files as $filePath) 
	{
		$fileName = basename($filePath);
		echo "Processing: {$fileName}... ";
		$dbFileName = pathinfo($filePath, PATHINFO_FILENAME);
		
		$content = file_get_contents($filePath);
		if ($content === false) 
		{
			echo "[Skipped] N permission to read." . NEW_LINE;
			$skipped++;
			continue;
		}
		$size = strlen($content); // size in bytes
		if ($size < $min || $size > $max) 
		{
			echo "[Skipped] File size ($size bytes) is outside the allowed range ($min - $max)." . NEW_LINE;
			$skipped++;
			continue;
		}
		if (!mb_check_encoding($content, 'UTF-8'))
		{
			echo "[Skipped] File is not valid UTF-8 text." . NEW_LINE;
			$skipped++;
			continue;
		}
		try 
		{
			$output = PutFileNoCloseSqli($dbFileName, $content, "/../_configs/config.php");
			if($output["success"] == true) 
			{
				echo "[Success] File uploaded." . NEW_LINE;
				$succeeded++;
			}
			else 
			{
				echo "[Error] File upload failed." . NEW_LINE;
				echo "---------------------------" . NEW_LINE;
				echo $output["log"] . NEW_LINE;
				echo "---------------------------" . NEW_LINE;
				$failed++;
			}
		} 
		catch (Exception $e) 
		{
			echo "[Error] Failed to upload: " . $e->getMessage() . NEW_LINE;
			$failed++;
		}
	}
	echo "Finished. Uploaded {$succeeded}, Failed {$failed}, Skipped {$skipped}." . NEW_LINE;
	exit(0); // All good