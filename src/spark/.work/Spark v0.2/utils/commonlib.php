<?php

	define("NEW_LINE", "\n");


	/**
	 * Open a connection to the DB
	 * @return mysqli The connection object
	 */
	function connectDb(): mysqli {

		static $connection = null;

		if ($connection === null) 
		{
			try
			{
				$connection = new mysqli(
					DB_SERVER_PUBLIC,
					DB_USERNAME_PUBLIC,
					DB_PASSWORD_PUBLIC,
					DB_NAME_PUBLIC
				);
			} 
			catch (mysqli_sql_exception $e) 
			{
				// Show generic message
				throw new RuntimeException('Database connection failed.');
			}
		}

		//return
		return $connection;
	}

	/**
	 * Default Exception handler
	 * @param Throwable $ex The exception handle
	 * @return void
	 */
	function catchEx(Throwable $ex): void {

		if (PHP_SAPI === 'cli') logExToConsole($ex);
		else logExToSite($ex);
		exit(1); // Exit with error code
	}
	function logExToConsole(Throwable $ex): void {
		
		if (PHP_SAPI === 'cli') 
		{
			// CLI Error Output
			fwrite(STDERR, "FATAL ERROR: " . $ex->getMessage() . PHP_EOL);
			fwrite(STDERR, $ex->getTraceAsString() . PHP_EOL);
		}
	}
	function logExToSite(Throwable $ex): void {
		
		echo "FATAL ERROR: " . $ex->getMessage();
		echo "<br>";
		echo $ex->getTraceAsString();
	}
	function logExToText(Throwable $ex): void {
		
		echo "FATAL ERROR: " . $ex->getMessage();
		echo "\n";
		echo $ex->getTraceAsString();
	}
	function logExToVar(Throwable $ex): string {
		
		$msg = "FATAL ERROR: " . $ex->getMessage();
		$msg .= "\n";
		$msg .= $ex->getTraceAsString();
	}
	
	/**
	 * Outputs an ASCII art banner to the console
	 * @return the output string with the ASCII art
	 */
	function printBanner(): void {

		// https://www.asciiart.eu/text-to-ascii-art - Isometric2
		echo 
			NEW_LINE . NEW_LINE .
			"      ___           ___         ___           ___           ___      " . NEW_LINE .
			"     /\__\         /\  \       /\  \         /\  \         /\  \     " . NEW_LINE .
			"    /:/ _/_       /::\  \     /::\  \       /::\  \        \:\  \    " . NEW_LINE .
			"   /:/ /\  \     /:/\:\__\   /:/\:\  \     /:/\:\__\        \:\__\   " . NEW_LINE .
			"  /:/ /::\  \   /:/ /:/  /  /:/ /::\  \   /:/ /:/__/_   __  /:/__/_  " . NEW_LINE .
			" /:/_/:/\:\__\ /:/_/:/  /  /:/_/:/\:\__\ /:/_/:/\:\__\ /\ \/:/\:\__\ " . NEW_LINE .
			" \:\/:/ /:/  / \:\/:/  /   \:\/:/  \/__/ \:\/:/  \/__/ \:\/:/  \/__/ " . NEW_LINE .
			"  \::/ /:/  /   \::/__/     \::/__/       \::/__/       \::/__/      " . NEW_LINE .
			"   \/_/:/  /     \:\  \      \:\  \        \:\  \        \:\  \      " . NEW_LINE .
			"     /:/  /       \:\__\      \:\__\        \:\__\        \:\__\     " . NEW_LINE .
			"     \/__/         \/__/       \/__/         \/__/         \/__/     " . NEW_LINE . 
			NEW_LINE . NEW_LINE ;
	}
	
	
	
	
	function verifyDescribeItemId($fileName) {
		
		if (preg_match('/^[\p{L}0-9._-]+$/u', $fileName)) return true;
		return false;
	}
	function verifyDescribeFilename($fileName) {
		
		if (preg_match('/^[\p{L}0-9._-]+$/u', $fileName)) return true;
		return false;
	}
	function verifyDescribeNamespace($namespace) {
		
		//same as filename, but first or last symbol cannot be dots
		if (preg_match('/^(?!\.)[\p{L}0-9._-]+(?<!\.)$/u', $namespace)) return true;
		return false;
	}
	function verifyDescribeFilenameLatin($fileName) {
		
		if (preg_match('/^[a-zA-Z0-9._-]+$/', $fileName)) return true;
		return false;
	}