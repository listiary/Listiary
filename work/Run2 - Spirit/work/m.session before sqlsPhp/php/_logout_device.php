<?php

	// Presets
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_config.php";
	require_once __DIR__ . "/_sessionlib.php";
	set_exception_handler('catchEx');
	
	// More Presets
	//startSecureSession();
	$link = connectDb();
	

	// Logout this user on this browser
	function logout_all(mysqli $link) {
		
		// Delete session data on server
		session_start();
		$user_id = $_SESSION['id'] ?? null;
		$_SESSION = array();
		session_destroy();

		// Remove session cookie on client
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(), // typically PHPSESSID
				'',
				time() - 42000, // expire in the past
				$params["path"],
				$params["domain"],
				$params["secure"],
				$params["httponly"]
			);
		}
		
		if($user_id && $user_id > 0)
		{
			invalidateAllRememberTokens($link, $user_id);
		}
		
		// Redirect to login page
		//header("location: login.php");
	}
	
	// Logout all users on all devices
	function logout_device(mysqli $link) {

		// Delete session data on server
		session_start();
		$_SESSION = array();
		session_destroy();

		// Remove session cookie on client
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(), // typically PHPSESSID
				'',
				time() - 42000, // expire in the past
				$params["path"],
				$params["domain"],
				$params["secure"],
				$params["httponly"]
			);
		}

		// remove persistent login token if cookie exists
		if (isset($_COOKIE['remember_token']))
		{
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
			
			invalidateRememberToken($link, $selector);
		}
	}
	
	
	
	// Perform log out
	logout_device($link);
	
	// Redirect to login page
	header("location: https://development.listiary.org/m.index.php");
	exit;