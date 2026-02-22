<?php

	// Initialize the session
	session_start();
 
	//include config
	require_once __DIR__ . "/php/_config.php";
	
	//read config
	$servername = ""; 
	$username = ""; 
	$password = ""; 
	$dbname = "";
	setDbConstants("public");

	//create connection
	$link = createConnection();

	// Check connection
	if(!$link) die("Connection failed: " . mysqli_connect_error());
	//echo "Created connection to '" . $servername . "'<br />";
 
	// Define variables and initialize with empty values
	$email = "";
	$email_err = "";
	$message = "Enter the email address associated with the account and we will send you a password reset link.";
	$message_state = "info";
	$button_text = "Send Email";
 
	// Processing form data when form is submitted
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Validate email
		if(empty(trim($_POST["email"])))
		{
			$email_err = "Please enter a valid email address.";     
		}
		else
		{
			$email = trim($_POST["email"]);
			
			// Check account with that email exists in the database
			$sql = "SELECT EXISTS(SELECT 1 FROM accounts WHERE email = ?) AS account_exists;";
			if($stmt = mysqli_prepare($link, $sql))
			{
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "s", $email);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt))
				{
					// Get the result set object
					$result = mysqli_stmt_get_result($stmt);
					
					// Fetch the row as an associative array
					if($row = mysqli_fetch_assoc($result))
					{
						//var_dump($row); // This will print the database response to the top of your screen
						//die(); // Stop the script so you can read the dump

						// Check the alias 'account_exists' defined in your SQL
						if ($row['account_exists'] == 0)
						{
							$email_err = "There is no account associated to that email address.";
						}
						else
						{
							if(checkIfTooSoonToResend($link, $email))
							{
								$message = "A password reset link has already been requested in the last 5 minutes.<br>Please wait some more before requesting a new link.";
								$message_state = "error";
								$button_text = "Retry";
							}
							else
							{
								$status = sendPasswordResetEmail_Gemini($link, $email);
								if($status == true) 
								{
									$message = "A password reset link has been sent to your email address with further instructions.<br>Please check your inbox and spam folders.<br>If you don’t see the email, allow a few minutes for delivery before requesting another link.";
									$message_state = "success";
									$button_text = "Resend Email";
								}
								else 
								{
									$message = "Oops! Something went wrong. Please try again later.";
									$message_state = "error";
									$button_text = "Retry";
								}
							}
						}
					}
				}
				else
				{
					echo "Oops! Something went wrong. Please try again later.";
				}
				
				// Close statement
				mysqli_stmt_close($stmt);
			}
			else 
			{
				die("SQL Error: " . mysqli_error($link));
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
	
	// Reset password process
	function sendPasswordResetEmail($conn, $email) {
		
		//generate a token
		//$token = bin2hex(random_bytes(32));
		//$url = "https://yourdomain.com/password-reset.php?token=" . $token . "&email=" . urlencode($email);

		//store it in the db
		//"INSERT INTO password_resets (email, token, expires_at) 
		//VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR));"
		
		//send an email
		//Hash the Token: Just like passwords, you should ideally store a hash of the token (hash('sha256', $token)) in the database. That way, if your database is leaked, the attacker can't use the tokens to take over accounts.

		//One-Time Use: Once the user successfully changes their password, your PHP code must delete that row from the password_resets table so the link can't be used again.

		//Rate Limiting: Don't let someone trigger 1,000 reset emails in a minute. PHP should check the last time an email was sent for that specific address before sending another.
		
		//return
		return true;
	}
	function sendPasswordResetEmail_Gemini($link, $email) {
    
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
		$reset_url = "https://development.listiary.org/session/m.resetpass.php?token=" . $raw_token . "&email=" . urlencode($email);

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
	function checkIfTooSoonToResend($conn, $email) {

		//should we add `created_at` field in the DB as well?
		$sql = "SELECT 1
				FROM password_resets
				WHERE email = ?
				AND expires_at > DATE_ADD(NOW(), INTERVAL 55 MINUTE)
				LIMIT 1";

		if (!$stmt = mysqli_prepare($conn, $sql))
		{
			die("mysqli_prepare failed!");
		}

		mysqli_stmt_bind_param($stmt, "s", $email);

		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			die("mysqli_stmt_execute failed!");
		}

		mysqli_stmt_store_result($stmt);
		$res = mysqli_stmt_num_rows($stmt);
		$tooSoon = $res > 0;
		
		mysqli_stmt_close($stmt);
		return $tooSoon;
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- CRITICAL FOR MOBILE: This line makes it scale correctly on phones -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
		
		.info_info {
			color: black;
		}
		.info_success {
			color: green;
			font-style: italic;
		}
		.info_error {
			color: red;
			font-style: italic;
		}
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Reset Password</h2>
		<span class="info_<?php echo $message_state; ?>"><?php echo $message; ?></span>
		<br /><br />
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		
			<div class="form-group">
                <label>Email address</label>
                <input type="text" name="email" 
                       class="<?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $email; ?>"
                       placeholder="email">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>

            <div class="btn-container">
                <input type="submit" class="btn btn-primary" value="<?php echo $button_text; ?>">
            </div>

            <p style="text-align: center;">Don't have an account? <a href="m.register.php">Register here</a>.
			<br/>Or <a href="m.login.php">Log In</a></p>
        </form>
    </div>
</body>
</html>
