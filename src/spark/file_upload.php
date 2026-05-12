<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/PutFile.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName <database_filename> <local_file_path>";
	
	
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//print ASCII art baner
	printBanner();
	
	// Grab the first argument from the command line
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
	
	// Grab the second argument from the command line
	$filePath = "";
	if (isset($argv[2]))
	{
		$filePath = $argv[2];
	}
	if (empty($filePath)) 
	{
		echo "No or Empty filePath." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	if (!file_exists($filePath)) 
	{
		$altPath = __DIR__ . DIRECTORY_SEPARATOR . ltrim($filePath, '/\\');
		if (!file_exists($altPath))
		{
			echo "File does not exist at '{$filePath}'." . NEW_LINE;
			echo $usage . NEW_LINE;
			exit(1);
		}
		else
		{
			echo "Relative path resolved to '{$altPath}'." . NEW_LINE;
			$filePath = $altPath;
		}
	}
	$filePath = realpath($filePath);
	echo "Path resolved to '{$filePath}'." . NEW_LINE;
	if (!is_file($filePath)) 
	{
		echo "This is not a valid file '{$filePath}'." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	if (!is_readable($filePath)) 
	{
		echo "You do not have permission to read '{$filePath}'." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}

	//read file
	$content = file_get_contents($filePath);
	if ($content === false) 
	{
		echo "You do not have permission to read the file contents." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	
	//validate Length and "Text-ness"
	$size = strlen($content); // size in bytes
	$min = 3;
	$max = 500000;
	if ($size < $min || $size > $max) 
	{
		echo "File size ($size bytes) is outside the allowed range ($min - $max)." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	// Check if it's actually valid UTF-8 text (prevents binary uploads)
	if (!mb_check_encoding($content, 'UTF-8'))
	{
		echo "File is not valid UTF-8 text." . NEW_LINE;
		echo $usage . NEW_LINE;
		exit(1);
	}
	//should we trust this method if extension=mbstring is not enabled in php,
	//or should we require the extension?
	// if (function_exists('mb_check_encoding')) {
		// if (!mb_check_encoding($content, 'UTF-8')) {
			// echo "File is not valid UTF-8 text (it may be a binary file)." . NEW_LINE;
			// echo $usage . NEW_LINE;
			// exit(1);
		// }
	// }
	// else 
	// {
		// // Fallback for systems without mbstring: check for null bytes which usually indicate binary
		// if (strpos($content, "\0") !== false) {
			// echo "Warning: Null bytes detected. This appears to be a binary file." . NEW_LINE;
			// exit(1);
		// }
	// }



	// Execute the task
	$output = PutFile($fileName, $content, "/../_configs/config.php");
	
	// Output the log narrative
	echo $output["log"];
	echo NEW_LINE;

	// return
	if ($output["result"] == true) 
	{
		exit(0);
	}
	else
	{
		exit(1);
	}