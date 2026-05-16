<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');
	
	//Warning - returning the data from a user file can be vulnerable
	//to command injections if we decide to do something with it later on
	
	//Create a stub file and upload and compile it
	function Action_CreateStub($item, $namespace, $configRelativePath = "/_configs/config.php") {
		
		$log = "";
		try 
		{
			//check namespace
			if (!verifyDescribeNamespace($namespace))
			{
				$log .= "Invalid namespace. Only letters, digits, '.', '-', '_' are allowed." . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}
	
			// Include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
			
			//check connection
			$link = connectDb();
			$log .= "Connection OK" . NEW_LINE;

			//generate .DS source file
			$contents = "directives ->\n\n";
			$contents .= "  language-version <1.0>,\n";
			$contents .= "  namespace <{$namespace}>,\n";
			$contents .= "  filename <{$namespace}.ds>,\n";
			$contents .= "  authors <ADD_AUTHOR>;\n\n\n";
			$contents .= $item . " <.rnode> ->\n\n";
			$contents .= "  ITEM1,\n";
			$contents .= "  ITEM2;";
			
			//put file contents
			$nameEscaped = mysqli_real_escape_string($link, $namespace);
			$contentEscaped = mysqli_real_escape_string($link, $contents);
			$sql = "INSERT INTO `describe_documents` (`filename`, `content`) VALUES ('$nameEscaped', '$contentEscaped')";

			//check result
			$result = mysqli_query($link, $sql);
			if (!$result) 
			{
				if (mysqli_errno($link) == 1062)
				{
					$log .= "Error: A file named '{$fileName}' already exists in the database." . NEW_LINE;
				}
				else
				{
					$log .= "Error uploading file: " . mysqli_error($link) . NEW_LINE;
				}
				$log .= "SCRIPT FAILED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			//compile the file
			$fileName = $namespace;
			$result = doPostRequest(COMPILER_URL, $contents, $fileName);
			//logPostResponse($result);
			
			//check response
			if ($result == null)
			{
				$log .= "Parser response is NULL" . NEW_LINE;
				$log .= "SCRIPT FAILED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			// Decode response JSON
    		$jArr = json_decode($result, true);
			
			// Check if decoding failed
			if (!is_array($jArr))
			{
				$log .= "ERROR: Failed to parse JSON" . NEW_LINE;
				$log .= "--- RAW RESPONSE ---" . NEW_LINE;
				$log .= $result . NEW_LINE;
				$log .= "--------------------" . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			 // Check if "Output" is missing
			if (!isset($jArr["Output"])) 
			{
				$log .= "ERROR: 'Output' is missing from response" . NEW_LINE;
				$log .= "--- RAW RESPONSE ---" . NEW_LINE;
				$log .= $result . NEW_LINE;
				$log .= "--------------------" . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}

			// Check if "Result" is missing
			if (!isset($jArr["Result"])) 
			{
				$log .= "ERROR: 'Result' is missing from response" . NEW_LINE;
				$log .= "--- RAW RESPONSE ---" . NEW_LINE;
				$log .= $result . NEW_LINE;
				$log .= "--------------------" . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}

    		// Compare result, safely using double quotes around array key
    		if (strtolower($jArr["Result"]) !== "success")
			{
				$log .= "ERROR: Result is " . $jArr["Result"] . NEW_LINE;
				$log .= "--- RAW RESPONSE ---" . NEW_LINE;
				$log .= $result . NEW_LINE;
				$log .= "--------------------" . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}

			// Decode the compiled output
			$compiled64 = $jArr["Output"];
			$compiledJson = base64_decode($compiled64);
			//echo $compiledJson;
			
			// Upsert document
			$content_safe = mysqli_real_escape_string($link, $compiledJson);
			$sql = "INSERT INTO `compiled_documents` (filename, content, submitted_at)
				VALUES ('$nameEscaped', '$content_safe', NOW())
				ON DUPLICATE KEY UPDATE
				content = VALUES(content),
				submitted_at = NOW();";
				
			//check result
			if (!mysqli_query($link, $sql))
			{
				$log .= "Database error: " . mysqli_error($conn) . NEW_LINE;
				$log .= "SCRIPT FAILED";
				mysqli_close($link);
				return ["success" => false, "log" => $log, "result" => null];
			}
			
			//return
			$log .= "SCRIPT SUCCEEDED";
			mysqli_close($link);
			return ["success" => true, "log" => $log, "result" => true];
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