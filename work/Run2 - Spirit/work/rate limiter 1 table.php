			CREATE TABLE rate_limited_events (

				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				event_type VARCHAR(50) NOT NULL,
				event_id VARCHAR(255) NOT NULL,
				ip_address VARCHAR(45) NOT NULL,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP
			);
			
			CREATE INDEX idx_rate_limit_lookup ON rate_limited_events (event_type, event_id, created_at);
			
			
			
			
			
	// Record failed login Attempt
	function recordRateLimitedEvent_FailedLoginAttempt(mysqli $link, string $email): void {
		
		// Start the transaction
		mysqli_begin_transaction($link);

		try {
			
			// 1. Prune records older than 1 day for THIS email
			$prune_sql = "DELETE FROM rate_limited_events WHERE event_id = ? AND event_type = 'failed_login' AND created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)";
			if ($prune_stmt = mysqli_prepare($link, $prune_sql)) 
			{
				mysqli_stmt_bind_param($prune_stmt, "s", $email);
				if (!mysqli_stmt_execute($prune_stmt)) 
				{
					throw new Exception("Prune execute failed");
				}
				mysqli_stmt_close($prune_stmt);
			} 
			else 
			{
				throw new Exception("Prune prepare failed");
			}

			// 2. Record the current failed attempt
			$eventType = "failed_login";
			$ip = $_SERVER['REMOTE_ADDR'];
			$insert_sql = "INSERT INTO rate_limited_events (event_type, event_id, ip_address) VALUES (?, ?, ?)";
			if ($insert_stmt = mysqli_prepare($link, $insert_sql)) 
			{
				mysqli_stmt_bind_param($insert_stmt, "sss", $eventType, $email, $ip);
				if (!mysqli_stmt_execute($insert_stmt)) 
				{
					throw new Exception("Prune execute failed");
				}
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
	function isIpBlocked_Login(mysqli $link): bool {
		
		$ip = $_SERVER['REMOTE_ADDR'];
		$sql = "SELECT COUNT(*) FROM rate_limited_events WHERE event_type = 'failed_login' AND ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
		
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "s", $ip);
		if (!mysqli_stmt_execute($prune_stmt)) 
		{
			throw new Exception("Prune execute failed");
		}
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		return $count >= 5; // 5 attempts per IP in 15 mins
	}
	
	// Check if specific account is blocked due to too many failed attempts
	function isEmailBlocked_Login(mysqli $link, string $email): bool {
		
		$sql = "SELECT COUNT(*) FROM rate_limited_events WHERE event_type = 'failed_login' AND event_id = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
		
		$stmt = mysqli_prepare($link, $sql);
		mysqli_stmt_bind_param($stmt, "s", $email);
		if (!mysqli_stmt_execute($prune_stmt)) 
		{
			throw new Exception("Prune execute failed");
		}
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		return $count >= 10; // 10 attempts per email in 15 mins
	}

	//given what I do, what indexes to use?

