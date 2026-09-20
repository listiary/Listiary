<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_commonlib.php";
	set_exception_handler('catchEx');

	
	//Make the needed tables for listiary on an empty DB
	function MakeTables($configRelativePath, $sqlRelativePath): string {
		
		$output = "";
		try 
		{
			// Include the config
			if (!@include_once __DIR__ . $configRelativePath) 
			{
				throw new Exception("Missing main config file - '" . $configRelativePath . "' ");
			}
			
			//check connection
			$link = connectDb();
			$output .= "Connection OK<br>";
			
			//do
			$files = 
			[
				__DIR__ . $sqlRelativePath . "/init-accounts.sql",
				__DIR__ . $sqlRelativePath . "/init-history.sql",
				__DIR__ . $sqlRelativePath . "/init-housekeeping.sql",
				__DIR__ . $sqlRelativePath . "/init-main.sql",
				__DIR__ . $sqlRelativePath . "/init-permissions.sql",
			];
			foreach ($files as $file)
			{
				$output .= _runSqlFile($link, $file);
			}

			// Return
			$output .= "SCRIPT SUCCEEDED";
			return $output;
		}
		catch (Throwable $ex) 
		{
			// Optional: log (your handler also logs, so this is redundant but safe)
			// error_log($ex->getMessage());
			$output .= "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . "<br>";

			// PANIC: Drop everything from the database
			// DDL statements (like CREATE TABLE) cannot be rolled back via transactions.
			// This blunt tool manually fetches and drops every table and view to reset the DB.
			if (isset($link) && $link instanceof mysqli)
			{
				try 
				{
					$output .= "Initiating panic cleanup...<br>";
					
					// Disable foreign key checks so tables drop without relationship errors
					$link->query("SET FOREIGN_KEY_CHECKS = 0");
					
					// Drop all tables
					$result = $link->query("SHOW TABLES");
					if ($result)
					{
						while ($row = $result->fetch_array()) 
						{
							$tableName = $row[0];
							$link->query("DROP TABLE IF EXISTS `$tableName`");
						}
					}

					// Drop all views (in case your SQLs create any)
					$result = $link->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
					if ($result)
					{
						while ($row = $result->fetch_array()) 
						{
							$viewName = $row[0];
							$link->query("DROP VIEW IF EXISTS `$viewName`");
						}
					}

					// Re-enable foreign key checks
					$link->query("SET FOREIGN_KEY_CHECKS = 1");
					$output .= "Cleanup complete: Database completely wiped.<br>";
				}
				catch (Throwable $cleanupEx)
				{
					$output .= "CRITICAL: Cleanup failed! DB might be in a partial state. " . $cleanupEx->getMessage() . "<br>";
				}
			}
			
			$output .= "SCRIPT FAILED";
			return $output;
		}
	}
	function _runSqlFile(mysqli $link, string $filePath): string {

		$output = "";
		$label = basename($filePath);
		$output .= "Running: '" . $label . "' ...";

		try 
		{
			$sql = file_get_contents($filePath);
			if ($sql === false) 
			{
				throw new Exception("Fail - Cannot read file");
			}

			// Execute multi-statement SQL
			if (!$link->multi_query($sql)) 
			{
				throw new Exception("Fail - SQL execution failed");
			}
			
			// Cycle through all results to ensure completion and catch subsequent errors in the batch
			do 
			{
				if ($result = $link->store_result())
				{
					$result->free();
				}
				if ($link->error) 
				{
					throw new Exception("Fail - SQL error");
				}
			}
			while ($link->more_results() && $link->next_result());

			// Report
			$output .= " OK<br>";
		} 
		catch (Throwable $e) 
		{
			// Stop installer immediately, let the main catch block handle the panic
			$output .= " Fail<br>";
			throw $e; 
		}
		
		return $output;
	}