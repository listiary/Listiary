<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//Output the contents of a single file
	function Files_Compile($configRelativePath = "/_configs/config.php") {
		
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
			$sql = "SELECT `content`, `filename` FROM `describe_documents`;";
			$filesFetchResult = mysqli_query($link, $sql);

			// Check if the query failed
			if (!$filesFetchResult) 
			{
				$log .= "SQL Error: " . mysqli_error($link) . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}

			// Check result
			if (mysqli_num_rows($filesFetchResult) <= 0)
			{
				$log .= "No entries fetched." . NEW_LINE;
				$log .= "SCRIPT SUCCEEDED";
				mysqli_close($link);
				return ["success" => true, "log" => $log, "result" => $data];
			}

			// Iterate through rows
			echo "--- IMMEDIATE OUTPUT ---" . NEW_LINE;
			while ($row = mysqli_fetch_assoc($filesFetchResult))
			{
				$filename = $row['filename'];
        		$content = $row['content'];

				//compile data
				$log .= "Compiling $filename... ";
				echo "Compiling $filename... ";
				$result = doPostRequest(COMPILER_URL, $content, $filename);
				//logPostResponse($result);

				//check response
				if ($result == null)
				{
					echo "XX" . NEW_LINE;
					$log .= "ERROR: Response is NULL" . NEW_LINE;
					continue;
				}

				// Decode response JSON
    			$jArr = json_decode($result, true);

				// Check if decoding failed
				if (!is_array($jArr))
				{
					echo "XX" . NEW_LINE;
					$log .= "ERROR: Failed to parse JSON" . NEW_LINE;
					$log .= "--- RAW RESPONSE ---" . NEW_LINE;
					$log .= $result . NEW_LINE;
					$log .= "--------------------" . NEW_LINE;
					continue;
				}

				// Check if "Output" is missing
				if (!isset($jArr["Output"])) 
				{
					echo "XX" . NEW_LINE;
					$log .= "ERROR: 'Output' is missing from response" . NEW_LINE;
					$log .= "--- RAW RESPONSE ---" . NEW_LINE;
					$log .= $result . NEW_LINE;
					$log .= "--------------------" . NEW_LINE;
					continue;
				}

				// Check if "Result" is missing
				if (!isset($jArr["Result"])) 
				{
					echo "XX" . NEW_LINE;
					$log .= "ERROR: 'Result' is missing from response" . NEW_LINE;
					$log .= "--- RAW RESPONSE ---" . NEW_LINE;
					$log .= $result . NEW_LINE;
					$log .= "--------------------" . NEW_LINE;
					continue;
				}

				// Compare result, safely using double quotes around array key
				if (strtolower($jArr["Result"]) !== "success")
				{
					echo "XX" . NEW_LINE;
					$log .= "ERROR: Result is " . $jArr["Result"] . NEW_LINE;
					$log .= "--- RAW RESPONSE ---" . NEW_LINE;
					$log .= $result . NEW_LINE;
					$log .= "--------------------" . NEW_LINE;
					continue;
				}

				// Decode the compiled output
				$compiled64 = $jArr["Output"];
				$compiledJson = base64_decode($compiled64);
				//echo $compiledJson;

				// Upsert document
				$entry_safe = mysqli_real_escape_string($link, $filename);
				$content_safe = mysqli_real_escape_string($link, $compiledJson);
				$sql = "INSERT INTO `compiled_documents` (filename, content, submitted_at)
					VALUES ('$entry_safe', '$content_safe', NOW())
					ON DUPLICATE KEY UPDATE
					content = VALUES(content),
					submitted_at = NOW();";
				if (!mysqli_query($link, $sql))
				{
					echo "XX" . NEW_LINE;
					$log .= "Database error: " . mysqli_error($conn) . NEW_LINE;
				}
				else
				{
					//output
					$data[] =
					[
						"filename" => $entry_safe,
						"content"  => $content_safe
					];

					echo "Ok" . NEW_LINE;
					$log .= "Ok." . NEW_LINE;
				}
			}

			// Close connection
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




	function doPostRequest($url, $code, $filename): string {

		// Base64 encode the source code
		$code_base64 = base64_encode($code);

		//https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
		$requestPayload = [
			'command' => 'parse-file',
			'verbosity' => 'low',
			'translator' => 'JSON',
			'filename' => $filename,
			'code' => $code_base64
		];

		// Convert JSON to string
		$jsonString = json_encode($requestPayload);

		// Base64-encode the JSON string (this is what Lambda expects in request.Body)
		$encodedRequest = base64_encode($jsonString);

		// use key 'http' even if you send the request to https://...
		$options = [
			'http' => [
				'header' => "Content-type: text/plain\r\n",
				'method' => 'POST',
				'content' => $encodedRequest,
			],
		];

		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return $result;
	}
	function logPostResponse($response): string {

		$log = "";
		if ($response == null)
		{
			$log .= "ERROR : response is NULL\n";
			return $log;
		}
		$log .= "\n\nRaw Response:\n$response\n\n";

		$jArr = json_decode($response, true);
		//var_dump($jArr);

		//json.Result
		if (!isset($jArr["Result"])) $log .= "Result : NULL\n";
		else $log .= "Result : " . $jArr["Result"] . "\n";


		//json.Command
		if (!isset($jArr["Command"])) $log .= "Command : NULL\n";
		else $log .= "Command : " . $jArr["Command"] . "\n";
		$log .= "\n\n";

		//json.Logs
		if (!isset($jArr["Logs"])) $log .= "Logs : NULL\n";
		else $log .= "Logs :\n" . $jArr["Logs"] . "\n\n";
		$log .= "\n";

		//json.Output
		if (!isset($jArr["Output"])) $log .= "Output : NULL\n";
		else $log .= "Output :\n" . $jArr["Output"] . "\n";
		$log .= "\n";

		return $log;
	}
	function logArticle($article): string {

		$log = "";
		$log .= "Code :\n" . htmlspecialchars($article);
		$log .= "\n\n\n\n";
		return $log;
	}
	function logCode($code): string {

		$log = "";
		$log .= $code;
		$log .= "\n\n\n\n";
		return $log;
	}