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

	// Include secret key for usercode generation
	require_once __DIR__ . "/php/_secret.php";
	$secret_key = SECRET_KEY_1;

    // Define variables and initialize with empty values
    $username = "";
	$email = "";
    $password = "";
    $confirm_password = "";
    $username_err = "";
	$email_err = "";
    $password_err = "";
    $confirm_password_err = "";

    // Processing form data when form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
		// Verify CSRF token
		if(isCsrfTokenValid() == false)
		{
			http_response_code(403);
			throw new RuntimeException('Invalid CSRF token.');
		}
		
        // Validate username
        if(empty(trim($_POST["username"])))
        {
            $username_err = "Please enter a username.";
        }
        elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"])))
        {
            $username_err = "Username can only contain letters, numbers, and underscores.";
        }
		elseif(!preg_match('/^[a-zA-Z0-9_]{3,35}$/', $_POST["username"])) 
		{
			$username_err = "Username can only contain letters, numbers, and underscores and be 3 to 35 symbols long.";
		}

		// Validate email
		if(empty(trim($_POST["email"])))
        {
            $email_err = "Please enter an email address.";
        }
		else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) 
		{
			$email_err = "Please enter a valid email address.";
		}

        // Validate password
        if(empty(trim($_POST["password"])))
        {
            $password_err = "Please enter a password.";
        }
        elseif(strlen(trim($_POST["password"])) < 12)
        {
            $password_err = "Password must have at least 12 characters.";
        }
        else
        {
            $password = trim($_POST["password"]);
        }
        
        // Validate confirm password
        if(empty(trim($_POST["confirm_password"])))
        {
            $confirm_password_err = "Please confirm password.";
        }
        else
        {
            $confirm_password = trim($_POST["confirm_password"]);
            if(empty($password_err) && ($password != $confirm_password))
            {
                $confirm_password_err = "Password did not match.";
            }
        }
		
		
		//log account creation rate-limiting event
		//users should be limited at how many emails they might try
		//but it should be a bigger number than the allowed successful registrations
		//but for now, we won't be limiting that
		
		// Check if username taken
		$isUsernameTaken = isUsernameTaken($link, $username);
		if($isUsernameTaken == false) $username = trim($_POST["username"]);
		else $username_err = "This username is already taken.";
		
		// Check if email taken
		$isEmailTaken = isEmailTaken($link, $email);
		if($isEmailTaken == false) $email = trim($_POST["email"]);
		else $email_err = "This email is already taken.";
		
		// Check if too many accounts registrations on this IP
		if(isIpBlockedForRegister($link))
		{
			$username_err = "Your IP adress has been blocked for too many registrations.";
			$email_err = "Your IP adress has been blocked for too many registrations.";
			$password_err = "Your IP adress has been blocked for too many registrations.";
			$confirm_password_err = "Your IP adress has been blocked for too many registrations.";
		}

        // Check input errors before inserting in database
        if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err))
        {
			createAccount($link, $username, $email, $password, 0);
			
			//log account creation rate-limiting event
			recordRegistrationSuccess($link, $email);
			
			// send verification link
			$res = sendVerificationEmail($link, $username, $email, $raw_token);
			if($res)
			{
				// Redirect to login page
				mysqli_stmt_close($stmt);
				header("location: m.regsuccess.php");
			}
			else
			{
				mysqli_stmt_close($stmt);
				throw new RuntimeException('Oops. Something went wrong.');
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
    <title>Sign Up</title>
	<link rel="stylesheet" href="css/m.register.css">
</head>
<body>
    <div class="wrapper">
        <h2>Create Account</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" 
                       class="<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $username; ?>"
                       placeholder="Choose a username">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
			
			<div class="form-group">
                <label>Email address</label>
                <input type="text" name="email" 
                       class="<?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $email; ?>"
                       placeholder="Enter your email address">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            
            <div class="form-group">
                <label>Password (minimum 12 characters)</label>
                <input type="password" name="password" 
                       class="<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $password; ?>"
                       placeholder="Enter a password">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" 
                       class="<?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $confirm_password; ?>"
                       placeholder="Enter the password again">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>

            <div class="btn-container">
                <input type="submit" class="btn btn-primary" value="Sign Up">
                <input type="reset" class="btn btn-secondary" value="Reset">
            </div>

            <p>Already have an account? <a href="m.login.php">Login here</a>.</p>
        </form>
    </div>
</body>
</html>