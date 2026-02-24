<?php

	//https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php
	// TODO: WE NEED:
	// session_regenerate_id(true)
	// CSRF protection on forms
	// Rate limiting login attempts
	// HTTPS-only session cookies
	// Logout script that destroys session properly

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	set_exception_handler('catchEx');
	
	// More Presets
	startSecureSession();
	$link = connectDb();

	//try to get redirect params
	$redirecturl = getRedirectLink();

	// If we logged in - redirect
	// Else, try to log in from a remember_me cookie
	// if successful - redirect, if not load the page further
	// And if we are using POST, we will proceed to try and execute a login
	if (!isSessionEmpty()) 
	{
		header("Location: " . $redirecturl);
		exit;
	}
	else
	{
		$result = doRememberedLogin($link);
		if($result === true)
		{			
			//redirect
			header("Location: " . $redirecturl);
			exit;
		}
	}
	
	

	// Define variables and initialize with empty values
	$username = $password = "";
	$username_err = $password_err = $login_err = "";
 
	// Processing form data when form is submitted
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Check if username is empty
		if(empty(trim($_POST["username"])))
		{
			$username_err = "Please enter username.";
		} 
		else
		{
			$username = trim($_POST["username"]);
		}
		
		// Check if password is empty
		if(empty(trim($_POST["password"])))
		{
			$password_err = "Please enter your password.";
		} 
		else
		{
			$password = trim($_POST["password"]);
		}
		
		// Validate credentials
		if(empty($username_err) && empty($password_err))
		{
			// Verify password and username
			$user = fetchUserCredentials($link, $username);
			if ($user === null || !password_verify($password, $user['password_hash']))
			{
				$login_err = "Invalid username or password.";
				return;
			}

			// Success - login
			if (!empty($_POST['remember_me'])) 
			{
				executePersistentLogin($link, $user['id']);
			}
			//var_dump($id);
			restoreUserSession($link, $user['id']);
			header("Location: " . $redirecturl);
			exit;
		}
	}
	
	// Retrieve 'id' and 'password_hash' for a user provided username from DB
	// Return NULL if no such username in our DB
	function fetchUserCredentials(mysqli $link, string $username): ?array {

		$sql = "SELECT id, password_hash FROM accounts WHERE username = ?";
		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt) 
		{
			throw new RuntimeException('Database prepare failed.');
		}
		mysqli_stmt_bind_param($stmt, "s", $username);
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




	// Log in with a remember me token and the database.
	function doRememberedLogin($link): bool {

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
	function executePersistentLogin(mysqli $link, int $user_id) {
		
		// Generate selector (public) and token (secret)
		$token = bin2hex(random_bytes(32));
		$hashedToken = hash('sha256', $token);
		$selector = bin2hex(random_bytes(8));
		
		// Store in database, let SQL compute expiration (1 year from now)
		$sql = "INSERT INTO persistent_logins (user_id, selector, token_hash, expires_at) 
            VALUES (?, ?, ?, NOW() + INTERVAL 1 YEAR)";
		
		if ($stmt = mysqli_prepare($link, $sql))
		{
			mysqli_stmt_bind_param($stmt, "iss", $user_id, $selector, $hashedToken);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		} 
		else
		{
			die("DB Error: " . mysqli_error($link));
		}

		// Set the cookie
		$cookieValue = $selector . ':' . $token;
		setcookie(
			"remember_token",
			$cookieValue,
			time() + (60*60*24*364),	//tiny safety buffer between the client and the DB to avoid 'just expired' edge cases caused by timezone differences.
			"/",
			"",
			true,
			true
		);
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

	// Do we have a logged in session or a useless empty one.
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

	// Open a connection to the DB.
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

	// Default Exception handler.
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
    <!-- CRITICAL FOR MOBILE: This line makes it scale correctly on phones -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <style>
        /* Modern Reset */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: #1c1e21;
        }

        .wrapper {
            background-color: white;
            width: 100%;
            max-width: 400px; /* Limits width on desktop */
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 24px;
            font-size: 28px;
            color: #1877f2;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #606770;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px;
            font-size: 16px; /* 16px prevents iOS zoom on focus */
            border: 1px solid #dddfe2;
            border-radius: 8px;
            transition: border-color 0.2s;
            background-color: #f5f6f7;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #1877f2;
            outline: none;
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(24, 119, 242, 0.2);
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff8f8 !important;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 60px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-primary {
            background-color: #1877f2;
            color: white;
        }

        .btn-secondary {
            background-color: #e4e6eb;
            color: #4b4f56;
        }

        .btn:hover {
            opacity: 0.9;
        }

        p {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #606770;
        }

        a {
            color: #1877f2;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }
		
		.remember-me {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-top: 8px;
			margin-bottom: 10px;
		}

		.remember-me input[type="checkbox"] {
			width: 18px;
			height: 18px;
			accent-color: #1877f2;
			cursor: pointer;
		}

		.remember-me label {
			margin: 0;
			font-size: 14px;
			font-weight: 600;
			color: #606770;
			cursor: pointer;
		}
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign In</h2>
		<br />
		<span><?php if(!empty($login_err)){ echo '<div class="alert alert-danger">' . $login_err . '</div>';} ?></span>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            
			<div class="form-group">
                <label>Email address or Username</label>
                <input type="text" name="username" 
                       class="<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $username; ?>"
                       placeholder="email or username">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" 
                       class="<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $password; ?>"
                       placeholder="password">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
			<div class="remember-me">
				<input type="checkbox" name="remember_me" id="remember_me"
					<?php echo (!empty($_POST['remember_me'])) ? 'checked' : ''; ?>>
				<label for="remember_me">Keep me logged in (for up to a year)</label>
			</div>

            <div class="btn-container">
                <input type="submit" class="btn btn-primary" value="Login">
                <input type="reset" class="btn btn-secondary" value="Reset">
            </div>
			

            <p style="text-align: center;">Don't have an account? <a href="m.register.php">Register here</a>.
			<br/><a href="m.forgotpass.php">Forgot password</a></p>
        </form>
    </div>
</body>
</html>