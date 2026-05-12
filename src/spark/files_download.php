<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	require_once __DIR__ . "/tasks/DownloadFiles.php";
	set_exception_handler('catchEx');
	
	
	
	//Usage
	$thisName = basename(__FILE__);
	$usage = "Usage: php $thisName [<config_name>] [<dest_path>]";
	
	
	
	// Grab the first argument from the command line: php z_ListFiles.php "wiki_one"
	// $wikiName = $argv[1] ?? 'default'; 
	// $configPath = "/_config_{$wikiName}.php";
	
	//print ASCII art baner
	printBanner();
	
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
	$output = DownloadFiles("/../_configs/config.php", -1);
	
	// Output the log narrative
	echo $output["log"];
	echo NEW_LINE . NEW_LINE;

	//write the files to disk
	$saveCount = 0;
	if (!empty($output["result"]) && is_array($output["result"]))
	{
		foreach ($output["result"] as $fileData) 
		{
			$fileName = $fileData['filename'];
			$content  = $fileData['content'];

			// Construct the full path
			// rtrim ensures we don't have double slashes
			$fullPath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName . ".ds";

			// Write the file
			if (file_put_contents($fullPath, $content) !== false) 
			{
				echo "Saved: {$fileName} (" . strlen($content) . " bytes)" . NEW_LINE;
				$saveCount++;
			} 
			else 
			{
				echo "FAILED to save: {$fileName}" . NEW_LINE;
			}
		}
		echo "Finished. Saved {$saveCount} files to disk." . NEW_LINE;
	}
	elseif ($output["success"]) 
	{
		echo "No files were found to download." . NEW_LINE;
	}

	// Exit with a proper status code
	if ($output["success"]) 
	{
		exit(0); // All good
	} 
	else 
	{
		exit(1); // Notify the system that the script failed
	}