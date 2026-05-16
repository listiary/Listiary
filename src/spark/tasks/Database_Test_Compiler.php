<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');



	// Test if our main config is properly configured and we have an empty DB
	function Database_Test_Compiler($configRelativePath = "/utils/config.php"): array {
		
		$log = "";
		$data = [];
		try 
		{
			//include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
		
			//config info
			$compilerUrl = COMPILER_URL;
			$log .= "Compiler URL: " . $compilerUrl . NEW_LINE . NEW_LINE;
		
			//generate .DS source test file
			$contents = "directives ->\n\n";
			$contents .= "  language-version <1.0>,\n";
			$contents .= "  namespace <test.compiler>,\n";
			$contents .= "  filename <test.compiler.ds>,\n";
			$contents .= "  authors <ADD_AUTHOR>;\n\n\n";
			$contents .= "Test list (Hello Describe Compiler) <.rnode> ->\n\n";
			$contents .= "  ITEM1,\n";
			$contents .= "  ITEM2;";

			//test Compiler
			$url = $compilerUrl;
			$code = $contents;
			$filename = "test.compiler.ds";

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
			
			//report
			$data["json_result"] = $result;
			$log .= "----- RAW RESULT -----" . NEW_LINE;
			$log .= json_encode($jArr) . NEW_LINE;
			$log .= "----------------------" . NEW_LINE . NEW_LINE;

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
			$log .= "Ok - JSON has an 'Output' item." . NEW_LINE;

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
			$log .= "Ok - JSON has an 'Result' item." . NEW_LINE;

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
			$log .= "Ok - JSON 'Result' is 'success'." . NEW_LINE . NEW_LINE;

			// Decode the compiled output
			$compiled64 = $jArr["Output"];
			$compiledJson = base64_decode($compiled64);
			$log .= "----- COMPILED FILE DECODED -----" . NEW_LINE;
			$log .= json_encode($compiledJson) . NEW_LINE;
			$log .= "----------------------" . NEW_LINE . NEW_LINE;

			//return
			$log .= "SCRIPT SUCCEEDED";
			return ["success" => true, "log" => $log, "result" => true];
		}
		catch (Throwable $ex) 
		{
			// Optional: log (your handler also logs, so this is redundant but safe)
			//error_log($ex->getMessage());

			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")"  . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}