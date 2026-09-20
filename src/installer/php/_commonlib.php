<?php


	/* SERVICE FUNCTIONS - MOST PAGES */
	/**
	 * Start a session with a hardened cookie.
	 * Must call before any output.
	 * @return void
	 */
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

	/**
	 * Do we have a logged in session or a useless empty one
	 * @return bool True if we are logged in, otherwise false
	 */
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

	/**
	 * Check if CSRF token is valid and regenerate it if it is valid
	 * @return bool True if the token CSRF is valid, otherwise false
	 */
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
				// Log safely (no password exposure)
				error_log('Database connection failed: ' . $e->getMessage());

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
	