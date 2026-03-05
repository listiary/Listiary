<?php

	/* RATE LIMITER - Logins */
	const MAX_LOGIN_ATTEMPTS_PER_IP = 10;			//10 failed attempts
	const MAX_LOGIN_ATTEMPTS_PER_EMAIL = 5;			//5 failed attempts
	const WAIT_TIME_LOGIN = 15;						//wait for 15 minutes 
	const KEEP_OLD_RECORDS = 7;						//prune old records after 1 day
	
	// Record failed login Attempt
	function recordFailedLoginAttempt(mysqli $link, string $email): void {
		
		// Start the transaction
		mysqli_begin_transaction($link);

		try {
			
			// 1. Prune records older than 1 day for THIS email
			$prune_sql = "DELETE FROM login_attempts WHERE email = ? AND attempt_time < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS . " DAY)";
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
			$insert_sql = "INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)";
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
		$sql = "SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_LOGIN . " MINUTE)";
		
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
		
		$sql = "SELECT COUNT(*) FROM login_attempts WHERE email = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_LOGIN . " MINUTE)";
		
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
		
		$sql = "DELETE FROM login_attempts WHERE email = ?";
		
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

		$sql = "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS . " DAY)";
		if (!mysqli_query($link, $sql)) 
		{
			throw new RuntimeException("Failed to prune login_attempts table.");
		}
	}


	/* RATE LIMITER - Password reset */
	const MAX_RESET_ATTEMPTS_PER_IP = 10;			//10 failed attempts
	const MAX_RESET_ATTEMPTS_PER_EMAIL = 5;			//5 failed attempts
	const WAIT_TIME_RESET = 16;						//wait for 16 minutes 
	const KEEP_OLD_RECORDS_RESETS = 7;				//prune old records after 1 day

	// Record password reset email
	function recordPasswordResetRequest(mysqli $link, string $email): void {
		
		// Start the transaction
		mysqli_begin_transaction($link);

		try {
			
			// 1. Prune records older than 1 day for THIS email
			$prune_sql = "DELETE FROM password_reset_resends WHERE email = ? AND send_time < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS_RESETS . " DAY)";
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
			$insert_sql = "INSERT INTO password_reset_resends (email, ip_address) VALUES (?, ?)";
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
		$sql = "SELECT COUNT(*) FROM password_reset_resends WHERE ip_address = ? AND send_time > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_RESET . " MINUTE)";
		
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
		
		$sql = "SELECT COUNT(*) FROM password_reset_resends WHERE email = ? AND send_time > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_RESET . " MINUTE)";
		
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
		
		$sql = "DELETE FROM password_reset_resends WHERE email = ?";
		
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

		$sql = "DELETE FROM password_reset_resends WHERE send_time < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS_RESETS . " DAY)";
		if (!mysqli_query($link, $sql)) 
		{
			throw new RuntimeException("Failed to prune login_attempts table.");
		}
	}


	/* RATE LIMITER - Register */
	const MAX_REGISTER_SUCCESSES_PER_IP = 2;		//2 successes - 2 accounts can be registered in 1 week with the same ip address
	const WAIT_TIME_REGISTER = 10080;				//wait for 10080 minutes or 1 week
	const KEEP_OLD_RECORDS_REGISTER = 14;			//prune old records after 14 days

	// Record registration Attempt
	function recordRegistrationSuccess(mysqli $link, string $email): void {
		
		// Start the transaction
		mysqli_begin_transaction($link);

		try {
			
			// 1. Prune records older than 1 day for THIS email
			$prune_sql = "DELETE FROM register_success WHERE email = ? AND attempt_time < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS_REGISTER . " DAY)";
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
			$insert_sql = "INSERT INTO register_success (email, ip_address) VALUES (?, ?)";
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
		$sql = "SELECT COUNT(*) FROM register_success WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL " . WAIT_TIME_REGISTER . " MINUTE)";
		
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "s", $ip);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		return $count >= MAX_REGISTER_SUCCESSES_PER_IP;
	}

	// Cleanup Database register_success table by deleting records for an email
	function cleanupLoginAttempts(mysqli $link, string $email): void {
		
		$sql = "DELETE FROM register_success WHERE email = ?";
		
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
	function pruneLoginAttemptsTable(mysqli $link): void {

		$sql = "DELETE FROM register_success WHERE attempt_time < DATE_SUB(NOW(), INTERVAL " . KEEP_OLD_RECORDS_REGISTER . " DAY)";
		if (!mysqli_query($link, $sql)) 
		{
			throw new RuntimeException("Failed to prune register_success table.");
		}
	}