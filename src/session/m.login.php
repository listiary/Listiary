<?php

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_sessionlib.php";
	require_once __DIR__ . "/php/_ratelimiters.php";
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
		// Verify CSRF token
		if(isCsrfTokenValid() == false)
		{
			http_response_code(403);
			throw new RuntimeException('Invalid CSRF token.');
		}
		
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
		
		// Check if too many incorrect attempts have been made
		$email = $_POST["username"];
		if(isIpBlocked($link) || isEmailBlocked($link, $email))
		{
			$login_err = "Too many unsuccessful login attempts have been made. Wait a while.";
			$username_err = "Too many unsuccessful login attempts have been made. Wait a while.";
			$password_err = "Too many unsuccessful login attempts have been made. Wait a while.";
		}
		
		// Validate credentials
		if(empty($username_err) && empty($password_err))
		{
			// Verify password and username
			$user = fetchUserCredentials($link, $_POST["username"]);

			if ($user === null || !password_verify($password, $user['password_hash']))
			{
				$login_err = "Invalid username or password.";
				$password_err = "Invalid username or password.";
				$username_err = "Invalid username or password.";
				recordFailedLoginAttempt($link, $email);
			}
			else
			{
				// Success - login
				cleanupLoginAttempts($link, $email);
				if (!empty($_POST['remember_me'])) 
				{
					executePersistentLogin($link, $user['id']);
				}
				restoreUserSession($link, $user['id']);
				header("Location: " . $redirecturl);
				exit;
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- CRITICAL FOR MOBILE: This line makes it scale correctly on phones -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
	<link rel="stylesheet" href="css/m.login.css">
</head>
<body>
    <div class="wrapper">
        <h2>Sign In</h2>
		<br />
		<span><?php if(!empty($login_err)){ echo '<div style="color: red; font-style: italic;">' . $login_err . '</div><br>';} ?></span>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
			<div class="form-group">
                <label>Email address</label>
                <input type="text" name="username" 
                       class="<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $username; ?>"
                       placeholder="email address">
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