<?php

	// Presets
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_commonlib.php";
	set_exception_handler('catchEx');

	try 
	{
		// Include the config
		if (!@include_once __DIR__ . "/../_configs/_config.php") 
		{
			throw new Exception("Missing main config file - '/../_configs/_config.php' ");
		}
		
		//check connection
		$link = connectDb();
		echo "Connection OK<br>";
		
		//do
		$files = 
		[
			__DIR__ . "/../_installer_sqls/init-accounts.sql",
			__DIR__ . "/../_installer_sqls/init-history.sql",
			__DIR__ . "/../_installer_sqls/init-housekeeping.sql",
			__DIR__ . "/../_installer_sqls/init-main.sql",
			__DIR__ . "/../_installer_sqls/init-permissions.sql",
		];
		foreach ($files as $file)
		{
			runSqlFile($link, $file);
		}

		// Return
		echo "SCRIPT SUCCEEDED";
	}
	catch (Throwable $ex) 
	{
		// Optional: log (your handler also logs, so this is redundant but safe)
		// error_log($ex->getMessage());
		echo "EXCEPTION: " . $ex->getMessage() . " (line " . $ex->getLine() . ")" . "<br>";

		// PANIC: Drop everything from the database
		// DDL statements (like CREATE TABLE) cannot be rolled back via transactions.
		// This blunt tool manually fetches and drops every table and view to reset the DB.
		if (isset($link) && $link instanceof mysqli)
		{
			try 
			{
				echo "Initiating panic cleanup...<br>";
				
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
				echo "Cleanup complete: Database completely wiped.<br>";
			}
			catch (Throwable $cleanupEx)
			{
				echo "CRITICAL: Cleanup failed! DB might be in a partial state. " . $cleanupEx->getMessage() . "<br>";
			}
		}
		
		echo "SCRIPT FAILED";
		exit;
	}

	function runSqlFile(mysqli $link, string $filePath): void
	{
		$label = basename($filePath);
		echo "Running: '" . $label . "' ...";

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
			echo " OK<br>";
		} 
		catch (Throwable $e) 
		{
			// Stop installer immediately, let the main catch block handle the panic
			echo " Fail<br>";
			throw $e; 
		}
	}