<?php

	//DB SCHEMA
	class TableAccounts
	{
		public static $name = 'accounts';
		public static $id = 'id';
		public static $email = 'email';
		public static $passwordHash = 'password_hash';
	}
	






	/**
	 * Fetch a user's id and password hash from the accounts table.
	 *
	 * This function retrieves the user's credentials for authentication. 
	 *
	 * @param mysqli $link Database connection object
	 * @param string $email Email of the user to look up
	 * @return array|null ['id' => int, 'password_hash' => string] if user exists, null otherwise
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function fetchUserCredentials(mysqli $link, string $email): ?array {

		$sql = 'SELECT ' . TableAccounts::$id . ', ' . TableAccounts::$passwordHash . 
			' FROM ' . TableAccounts::$name . ' WHERE ' . 
			TableAccounts::$email . ' = ?';

		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt) 
		{
			throw new RuntimeException('Database prepare failed.');
		}
		mysqli_stmt_bind_param($stmt, "s", $email);
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Database execution failed.');
		}

		mysqli_stmt_bind_result($stmt, $id, $password_hash);
		if (!mysqli_stmt_fetch($stmt))
		{
			// No such user
			mysqli_stmt_close($stmt);
			return null;
		}

		mysqli_stmt_close($stmt);

		return [
			'id' => $id,
			'password_hash' => $password_hash,
		];
	}
	
	
	
	