<?php
    // Include config
    require_once __DIR__ . "/php/_config.php";
	
	//read config
	$servername = ""; 
	$username = ""; 
	$password = ""; 
	$dbname = "";
	setDbConstants("public");
	
	// Include secret key for usercode generation
	require_once __DIR__ . "/php/_secret.php";
	$secret_key = SECRET_KEY_1;

	//create connection
	$link = createConnection();

    // Check connection
    if(!$link) die("Connection failed: " . mysqli_connect_error());

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
	
	
	// Read Database connection constants from the config into our global variables.
	function setDbConstants($domain) {

		global $servername, $username, $password, $dbname;
		
		if($domain == "public")
		{
			$servername = DB_SERVER_PUBLIC;
			$username = DB_USERNAME_PUBLIC;
			$password = DB_PASSWORD_PUBLIC;
			$dbname = DB_NAME_PUBLIC;
		}
		else if($domain == "personal")
		{
			$servername = DB_SERVER_PERSONAL;
			$username = DB_USERNAME_PERSONAL;
			$password = DB_PASSWORD_PERSONAL;
			$dbname = DB_NAME_PERSONAL;
		}
		else if($domain == "private")
		{
			$servername = DB_SERVER_PRIVATE;
			$username = DB_USERNAME_PRIVATE;
			$password = DB_PASSWORD_PRIVATE;
			$dbname = DB_NAME_PRIVATE;
		}
		else if($domain == "normative")
		{
			$servername = DB_SERVER_DOCUMENTATION;
			$username = DB_USERNAME_DOCUMENTATION;
			$password = DB_PASSWORD_DOCUMENTATION;
			$dbname = DB_NAME_DOCUMENTATION;
		}
		else
		{
			die("Connection failed: Unknown value for url parameter domain - '" . $domain . "'");
		}
	}

	// Create a connection to the database
	function createConnection() {

		global $servername, $username, $password, $dbname;

		$connection = mysqli_connect($servername, $username, $password, $dbname);
		if(!$connection) die("Connection failed: " . mysqli_connect_error());
		//echo "Created connection to '" . $servername . "'<br />";

		return $connection;
	}
	
	// Send activation link
	function sendVerificationEmail($link, $username, $email, $raw_token) {
		
		// Construct the verification URL
		// We send the RAW token in the URL, not the hashed one!
		// We also pass the email in the URL to make looking up the database row easier later.
		$verify_url = "https://development.listiary.org/session/m.verify.php?token=" . $raw_token . "&email=" . urlencode($email);

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
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- CRITICAL FOR MOBILE: This line makes it scale correctly on phones -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Create Account</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            
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