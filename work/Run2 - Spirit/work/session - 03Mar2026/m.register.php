<?php

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_sessionlib.php";
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
		else if (!preg_match('/^[a-zA-Z0-9_]{3,35}$/', $_POST["username"])) 
		{
			$username_err = "Username can only contain letters, numbers, and underscores and be 3 to 35 symbols long.";
		}
        else
        {
            // Prepare a select statement
            $sql = "SELECT id FROM accounts WHERE username = ?";
            
            if($stmt = mysqli_prepare($link, $sql))
			{
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                
                // Set parameters
                $param_username = trim($_POST["username"]);
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt))
                {
                    /* store result */
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1)
                    {
                        $username_err = "This username is already taken.";
                    }
                    else
                    {
                        $username = trim($_POST["username"]);
                    }
                }
                else
                {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
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
		else
        {
			// Prepare a select statement
            $sql = "SELECT id FROM accounts WHERE email = ?";
            
            if($stmt = mysqli_prepare($link, $sql))
			{
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                
                // Set parameters
                $param_email = trim($_POST["email"]);
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt))
                {
                    /* store result */
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1)
                    {
                        $email_err = "This email is already registered with another account.";
                    }
                    else
                    {
                        $email = trim($_POST["email"]);
                    }
                }
                else
                {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
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

        // Check input errors before inserting in database
        if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err))
        {
            // Prepare an insert statement
            $sql = "INSERT INTO accounts (username, email, password_hash, usercode, is_bot, verification_token) VALUES (?, ?, ?, ?, ?, ?)";
            
            if($stmt = mysqli_prepare($link, $sql))
            {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ssssis", $param_username, $param_email, $param_password_hash, $param_usercode, $param_is_bot, $param_verification_token_hash);
                
                // Set parameters
                $param_username = $username;
				$param_email = $email;
                $param_password_hash = password_hash($password, PASSWORD_BCRYPT); // Creates a password hash
				$raw = hash_hmac('sha256', $param_username . $param_email, $secret_key, true);
				$param_usercode = substr(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='), 0, 16);
				$param_is_bot = 0;

				// verification token
				$raw_token = bin2hex(random_bytes(32));
				$hashed_token = hash('sha256', $raw_token);
				$param_verification_token_hash = $hashed_token;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt))
                {
					// send verification link
					$res = sendVerificationEmail($link, $username, $email, $raw_token);
					if($res)
					{
						// Redirect to login page
						header("location: m.regsuccess.php");
					}
					else
					{
						die("Sending email failed.");
					}
                }
                else
                {
					die("Execute failed: " . mysqli_error($link));
                    //echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
			else
			{
				die("Prepare failed: " . mysqli_error($link));
			}
        }
        
        // Close connection
        mysqli_close($link);
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