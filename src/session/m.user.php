<?php

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	set_exception_handler('catchEx');
	
	// More Presets
	startSecureSession();
	$link = connectDb();
	
	// If session empty look for the long login cookie.
	// If available, try to log in with that.
	// If not, we have no business being on the user page, so we redirect to the login page.
	if(isSessionEmpty())
	{
		$result = doRememberedLogin($link);
		if($result === false)
		{			
			//redirect
			header("Location: " . "m.login.php");
			exit;
		}
	}
	
	// Get data from session into variables that will be showed on our page
	$user_id = $_SESSION['id'];
	$username = $_SESSION['username'];
	$email = $_SESSION['email'];
	$usercode = $_SESSION['usercode'];
	$is_bot = $_SESSION['is_bot'];
	$is_active = $_SESSION['is_active'];
	$created_at = $_SESSION['created_at'];




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
		$userSql = "SELECT username, email, usercode, is_bot, is_active, created_at 
					FROM accounts 
					WHERE id = ? LIMIT 1";
		$userStmt = mysqli_prepare($link, $userSql);
		if (!$userStmt) {
			throw new RuntimeException('DB prepare failed for user info');
		}

		mysqli_stmt_bind_param($userStmt, "i", $user_id);
		mysqli_stmt_execute($userStmt);
		mysqli_stmt_bind_result(
			$userStmt,
			$username,
			$email,
			$usercode,
			$is_bot,
			$is_active,
			$created_at
		);
		$userExists = mysqli_stmt_fetch($userStmt);
		mysqli_stmt_close($userStmt);

		if (!$userExists)
		{
			throw new RuntimeException('User not found for session restoration');
		}

		// Populate session
		$_SESSION['username'] = $username;
		$_SESSION['email'] = $email;
		$_SESSION['usercode'] = $usercode;
		$_SESSION['is_bot'] = $is_bot;
		$_SESSION['is_active'] = $is_active;
		$_SESSION['created_at'] = $created_at;
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile – <?php echo htmlspecialchars($username); ?></title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #fff; /* white canvas for entire page */
        margin: 0;
        padding: 2rem;
		padding-top: 5rem;
        color: #333;
    }
    .profile-container {
        max-width: 480px;
        margin: 0 auto;
        text-align: center;
    }
    .avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background-color: #ddd;
        display: inline-block;
        margin-bottom: 1rem;
        object-fit: cover;
    }
    h1 { margin: 0.5rem 0; font-size: 1.8rem; }
    p { color: #555; margin: 0.3rem 0; font-size: 1rem; }
    .bio { margin-top: 1rem; font-size: 0.95rem; color: #666; }
</style>
</head>
<body>

<div class="profile-container">
    <!-- Avatar -->
	<img src="avatars/snail.jpg" alt="Avatar" class="avatar">

    <!-- Username -->
    <h1><?php echo htmlspecialchars($username); ?></h1>

    <!-- Basic info -->
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p><strong>Joined:</strong> <?php echo htmlspecialchars(date("F j, Y", strtotime($created_at))); ?></p>
    <p><strong>Status:</strong> <?php echo $is_active ? 'Active' : 'Inactive'; ?><?php echo $is_bot ? ' (Bot)' : ''; ?></p>

    <!-- Basic bio -->
    <div class="bio">
        This is your profile. You can update your avatar and bio here in the future.
    </div>
</div>

</body>
</html>