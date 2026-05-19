<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	
	//Find how many times each file is referenced
	function Database_Find_Orphans_Advanced($configRelativePath = "/_configs/config.php"): array {
		
		$log = "";
		$data = [];
		try 
		{
			// Include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
			
			//check connection
			$link = connectDb();
			$log .= "Connection OK" . NEW_LINE;

			// Fetch files from database
			$sql = "SELECT `filename`, `related_filename` FROM `housekeeping_filename_related`;";
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				$log .= "Error fetching filenames: " . mysqli_error($link);
				return ["success" => false, "log" => $log, "result" => null];
			}

			//1. Build a DIRECTED graph (A points to B, but not vice versa automatically)
            $graph = [];
            $allFiles = [];

            // Track all files we know about in the DB so we have a master list
			$startingFile = "";
            while ($row = mysqli_fetch_assoc($result)) 
            {
                $file = $row['filename'];
                $related = $row['related_filename'];
				if($startingFile == "") $startingFile = $file;

                // Directed connection: $file references $related
                $graph[$file][] = $related;

                // Add both to our master list of unique files
                $allFiles[$file] = true;
                $allFiles[$related] = true;
            }

            // 2. Traverse the chain starting ONLY from the first file
            $visited = [];
            $queue = [$startingFile];
            $visited[$startingFile] = true;

            while (count($queue) > 0) 
            {
                $current = array_shift($queue);

                // If this file references other files, follow those links
                if (isset($graph[$current])) 
                {
                    foreach ($graph[$current] as $neighbor) 
                    {
                        if (!isset($visited[$neighbor])) 
                        {
                            $visited[$neighbor] = true;
                            $queue[] = $neighbor; // Add to queue to explore its references later
                        }
                    }
                }
            }

            // 3. Compare visited files against the master list
            $unnavigableFiles = [];
            foreach (array_keys($allFiles) as $file) 
            {
                if (!isset($visited[$file])) 
                {
                    $unnavigableFiles[] = $file;
                }
            }

            // 4. Output the results
            $totalFiles = count($allFiles);
            $reachableCount = count($visited);
            
            $log .= "--- Navigation Analysis ---" . NEW_LINE;
            $log .= "Starting File: $startingFile" . NEW_LINE;
            $log .= "Total files in system: $totalFiles" . NEW_LINE;
            $log .= "Files reachable from start: $reachableCount" . NEW_LINE . NEW_LINE;

            if (count($unnavigableFiles) === 0) 
            {
                $log .= "SUCCESS: The chain of references eventually includes ALL files! Everything is navigable.\n";
            } 
            else 
            {
                $log .= "There are " . count($unnavigableFiles) . 
					" unnavigable file(s) that cannot be reached from the first file:" . NEW_LINE;
                foreach ($unnavigableFiles as $isolatedFile) 
                {
                    $log .= "  - $isolatedFile"  . NEW_LINE;
                }
            }
			
			// return
			$log .= "SCRIPT SUCCEEDED";
			mysqli_close($link);
			return ["success" => true, "log" => $log, "result" => $unnavigableFiles];
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}