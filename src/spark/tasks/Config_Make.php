<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../utils/commonlib.php";
	set_exception_handler('catchEx');



	// Test if our main config is properly configured and we have an empty DB
	function Config_Make($configName = "_config.php", $addDate = true): array {
		
		$log = "";
		$data = [];
		try 
		{
			// Ask user for values
			$servername = readline("Server name: ");
			$username = readline("Username: ");
			$password = readline("Password: ");
			$dbname = readline("Database name: ");
			
			// Create connection
			try
			{
				$link = @mysqli_connect($servername, $username, $password, $dbname);
			}
			catch(Throwable $x)
			{
				$log .= "Values are NOT correct - we were not able to establish a connection to the server with the values you provided!" . NEW_LINE;
				$log .= "Connection failed: " . mysqli_connect_error() . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}

			// Check connection
			if (!$link)
			{
				$log .= "Ok - values are NOT correct - we were not able to establish a connection to the server with the values you provided!" . NEW_LINE;
				$log .= "Connection failed: " . mysqli_connect_error() . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}
			else
			{
				// Close connection
				mysqli_close($link);
				$log .= "Ok - values are correct - we were able to establish a connection to the server with the values you provided!" . NEW_LINE;
			}

			// Get config template
			$filename = __DIR__ . "\..\utils\installer_templates\_config_template.php";
			if (!file_exists($filename))
			{
				$log .= "Config template file not found: $filename" . NEW_LINE;
				$log .= "SCRIPT FAILED";
				return ["success" => false, "log" => $log, "result" => null];
			}

			// Replace values
			$contents = file_get_contents($filename);
			$updated = str_replace("*SERVER_VALUE*", $servername, $contents);
			$updated = str_replace("*USERNAME_VALUE*", $username, $updated);
			$updated = str_replace("*PASSWORD_VALUE*", $password, $updated);
			$updated = str_replace("*DATABASE_NAME_VALUE*", $dbname, $updated);

			// Save new config file
			$now = date("Ymd-His");
			$newName = "_config(" . $now . ").php";
			if(!$addDate) $newName = "_config.php";
			$newName = __DIR__ . "\..\_configs\\" . $newName;
			file_put_contents($newName, $updated);
			$log .= "Done. Config created: '$newName'" . NEW_LINE;
			$log .= "Rename to 'config.php' to use." . NEW_LINE;
			$log .= "SCRIPT SUCCEEDED";
			return ["success" => true, "log" => $log, "result" => true];
		}
		catch (Throwable $ex) 
		{
			// Optional: log (your handler also logs, so this is redundant but safe)
			//error_log($ex->getMessage());

			$log .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . NEW_LINE;
			$log .= "SCRIPT FAILED";
			return ["success" => false, "log" => $log, "result" => null];
		}
	}