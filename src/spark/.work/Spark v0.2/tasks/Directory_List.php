<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	
	//List the files in the directory
	function Directory_List($relDir = ""): array {
		
		$log = "";
		$data = [];
		try 
		{
			// Normalize empty string to current directory
			if ($relDir === "")
			{
				$relDir = ".";
			}

			// Check if valid directory
			if (!is_dir($relDir))
			{
				$output .= "Error: '$relDir' is not a directory." . NEW_LINE;
				$output .= "SCRIPT FAILED";
				return $output;
			}

			$log .= "Getting a directory listing for '$relDir'" . NEW_LINE;
			$log .= "--------------------------------------------" . NEW_LINE;
			$fileList = _listFilesRecursive($relDir);
			foreach ($fileList as $filePath) 
			{
				$log .= $filePath . NEW_LINE;
				$data[] = $filePath;
			}
			$log .= "--------------------------------------------" . NEW_LINE;
			$log .= "SCRIPT SUCCEEDED";
			return ["success" => true, "log" => $log, "result" => $data];
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}
	function _listFilesRecursive($baseDir): array {

		$files = [];

		// Normalize path to remove trailing slashes for clean substring calculation
		$baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);
		
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $file) 
		{
			if ($file->isFile()) 
			{
				// Get relative path by subtracting base directory length (+1 for the slash)
				$relativePath = substr($file->getPathname(), strlen($baseDir) + 1);
				$files[] = $relativePath;
			}
		}

		//sort the files
		natcasesort($files);
		return array_values($files);
	}