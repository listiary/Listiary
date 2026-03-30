<?php

	require_once __DIR__ . "/_sql_model.php";
	
	
	
	// Record failed login Attempt
	function recordFailedLoginAttempt(mysqli $link, string $email): void {
		
		// Start the transaction
		mysqli_begin_transaction($link);

		try {
			
			// 1. Prune records older than 1 day for THIS email
			$prune_sql = "DELETE FROM " . TableLoginAttempts::$tableName . 
				" WHERE " . TableLoginAttempts::$email . " = ?" . 
				" AND " . TableLoginAttempts::$attemptTime . " < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS . " DAY)";

			if ($prune_stmt = mysqli_prepare($link, $prune_sql)) 
			{
				mysqli_stmt_bind_param($prune_stmt, "s", $email);
				mysqli_stmt_execute($prune_stmt);
				mysqli_stmt_close($prune_stmt);
			} 
			else 
			{
				throw new Exception("Prune prepare failed");
			}

			// 2. Record the current failed attempt
			$insert_sql = "INSERT INTO " . TableLoginAttempts::$tableName . 
				" (" . TableLoginAttempts::$email . ", " . TableLoginAttempts::$ipAddress . ") VALUES (?, ?)";
				
			if ($insert_stmt = mysqli_prepare($link, $insert_sql)) 
			{
				mysqli_stmt_bind_param($insert_stmt, "ss", $email, $_SERVER['REMOTE_ADDR']);
				mysqli_stmt_execute($insert_stmt);
				mysqli_stmt_close($insert_stmt);
			} 
			else 
			{
				throw new Exception("Insert prepare failed");
			}

			// Commit the transaction
			mysqli_commit($link);
		}
		catch (Exception $e) 
		{
			// Rollback on error
			mysqli_rollback($link);
			throw new RuntimeException("Database error: Transaction failed.");
		}
	}

	// Check if IP is blocked due to too many failed attempts
	function isIpBlocked(mysqli $link): bool {
		
		$ip = $_SERVER['REMOTE_ADDR'];
		$sql = "SELECT COUNT(*) FROM " . TableLoginAttempts::$tableName . 
			" WHERE " . TableLoginAttempts::$ipAddress . " = ?" . 
			" AND " . TableLoginAttempts::$attemptTime . " > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_LOGIN . " MINUTE)";
		
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "s", $ip);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		return $count >= MAX_LOGIN_ATTEMPTS_PER_IP;
	}

	// Check if specific account is blocked due to too many failed attempts
	function isEmailBlocked(mysqli $link, string $email): bool {
		
		$sql = "SELECT COUNT(*) FROM " . TableLoginAttempts::$tableName . 
			" WHERE " . TableLoginAttempts::$email . " = ?" .
			" AND " . TableLoginAttempts::$attemptTime . " > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_LOGIN . " MINUTE)";
		
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "s", $email);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		return $count >= MAX_LOGIN_ATTEMPTS_PER_EMAIL;
	}

	// Cleanup Database login_attempts table by deleting records for an email
	function cleanupLoginAttempts(mysqli $link, string $email): void {
		
		$sql = "DELETE FROM " . TableLoginAttempts::$tableName . 
			" WHERE " . TableLoginAttempts::$email . " = ?";
		
		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException("Database error preparing cleanup.");
		}
		
		// Bind the $email parameter to the '?' in the query
		mysqli_stmt_bind_param($stmt, "s", $email);
		
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException("Failed to cleanup login_attempts table.");
		}
		
		mysqli_stmt_close($stmt);
	}

	// Prune Database login_attempts table by deleting records older than 7 days.
	// Need to execute manually once in a while from the ARC panel
	function pruneLoginAttemptsTable(mysqli $link): void {

		$sql = "DELETE FROM " . TableLoginAttempts::$tableName . 
			" WHERE " . TableLoginAttempts::$attemptTime . " < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS . " DAY)";

		if (!mysqli_query($link, $sql)) 
		{
			throw new RuntimeException("Failed to prune login_attempts table.");
		}
	}



	// Record password reset email
	function recordPasswordResetRequest(mysqli $link, string $email): void {
		
		// Start the transaction
		mysqli_begin_transaction($link);

		try {
			
			// 1. Prune records older than 1 day for THIS email
			$prune_sql = "DELETE FROM " . TablePasswordResetResends::$tableName . 
				" WHERE " . TablePasswordResetResends::$email . " = ?" . 
				" AND " . TablePasswordResetResends::$sendTime . " < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS_RESETS . " DAY)";

			if ($prune_stmt = mysqli_prepare($link, $prune_sql)) 
			{
				mysqli_stmt_bind_param($prune_stmt, "s", $email);
				mysqli_stmt_execute($prune_stmt);
				mysqli_stmt_close($prune_stmt);
			} 
			else 
			{
				throw new Exception("Prune prepare failed");
			}

			// 2. Record the current failed attempt
			$insert_sql = "INSERT INTO " . TablePasswordResetResends::$tableName . 
				" (" . TablePasswordResetResends::$email . ", " . TablePasswordResetResends::$ipAddress . ") VALUES (?, ?)";

			if ($insert_stmt = mysqli_prepare($link, $insert_sql)) 
			{
				mysqli_stmt_bind_param($insert_stmt, "ss", $email, $_SERVER['REMOTE_ADDR']);
				mysqli_stmt_execute($insert_stmt);
				mysqli_stmt_close($insert_stmt);
			} 
			else 
			{
				throw new Exception("Insert prepare failed");
			}

			// Commit the transaction
			mysqli_commit($link);
		}
		catch (Exception $e) 
		{
			// Rollback on error
			mysqli_rollback($link);
			throw new RuntimeException("Database error: Transaction failed.");
		}
	}

	// Check if IP is blocked due to too many failed attempts
	function isIpBlockedForResetRequest(mysqli $link): bool {
		
		$ip = $_SERVER['REMOTE_ADDR'];
		$sql = "SELECT COUNT(*) FROM " . TablePasswordResetResends::$tableName . 
			" WHERE " . TablePasswordResetResends::$ipAddress . " = ?" . 
			" AND " . TablePasswordResetResends::$sendTime . " > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_RESET . " MINUTE)";
		
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "s", $ip);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		return $count >= MAX_RESET_ATTEMPTS_PER_IP;
	}

	// Check if specific account is blocked due to too many failed attempts
	function isEmailBlockedForResetRequest(mysqli $link, string $email): bool {
		
		$sql = "SELECT COUNT(*) FROM " . TablePasswordResetResends::$tableName . 
			" WHERE " . TablePasswordResetResends::$email " = ?" . 
			" AND " . TablePasswordResetResends::$sendTime . " > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_RESET . " MINUTE)";
		
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "s", $email);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		return $count >= MAX_RESET_ATTEMPTS_PER_EMAIL;
	}

	// Cleanup Database login_attempts table by deleting records for an email
	function cleanupResetAttempts(mysqli $link, string $email): void {
		
		$sql = "DELETE FROM " . TablePasswordResetResends::$tableName . 
			" WHERE " . TablePasswordResetResends::$email " = ?";
		
		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException("Database error preparing cleanup.");
		}
		
		// Bind the $email parameter to the '?' in the query
		mysqli_stmt_bind_param($stmt, "s", $email);
		
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException("Failed to cleanup login_attempts table.");
		}
		
		mysqli_stmt_close($stmt);
	}

	// Prune Database login_attempts table by deleting records older than 7 days.
	// Need to execute manually once in a while from the ARC panel
	function pruneResetAttemptsTable(mysqli $link): void {

		$sql = "DELETE FROM " . TablePasswordResetResends::$tableName . 
			" WHERE " . TablePasswordResetResends::$sendTime . " < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS_RESETS . " DAY)";

		if (!mysqli_query($link, $sql)) 
		{
			throw new RuntimeException("Failed to prune login_attempts table.");
		}
	}



	// Record registration Attempt
	function recordRegistrationSuccess(mysqli $link, string $email): void {
		
		// Start the transaction
		mysqli_begin_transaction($link);

		try {
			
			// 1. Prune records older than 1 day for THIS email
			$prune_sql = "DELETE FROM " . TableRegisterSuccess::$tableName . 
				" WHERE " . TableRegisterSuccess::$email . " = ?" . 
				" AND " . TableRegisterSuccess::$attemptTime . " < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS_REGISTER . " DAY)";

			if ($prune_stmt = mysqli_prepare($link, $prune_sql)) 
			{
				mysqli_stmt_bind_param($prune_stmt, "s", $email);
				mysqli_stmt_execute($prune_stmt);
				mysqli_stmt_close($prune_stmt);
			} 
			else 
			{
				throw new Exception("Prune prepare failed");
			}

			// 2. Record the current success attempt
			$insert_sql = "INSERT INTO " . TableRegisterSuccess::$tableName . 
				" (" . TableRegisterSuccess::$email . ", " . TableRegisterSuccess::$ipAddress . ") VALUES (?, ?)";

			if ($insert_stmt = mysqli_prepare($link, $insert_sql)) 
			{
				mysqli_stmt_bind_param($insert_stmt, "ss", $email, $_SERVER['REMOTE_ADDR']);
				mysqli_stmt_execute($insert_stmt);
				mysqli_stmt_close($insert_stmt);
			} 
			else 
			{
				throw new Exception("Insert prepare failed");
			}

			// Commit the transaction
			mysqli_commit($link);
		}
		catch (Exception $e) 
		{
			// Rollback on error
			mysqli_rollback($link);
			throw new RuntimeException("Database error: Transaction failed.");
		}
	}

	// Check if IP is blocked due to too many failed attempts
	function isIpBlockedForRegister(mysqli $link): bool {
		
		$ip = $_SERVER['REMOTE_ADDR'];
		$sql = "SELECT COUNT(*) FROM " . TableRegisterSuccess::$tableName . 
			" WHERE " . TableRegisterSuccess::$ipAddress . " = ?" . 
			" AND " . TableRegisterSuccess::$attemptTime . " > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_REGISTER . " MINUTE)";
		
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "s", $ip);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		return $count >= MAX_REGISTER_SUCCESSES_PER_IP;
	}

	// Cleanup Database register_success table by deleting records for an email
	function cleanupRegisterSuccessEvents(mysqli $link, string $email): void {
		
		$sql = "DELETE FROM " . TableRegisterSuccess::$tableName . 
			" WHERE " . TableRegisterSuccess::$email . " = ?";
		
		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException("Database error preparing cleanup.");
		}
		
		// Bind the $email parameter to the '?' in the query
		mysqli_stmt_bind_param($stmt, "s", $email);
		
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException("Failed to cleanup register_success table.");
		}
		
		mysqli_stmt_close($stmt);
	}

	// Prune Database register_success table by deleting records older than 7 days.
	// Need to execute manually once in a while from the ARC panel
	function pruneRegisterSuccessTable(mysqli $link): void {

		$sql = "DELETE FROM " . TableRegisterSuccess::$tableName . 
			" WHERE " . TableRegisterSuccess::$attemptTime . " < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS_REGISTER . " DAY)";

		if (!mysqli_query($link, $sql)) 
		{
			throw new RuntimeException("Failed to prune register_success table.");
		}
	}