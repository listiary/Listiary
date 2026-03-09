<?php


	// Get the ID of the user to be shown from URL GET parameter 'id'
	function getUserId(): int {

		$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
		if ($id === null || $id === false) 
		{
			throw new RuntimeException('Invalid or missing "id" parameter in URL');
		}

		return $id;
	}

	// Retrieve 'id' and 'password_hash' for a user provided email from DB
	// Return NULL if no such email in our DB
	function fetchUserCredentials(mysqli $link, string $email): ?array {

		$sql = "SELECT id, password_hash FROM accounts WHERE email = ?";
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
	
	// Fetch username and email for not logged in user
	function fetchUserData(mysqli $link, int $user_id): array {
		
		// Fetch user info
		$userSql = "SELECT username, email, usercode, is_bot, is_active, is_premium, created_at 
					FROM accounts 
					WHERE id = ? LIMIT 1";
		$userStmt = mysqli_prepare($link, $userSql);
		if (!$userStmt) {
			throw new RuntimeException('DB prepare failed for user info');
		}

		mysqli_stmt_bind_param($userStmt, "i", $user_id);
		mysqli_stmt_execute($userStmt);
		
		$result = mysqli_stmt_get_result($userStmt);
		$row = mysqli_fetch_assoc($result);
		mysqli_stmt_close($userStmt);
		
		if (!$row)
		{
			throw new RuntimeException('User not found for session restoration');
		}

		// Populate session
		return [
			"username" => $row['username'],
			"email" => $row['email'],
			"is_bot" => (bool)$row['is_bot'],
			"is_active" => (bool)$row['is_active'],
			"is_premium" => (bool)$row['is_premium'],
			"created_at" => $row['created_at']
		];
	}

	// Get User details and store them in session
	function fetchUserDetails(mysqli $link, int $user_id): void {
		
		/* Get the details for the user - bio, avatar, etc. 
		 * They live in a separate table in the database. */
		
		// Fetch user details
		$userSql = "SELECT avatar_path, avatar_shape, avatar_shape_radius, bio, city, country, timezone, phone1 
					FROM account_details 
					WHERE account_id = ? LIMIT 1";
		$userStmt = mysqli_prepare($link, $userSql);
		if (!$userStmt)
		{
			throw new RuntimeException('DB prepare failed for user details');
		}

		mysqli_stmt_bind_param($userStmt, "i", $user_id);
		mysqli_stmt_execute($userStmt);
		$result = mysqli_stmt_get_result($userStmt);
		$row = mysqli_fetch_assoc($result); // associative array

		mysqli_stmt_close($userStmt);
		if (!$row) 
		{
			throw new RuntimeException('User not found for fetching details');
		}


		// Populate session
		$_SESSION['avatar_path'] = $row['avatar_path'];
		$_SESSION['avatar_shape'] = $row['avatar_shape'];
		$_SESSION['avatar_shape_radius'] = $row['avatar_shape_radius'];
		$_SESSION['bio'] = $row['bio'];
		$_SESSION['city'] = $row['city'];
		$_SESSION['country'] = $row['country'];
		$_SESSION['timezone'] = $row['timezone'];
		$_SESSION['phone1'] = $row['phone1'];
	}

	// Get User details and store them in an associative array
	function fetchUserDetailsArr(mysqli $link, int $user_id): array {
		
		/* Get the details for the user - bio, avatar, etc. 
		 * They live in a separate table in the database. */
		
		// Fetch user details
		$userSql = "SELECT avatar_path, avatar_shape, avatar_shape_radius, bio, city, country, timezone, phone1 
					FROM account_details 
					WHERE account_id = ? LIMIT 1";
		$userStmt = mysqli_prepare($link, $userSql);
		if (!$userStmt)
		{
			throw new RuntimeException('DB prepare failed for user details');
		}

		mysqli_stmt_bind_param($userStmt, "i", $user_id);
		mysqli_stmt_execute($userStmt);
		$result = mysqli_stmt_get_result($userStmt);
		$row = mysqli_fetch_assoc($result); // associative array

		mysqli_stmt_close($userStmt);
		if (!$row) 
		{
			throw new RuntimeException('User not found for fetching details');
		}


		// Populate session
		return [
			"avatar_path" => $row['avatar_path'],
			"avatar_shape" => $row['avatar_shape'],
			"avatar_shape_radius" => $row['avatar_shape_radius'],
			"bio" => $row['bio'],
			"city" => $row['city'],
			"country" => $row['country'],
			"timezone" => $row['timezone'],
			"phone1" => $row['phone1']
		];
	}

	// Check if we can resend password reset email (5 minutes have passed from the last sending)
	function checkIfTooSoonToResend(mysqli $link, string $email): bool {

		//should we add `created_at` field in the DB as well?
		$sql = "SELECT 1
				FROM password_resets
				WHERE email = ?
				AND expires_at > DATE_ADD(NOW(), INTERVAL 55 MINUTE)
				LIMIT 1";

		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException("Database error.");
		}

		mysqli_stmt_bind_param($stmt, "s", $email);
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException("Database error.");
		}

		mysqli_stmt_store_result($stmt);
		$res = mysqli_stmt_num_rows($stmt);
		$tooSoon = $res > 0;
		
		mysqli_stmt_close($stmt);
		return $tooSoon;
	}

	// Send password reset email and save reset token in the DB
	function sendPasswordResetEmail(mysqli $link, string $email): bool {
    
		// 1. Generate a raw, cryptographically secure token
		// This is what we will email to the user.
		$raw_token = bin2hex(random_bytes(32));

		// 2. Hash the token for database storage
		// If a hacker steals your DB, they only get the hashes, not the usable links.
		$hashed_token = hash('sha256', $raw_token);

		// 3. Insert or Update the token in MariaDB
		// ON DUPLICATE KEY UPDATE ensures that if a user requests a reset 3 times,
		// they don't get 3 rows. It just overwrites their existing token and extends the timer.
		$sql = "INSERT INTO password_resets (email, token, expires_at) 
				VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR)) 
				ON DUPLICATE KEY UPDATE 
				token = VALUES(token), 
				expires_at = VALUES(expires_at)";
            
		if ($stmt = mysqli_prepare($link, $sql)) 
		{
			// Bind parameters: 's' for email (string), 's' for hashed_token (string)
			mysqli_stmt_bind_param($stmt, "ss", $email, $hashed_token);
			
			// Execute and check for failure
			if (!mysqli_stmt_execute($stmt)) 
			{
				// In a real app, you might want to log this error silently
				//error_log("Database error creating reset token: " . mysqli_error($link));
				mysqli_stmt_close($stmt);
				return false;
			}
			mysqli_stmt_close($stmt);
		} 
		else 
		{
			return false; // Statement preparation failed
		}

		// 4. Construct the Reset URL
		// We send the RAW token in the URL, not the hashed one!
		// We also pass the email in the URL to make looking up the database row easier later.
		$reset_url = "https://development.listiary.org/session/m.resetpass.php?token=" . $raw_token;

		// 5. Send the Email
		$subject = "Password Reset Request";
		
		$message = "Hello,\n\n";
		$message .= "We received a request to reset the password for your account.\n";
		$message .= "You can reset your password by clicking the link below:\n\n";
		$message .= $reset_url . "\n\n";
		$message .= "This link will expire in 1 hour. If you did not request a password reset, please ignore this email.\n";

		// Standard email headers
		$headers = "From: noreply@listiary.org\r\n";
		$headers .= "Reply-To: noreply@listiary.org\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion();

		// Use PHP's built-in mail function (Note: In production, consider using PHPMailer or Symfony Mailer for better deliverability)
		if (mail($email, $subject, $message, $headers)) 
		{
			return true;
		}
		else 
		{
			return false;
		}
	}

	// Dummy version of sendPasswordResetEmail that takes time to execute.
	// Use to twart timing attacks aiming at email enumeration
	function sendPasswordResetEmailDummy(mysqli $link, string $email): bool {

		// 1. Generate token (same CPU cost)
		$raw_token = bin2hex(random_bytes(32));

		// 2. Hash token (same CPU cost)
		$hashed_token = hash('sha256', $raw_token);

		// 3. Perform a harmless dummy query to simulate DB timing
		$sql = "SELECT SLEEP(0.05)"; // 50ms artificial DB delay
		if ($stmt = mysqli_prepare($link, $sql)) {
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		}

		// 4. Add small randomized delay to reduce timing fingerprinting
		usleep(random_int(20000, 60000)); // 20–60ms

		// 5. Always return true to avoid leaking account existence
		return true;
	}

	// Send account activation link
	function sendVerificationEmail(mysqli $link, string $username, string $email, string $raw_token): bool {
		
		// Construct the verification URL
		// We send the RAW token in the URL, not the hashed one!
		// We also pass the email in the URL to make looking up the database row easier later.
		$verify_url = "https://development.listiary.org/session/m.verify.php?token=" . $raw_token;

		// Send the Email
		$subject = "Welcome - account verification";
		$message  = "Hello {$username},\n\n";
		$message .= "Welcome.\n\n";
		$message .= "Your account is ready. To activate it, please verify your email address using the link below:\n\n";
		$message .= $verify_url . "\n\n";
		$message .= "If you did not create this account, you can safely ignore this message.\n\n";
		$message .= "We look forward to seeing what you create.\n\n";
		$message .= "If you did not register this account, you can safely ignore this email.\n";

		// Standard email headers
		$headers = "From: noreply@listiary.org\r\n";
		$headers .= "Reply-To: noreply@listiary.org\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion();

		// Use PHP's built-in mail function (Note: In production, consider using PHPMailer or Symfony Mailer for better deliverability)
		if (mail($email, $subject, $message, $headers)) 
		{
			return true;
		}
		else 
		{
			return false;
		}
	}

	// Check if account with that email exists in the database
	function isEmailInDatabase(mysqli $link, string $email): bool {
		
		$sql = "SELECT 1 FROM accounts WHERE email = ? LIMIT 1";
		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt)
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}

		// Bind variables to the prepared statement as parameters
		if (!mysqli_stmt_bind_param($stmt, "s", $email)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}

		// Attempt to execute the prepared statement
		if (!mysqli_stmt_execute($stmt)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}

		mysqli_stmt_store_result($stmt);
		$exists = mysqli_stmt_num_rows($stmt) > 0;

		mysqli_stmt_close($stmt);
		return $exists;
	}

	// Update password in the DB
	function updatePassword(mysqli $link, string $email) {
		
		$sql = "UPDATE accounts SET password_hash = ? WHERE email = ?";
		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt)
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		
		// Bind variables to the prepared statement as parameters
		if (!mysqli_stmt_bind_param($stmt, "ss", $param_new_password_hash, $param_email))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		
		// Set parameters
		$param_email = $email;
		$param_new_password_hash = password_hash($new_password, PASSWORD_BCRYPT); // Creates a password hash
		
		// Attempt to execute the prepared statement
		if (!mysqli_stmt_execute($stmt)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
	}

	// Check if a user with that username exists in the DB
	function isUsernameTaken(mysqli $link, string $username): bool {
		
		// Prepare a select statement
		$sql = "SELECT id FROM accounts WHERE username = ?";
	
		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		if(!mysqli_stmt_bind_param($stmt, "s", $param_username))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		$param_username = trim($username);
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
		
		/* store result */
		mysqli_stmt_store_result($stmt);
		if(mysqli_stmt_num_rows($stmt) == 1) return true;
		else return false;
	}

	// Check if a user with that email exists in the DB
	function isEmailTaken(mysqli $link, string $email): bool {
		
		// Prepare a select statement
		$sql = "SELECT id FROM accounts WHERE email = ?";
	
		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		if(!mysqli_stmt_bind_param($stmt, "s", $param_email))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		$param_email = trim($email);
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
		
		/* store result */
		mysqli_stmt_store_result($stmt);
		if(mysqli_stmt_num_rows($stmt) == 1) return true;
		else return false;
	}

	// Create a new account in the DB
	function createAccount($link, $username, $email, $password, $isBot = 0) {
		
		// Prepare an insert statement
		$sql = "INSERT INTO accounts (username, email, password_hash, usercode, is_bot, verification_token) VALUES (?, ?, ?, ?, ?, ?)";
		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		
		// Bind variables to the prepared statement as parameters
		if(!mysqli_stmt_bind_param($stmt, "ssssis", $param_username, $param_email, $param_password_hash, $param_usercode, $param_is_bot, $param_verification_token_hash))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		
		// Set parameters
		$param_username = $username;
		$param_email = $email;
		$param_password_hash = password_hash($password, PASSWORD_BCRYPT); // Creates a password hash
		$raw = hash_hmac('sha256', $param_username . $param_email, $secret_key, true);
		$param_usercode = substr(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='), 0, 16);
		$param_is_bot = 0;

		// Set parameters - verification token
		$raw_token = bin2hex(random_bytes(32));
		$hashed_token = hash('sha256', $raw_token);
		$param_verification_token_hash = $hashed_token;
		
		// Attempt to execute the prepared statement
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
		mysqli_stmt_close($stmt);
	}





	// Check CSRF token and regenerate it
	function isCsrfTokenValid(): bool {

		if (
			empty($_POST['csrf_token']) ||
			empty($_SESSION['csrf_token']) ||
			!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
		) 
		{
			return false;
		}

		// Rotate token after successful validation
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		return true;
	}
	
	// Log in with a remember me token and the database.
	function doRememberedLogin(mysqli $link): bool {

		/* Attempt a "remember me" login using persistent cookie
		 * Returns true if login succeeds, false if cookie invalid or expired
		 * Throws RuntimeException for malformed cookie or serious DB issues */
	 
		if (!isset($_COOKIE['remember_token'])) return false;
		$cookie = $_COOKIE['remember_token'];

		// Split cookie into selector and validator
		if (strpos($cookie, ':') === false)
		{
			throw new RuntimeException('Invalid remember_token cookie format');
		}
		list($selector, $validator) = explode(':', $cookie, 2);
		if (empty($selector) || empty($validator)) 
		{
			throw new RuntimeException('Invalid remember_token cookie content');
		}

		// Look up selector in DB
		$sql = "SELECT user_id, token_hash, expires_at 
				FROM persistent_logins 
				WHERE selector = ? LIMIT 1";
		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt) 
		{
			throw new RuntimeException('DB prepare failed');
		}
		mysqli_stmt_bind_param($stmt, "s", $selector);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $user_id, $token_hash, $expires_at);
		$rowExists = mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		if (!$rowExists) 
		{
			// No such selector - possibly stale or forged cookie
			setcookie('remember_token', '', time() - 3600, '/', '', true, true);
			return false;
		}

		// Verify expiration and token
		$now = new DateTime();
		$expires = new DateTime($expires_at);
		$validatorHash = hash('sha256', $validator);

		// If token invalid or expired - delete DB row + remove cookie
		if ($now > $expires || !hash_equals($token_hash, $validatorHash))
		{
			invalidateRememberToken($link, $selector);
			return false;
		}

		// Token valid thus restore session
		restoreUserSession($link, $user_id);

		// Rotate persistent token safely
		executePersistentTokenRotation($link, $user_id, $selector, $validator);

		return true;
	}

	// Delete token from DB
	function invalidateRememberToken(mysqli $link, string $selector): void {
		
		/* Invalidate a persistent login token.
		 * Deletes the token row from the database and expires the client cookie. */
		
		$delSql = "DELETE FROM persistent_logins WHERE selector = ?";
		$delStmt = mysqli_prepare($link, $delSql);
		if ($delStmt) {
			mysqli_stmt_bind_param($delStmt, "s", $selector);
			mysqli_stmt_execute($delStmt);
			mysqli_stmt_close($delStmt);
		}

		// Expire cookie on client
		setcookie('remember_token', '', time() - 3600, '/', '', true, true);
	}
	
	// Delete all the tokens from DB for an user
	function invalidateAllRememberTokens(mysqli $link, int $id): void {
		
		/* Invalidate a persistent login token.
		 * Deletes the token row from the database and expires the client cookie. */
		
		$delSql = "DELETE FROM persistent_logins WHERE user_id = ?";
		$delStmt = mysqli_prepare($link, $delSql);
		if ($delStmt) {
			mysqli_stmt_bind_param($delStmt, "i", $id);
			mysqli_stmt_execute($delStmt);
			mysqli_stmt_close($delStmt);
		}

		// Expire cookie on client
		setcookie('remember_token', '', time() - 3600, '/', '', true, true);
	}

	// Fetch user info from accounts table and populates $_SESSION
	function restoreUserSession(mysqli $link, int $user_id): void {

		 /* Restore session for a given user ID.
		  * Fetches user info from accounts table and populates $_SESSION
		  * Throws RuntimeException if DB fails or user not found. */
  
		// Regenerate session ID to prevent session fixation
		session_regenerate_id(true);
		$_SESSION['loggedin'] = true;
		$_SESSION['id'] = $user_id;

		// Fetch user info
		$userSql = "SELECT username, email, usercode, is_bot, is_active, is_premium, created_at 
					FROM accounts 
					WHERE id = ? LIMIT 1";
		$userStmt = mysqli_prepare($link, $userSql);
		if (!$userStmt) {
			throw new RuntimeException('DB prepare failed for user info');
		}

		mysqli_stmt_bind_param($userStmt, "i", $user_id);
		mysqli_stmt_execute($userStmt);
		
		$result = mysqli_stmt_get_result($userStmt);
		$row = mysqli_fetch_assoc($result);
		mysqli_stmt_close($userStmt);
		
		if (!$row)
		{
			throw new RuntimeException('User not found for session restoration');
		}

		// Populate session
		$_SESSION['username'] = $row['username'];
		$_SESSION['email'] = $row['email'];
		$_SESSION['usercode'] = $row['usercode'];
		$_SESSION['is_bot'] = (bool)$row['is_bot'];
		$_SESSION['is_active'] = (bool)$row['is_active'];
		$_SESSION['is_premium'] = (bool)$row['is_premium'];
		$_SESSION['created_at'] = $row['created_at'];
	}
	
	// Stores persistent login token to the database
	function executePersistentLogin(mysqli $link, int $user_id): void {

		// Generate token and selector
		$token = bin2hex(random_bytes(32));
		$hashedToken = hash('sha256', $token);
		$selector = bin2hex(random_bytes(8));

		// SQL query
		$sql = "INSERT INTO persistent_logins 
				(user_id, selector, token_hash, expires_at) 
				VALUES (?, ?, ?, NOW() + INTERVAL 1 YEAR)";

		// Prepare statement (throws exception if it fails)
		$stmt = mysqli_prepare($link, $sql);

		// Bind parameters
		mysqli_stmt_bind_param($stmt, "iss", $user_id, $selector, $hashedToken);

		// Execute statement (throws exception if it fails)
		mysqli_stmt_execute($stmt);

		// Close statement
		mysqli_stmt_close($stmt);

		// Build cookie value
		$cookieValue = $selector . ':' . $token;

		// Set cookie
		$result = setcookie(
			"remember_token",
			$cookieValue,
			time() + (60 * 60 * 24 * 364),
			"/",
			"",
			true,
			true
		);

		// Explicitly check cookie result
		if ($result === false)
		{
			throw new Exception("Failed to set remember_token cookie.");
		}
	}

	// Rotates stored persistent login token
	function executePersistentTokenRotation(mysqli $link, int $user_id, string $selector, string $token): void {

		$oldTokenHash = hash('sha256', $token);

		// Start transaction
		mysqli_begin_transaction($link);
		try
		{
			// Generate new selector and token
			$newToken = bin2hex(random_bytes(32));
			$newTokenHash = hash('sha256', $newToken);

			// Update database
			$sql = "UPDATE persistent_logins
					SET token_hash = ?, expires_at = NOW() + INTERVAL 1 YEAR
					WHERE user_id = ? AND selector = ? AND token_hash = ?";

			$stmt = mysqli_prepare($link, $sql);
			mysqli_stmt_bind_param(
				$stmt,
				"siss",
				$newTokenHash,
				$user_id,
				$selector,
				$oldTokenHash
			);

			mysqli_stmt_execute($stmt);
			if (mysqli_stmt_affected_rows($stmt) !== 1)
			{
				mysqli_stmt_close($stmt);
				throw new RuntimeException('Token rotation failed: row not found');
			}
			mysqli_stmt_close($stmt);

			// Commit DB changes
			mysqli_commit($link);
		}
		catch (Exception $e)
		{
			// Roll back DB to previous valid state
			mysqli_rollback($link);
			throw $e;
		}

		// Only now touch the cookie
		$cookieValue = $selector . ':' . $newToken;
		$result = setcookie(
			"remember_token",
			$cookieValue,
			time() + (60 * 60 * 24 * 364),
			"/",
			"",
			true,
			true
		);
		if ($result === false)
		{
			// DB is already correct; force re-login later
			throw new RuntimeException('Failed to set rotated remember_token cookie');
		}
	}







	/* PASSWORD CHANGE - M.RESETPASS.PHP */
	// Should password change be allowed
	function allowPasswordChange(mysqli $link, string $token): array {

		$hashed_token = hash('sha256', $token);

		$sql = "SELECT email
				FROM password_resets
				WHERE token = ?
				AND expires_at >= NOW()
				LIMIT 1";

		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		if(!mysqli_stmt_bind_param($stmt, "s", $hashed_token))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
		
		// Bind result
		mysqli_stmt_bind_result($stmt, $email);
		$has_row = mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		// Return array with output flag and email
		return [
			'output' => $has_row !== null && $has_row !== false && $email !== null,
			'email' => $email ?? null
		];
	}

	// Remove reset record from the DB
	function removeResetRecord(mysqli $link, string $email) {
		
		$sql = "DELETE FROM password_resets WHERE email = ?";

		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		if (!mysqli_stmt_bind_param($stmt, "s", $email)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}

		mysqli_stmt_close($stmt);
	}





	/* ACCOUNT VERIFICATION - M.VERIFY.PHP */
	// Should activation be allowed
	function isActivationAllowed(mysqli $link, string $token): array {
		
		// Hash the token as stored in the DB
		$hashed_token = hash('sha256', $token);
		
		$sql = "SELECT email
				FROM accounts
				WHERE verification_token = ?
				LIMIT 1";

		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt)
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}

		if (!mysqli_stmt_bind_param($stmt, "s", $hashed_token)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}

		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}

		// Bind result
		mysqli_stmt_bind_result($stmt, $email);
		$has_row = mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		// Return array with output flag and email
		return [
			'output' => $has_row !== null && $has_row !== false && $email !== null,
			'email' => $email ?? null
		];
	}

	// Activate an account, remove activation token
	function activateAccount(mysqli $link, string $token): void {

		// Hash the token as stored in the DB
		$hashed_token = hash('sha256', $token);

		// Prepare an SQL statement
		$sql = "UPDATE accounts
				SET verification_token = NULL, is_active = 1
				WHERE verification_token = ?";

		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt)
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}

		// Bind variables to the prepared statement as parameters
		if (!mysqli_stmt_bind_param($stmt, "s", $hashed_token)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}

		// Attempt to execute the prepared statement
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}

		// Optional: check if any row was actually updated
		if (mysqli_stmt_affected_rows($stmt) === 0)
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('No account was activated. Token may be invalid or already used.');
		}

		mysqli_stmt_close($stmt);
	}





	/* SERVICE FUNCTIONS - MOST PAGES */
	// Get the article link to return to after logging in.
	function getRedirectLink(): string {

		$base = rtrim(BASE_URL, '/') . '/m.index.php';
		$params = [];

		//if we have the domain parameter set, it can only contain letters
		if (isset($_GET['domain']))
		{
			if(!preg_match('/^[a-zA-Z]+$/', $_GET['domain']))
			{
				throw new RuntimeException('Invalid characters in "domain" parameter');
			}
			$params['domain'] = $_GET['domain'];
		}
		
		//if we have the article parameter set, it can only contain letters, numbers and dot - '/^[a-zA-Z0-9.]+$/'.
		//in this version regex, it also cannot start with a dot or have 2 or more dot clusters
		if (isset($_GET['article']))
		{
			if(!preg_match('/^[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)*$/', $_GET['article']))
			{
				throw new RuntimeException('Invalid characters in "article" parameter');
			}
			$params['article'] = $_GET['article'];
		}
		
		//build
		if (!empty($params)) 
		{
			$base .= '?' . http_build_query($params);
		}

		return $base;
	}

	// Start a session with a hardened cookie. Must call before any output.
	function startSecureSession(): void {

		if (session_status() === PHP_SESSION_NONE) 
		{
			// Detect HTTPS properly
			$isHttps = (
				(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
				$_SERVER['SERVER_PORT'] == 443
			);
			
			// If we don't allow HTTP sessions
			if ($isHttps == false && ALLOW_SESSION_OVER_HTTP == false)
			{
				throw new RuntimeException('HTTPS is required for secure sessions.');
			}

			// Set secure cookie rules
			session_set_cookie_params([
				'lifetime' => 0,
				'path'     => '/',
				'secure'   => $isHttps,
				'httponly' => true,
				'samesite' => 'Strict'
			]);

			// Start the session
			if (!session_start()) 
			{
				throw new RuntimeException('Failed to start a session.');
			}
			
			// Generate CSRF token if not present
			if (empty($_SESSION['csrf_token'])) 
			{
				$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
			}
		}
	}

	// Do we have a logged in session or a useless empty one
	function isSessionEmpty(): bool {
		
		if(!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
		{
			return false;
		} 
		else 
		{
			return true;
		}
	}

	// Open a connection to the DB
	function connectDb(): mysqli {

		// singleton object
		static $connection = null;

		if ($connection === null) 
		{
			//create connection
			$connection = new mysqli(
				DB_SERVER_PUBLIC,
				DB_USERNAME_PUBLIC,
				DB_PASSWORD_PUBLIC,
				DB_NAME_PUBLIC
			);
			
			//handle error
			if ($connection->connect_error) 
			{
				throw new RuntimeException('Database connection failed: ' . $connection->connect_error);
			}
		}

		//return
		return $connection;
	}

	// Default Exception handler
	function catchEx(Throwable $ex): void {

		error_log($ex);
		http_response_code(500);
		if (!IS_PRODUCTION)
		{
			header('Content-Type: text/html; charset=utf-8');
			echo "<pre>" . htmlspecialchars((string)$ex) . "</pre>";
		}
		else
		{
			echo "An internal error occurred.";
		}
		exit;
	}