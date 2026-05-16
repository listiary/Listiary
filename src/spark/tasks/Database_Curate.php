<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//Output the contents of a single file
	function Database_Curate($configRelativePath = "/_configs/config.php") {
		
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

			// Get entries
			$sql = "SELECT `content`, `filename` FROM `compiled_documents`;";
    		$filesFetchResult = mysqli_query($link, $sql);

			// Check result
			if (!$filesFetchResult) 
			{
				$log .= "Error fetching files: " . mysqli_error($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			if (mysqli_num_rows($filesFetchResult) <= 0)
			{
				$log .= "No entries fetched." . NEW_LINE;
				return ["success" => false, "log" => $log, "result" => null];
			}

			// Iterate through rows
			echo "--- IMMEDIATE OUTPUT ---" . NEW_LINE;
			while ($row = mysqli_fetch_assoc($filesFetchResult))
			{
				$filename = $row['filename'];
        		$content = $row['content'];
				echo $filename . "... ";

				// Extract IDs
				$jArr = json_decode($content, true);
				$ids = [];
				extractIds($jArr, $ids);
				$ids = array_keys($ids);

				// Log count
				$length = count($ids);
				$log .= "'{$filename}' has {$length} public ids." . NEW_LINE;
				//print_r($ids); var_dump($ids); break;

				// Upload the data
				$filename_safe = mysqli_real_escape_string($link, $filename);
				$values = [];
				foreach ($ids as $id) 
				{
					$item_id_safe = mysqli_real_escape_string($link, $id);
					$values[] = "('$filename_safe', '$item_id_safe')";
				}
				if (!empty($values))
				{
					$valueString = implode(", ", $values);
					$data[] = $valueString;
					$query = "REPLACE INTO housekeeping_itemid_filename (filename, item_id) VALUES " . $valueString;
					//$query = "INSERT INTO housekeeping_itemid_filename (filename, item_id) VALUES " 
					//. $valueString . " ON DUPLICATE KEY UPDATE filename = VALUES(filename)";

					if (!mysqli_query($link, $query))
					{
						$log .= "Error: " . mysqli_error($link) . NEW_LINE;
						echo "XX (failed)" . NEW_LINE;
					}
					else
					{
						$log .= "Inserted " . mysqli_affected_rows($link) . " rows." . NEW_LINE;
						echo "Ok (" . mysqli_affected_rows($link) . " rows)" . NEW_LINE;
					}
				}
				else
				{
					echo "Ok (0 rows)" . NEW_LINE;
				}
			}
			
			//Curate filenames
			// Get entries
			$sql = "SELECT DISTINCT `filename` FROM `housekeeping_itemid_filename`;";
    		$filesFetchResult = mysqli_query($link, $sql);

			// Check result
			if (!$filesFetchResult) 
			{
				$log .= "Error fetching files: " . mysqli_error($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			if (mysqli_num_rows($filesFetchResult) <= 0)
			{
				$log .= "No entries fetched." . NEW_LINE;
				return ["success" => false, "log" => $log, "result" => null];
			}

			// Iterate through rows
			echo "--- IMMEDIATE OUTPUT ---" . NEW_LINE;
			while ($row = mysqli_fetch_assoc($filesFetchResult))
			{
				$filename = $row['filename'];
        		$filename_escaped = mysqli_real_escape_string($link, $filename);
				$log .= "Working on '{$filename}'... ";
				echo "Working on '{$filename}'... ";

				// Get the ids in this file
				$ids = [];
				$sql = "SELECT `item_id` FROM `housekeeping_itemid_filename` WHERE `filename` = '$filename_escaped';";
				$result2 = mysqli_query($link, $sql);
				while ($row2 = mysqli_fetch_assoc($result2))
				{
					$itemId = $row2['item_id'];
					$ids[] = $itemId;
				}

				// Check there are Ids
				if (empty($ids))
				{
					echo "No item IDs" . NEW_LINE;
					$log .= "No item IDs" . NEW_LINE;
					continue;
				}

				// Get the files for those ids
        		$relatedFilenames = [];
        		$ids_quoted = array_map(fn($id) => "'$id'", $ids);
        		$sql = "SELECT DISTINCT `filename` FROM `housekeeping_itemid_filename` WHERE `item_id` IN (" . implode(',', $ids_quoted) . ");";
        		$result3 = mysqli_query($link, $sql);
				if ($result3)
				{
					while ($row3 = mysqli_fetch_assoc($result3))
					{
						$relFile = $row3['filename'];
						$relatedFilenames[$relFile] = true;
					}
				}
        		$relatedFilenames = array_keys($relatedFilenames);

				// Upload the data
				$values = [];
				foreach ($relatedFilenames as $fn)
				{
					$fn_escaped = mysqli_real_escape_string($link, $fn);
					$values[] = "('$filename_escaped', '$fn_escaped')";
				}
				if (!empty($values))
				{
					$query = "REPLACE INTO housekeeping_filename_related (filename, related_filename) VALUES " 
						. implode(", ", $values);
					if (!mysqli_query($link, $query))
					{
						echo "Error: " . mysqli_error($link) . NEW_LINE;
						$log .= "Error: " . mysqli_error($link) . NEW_LINE;
					}
					else
					{
						echo "Inserted " . mysqli_affected_rows($link) . " rows." . NEW_LINE;
						$log .= "Inserted " . mysqli_affected_rows($link) . " rows." . NEW_LINE;
					}
				}
			}

			// Return
			$log .= "SCRIPT SUCCEEDED";
			mysqli_close($link);
			echo "------------------------" . NEW_LINE;
			return ["success" => true, "log" => $log, "result" => $data];
		}
		catch (Throwable $ex) 
		{
			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}
	function extractIds(array $node, array &$ids) {

		if (isset($node['id']) && is_string($node['id'])) {
			if (strpos($node['id'], '@') !== 0) {
				$ids[$node['id']] = true; // Use value as key to ensure uniqueness
			}
		}

		if (isset($node['items']) && is_array($node['items'])) {
			foreach ($node['items'] as $child) {
				if (is_array($child)) {
					extractIds($child, $ids);
				}
			}
		}
	}