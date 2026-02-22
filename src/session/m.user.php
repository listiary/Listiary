<?php

	// Presets
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
		$result = isset($_COOKIE['remember_token']);
		if($result === true) $result = doRememberedLogin($link);
		if($result === false)
		{
			//redirect
			header("Location: " . rtrim(BASE_URL, '/') . '/' . "session/m.login.php");
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

	// Log in with a remember me token and the database.
	function doRememberedLogin($link): bool {
		
		$cookie = $_COOKIE['remember_token'];
		if (strpos($cookie, ':') !== false) 
		{
			list($selector, $validator) = explode(':', $cookie, 2);

			// Look up selector in DB
			$sql = "SELECT user_id, token_hash, expires_at FROM persistent_logins WHERE selector = ? LIMIT 1";
			if ($stmt = mysqli_prepare($link, $sql)) 
			{
				mysqli_stmt_bind_param($stmt, "s", $selector);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_bind_result($stmt, $user_id, $token_hash, $expires_at);
				if (mysqli_stmt_fetch($stmt)) 
				{
					// Check expiration
					if (new DateTime() <= new DateTime($expires_at) &&
						hash_equals($token_hash, hash('sha256', $validator))) 
					{

						// Validator matches and token not expired → restore session
						session_regenerate_id(true);
						$_SESSION['loggedin'] = true;
						$_SESSION['id'] = $user_id;

						// Optionally fetch username from accounts table
						$usernameSql = "SELECT username, email, usercode, is_bot, is_active, created_at FROM accounts WHERE id = ?";
						if ($userStmt = mysqli_prepare($link, $usernameSql)) 
						{
							mysqli_stmt_bind_param($userStmt, "i", $user_id);
							mysqli_stmt_execute($userStmt);
							mysqli_stmt_bind_result($userStmt, $username, $email, $usercode, $is_bot, $is_active, $created_at);
							if (mysqli_stmt_fetch($userStmt))
							{
								// populate session
								$_SESSION['username'] = $username;
								$_SESSION['email'] = $email;
								$_SESSION['usercode'] = $usercode;
								$_SESSION['is_bot'] = $is_bot;
								$_SESSION['is_active'] = $is_active;
								$_SESSION['created_at'] = $created_at;
							}
							else
							{
								mysqli_stmt_close($userStmt);
								throw new RuntimeException('Invalid remember_token cookie');
							}
							mysqli_stmt_close($userStmt);
						}

						// Rotate persistent token (new validator + update DB + reset cookie)
						executePersistentLogin($link, $user_id);
						return true;
					} 
					else 
					{
						// Invalid or expired token → delete from DB & remove cookie
						$delSql = "DELETE FROM persistent_logins WHERE selector = ?";
						$delStmt = mysqli_prepare($link, $delSql);
						mysqli_stmt_bind_param($delStmt, "s", $selector);
						mysqli_stmt_execute($delStmt);
						mysqli_stmt_close($delStmt);
						
						// Expire the cookie
						setcookie('remember_token', '', time() - 3600, '/', '', true, true);
						return false;
					}
				}
				else
				{
					throw new RuntimeException('Invalid remember_token cookie');
				}
				mysqli_stmt_close($stmt);
			}
			else
			{
				throw new RuntimeException('Invalid remember_token cookie');
			}
		}
		else
		{
			throw new RuntimeException('Invalid remember_token cookie');
		}
	}
	
	// Stores persistent login token to the database
	function executePersistentLogin($link, $user_id) {
		
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

	// Do we have a logged in session or a useless empty one
	function isSessionEmpty(): bool {
		
		if(!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
		{
			return true;
		} 
		else 
		{
			return false;
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
	function catchEx(Throwable $ex) {

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