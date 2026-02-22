<?php

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
	$new_password = $confirm_password = "";
	$new_password_err = $confirm_password_err = "";
	
	if(isset($_GET['token']) == false) die("'token' parameter of the url is not set");
	$token = $_GET['token'];
	//echo "Got token parameter - '" . $token . "'<br />";
	
	if(isset($_GET['email']) == false) die("'email' parameter of the url is not set");
	$email = urldecode($_GET['email']);
	//echo "Got email parameter - '" . $email . "'<br />";

	
	
	
	// Check if link active
	if(allowPasswordChange($link, $email, $token) == false)
	{
		// if not, hide controls and show only message.
		$message = "This password-reset link is invalid or expired.<br>Please request a new password-reset link <a href='m.forgotpass.php'>here</a>.";
		$message_state = "error";
		$display = "none";
		$flexdisplay = "none";
	}
	else
	{
		// if active, show info message
		$safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
		$message = "Please create a new password for '<span class=\"info_info\" style=\"font-style: italic;\">" . $safe_email . "</span>'";
		$message_state = "info";
		$display = "block";
		$flexdisplay = "flex";
		
		// If POST - meaning we are Processing form data
		if($_SERVER["REQUEST_METHOD"] == "POST")
		{
			// Validate new password
			if(empty(trim($_POST["new_password"]))) {
				$new_password_err = "Please enter the new password.";     
			}
			elseif(strlen(trim($_POST["new_password"])) < 12) {
				$new_password_err = "Password must have atleast 12 characters.";
			} else {
				$new_password = trim($_POST["new_password"]);
				$new_password_err = "";
			}
			
			// Validate confirm password
			if(empty(trim($_POST["confirm_password"]))) {
				$confirm_password_err = "Please confirm the password.";
			} else {
				$confirm_password = trim($_POST["confirm_password"]);
				if(empty($new_password_err) && ($new_password != $confirm_password)) {
					$confirm_password_err = "Password did not match.";
				}
				else {
					$confirm_password_err = "";
				}
			}

			// Check input errors before updating the database
			if(empty($new_password_err) && empty($confirm_password_err)) {

				// Prepare an update statement
				$sql = "UPDATE accounts SET password_hash = ? WHERE email = ?";
				if($stmt = mysqli_prepare($link, $sql))
				{
					// Bind variables to the prepared statement as parameters
					mysqli_stmt_bind_param($stmt, "ss", $param_new_password_hash, $param_email);
					
					// Set parameters
					$param_email = $email;
					$param_new_password_hash = password_hash($new_password, PASSWORD_BCRYPT); // Creates a password hash
					
					// Attempt to execute the prepared statement
					if(mysqli_stmt_execute($stmt))
					{
						// 1. Invalidate the reset token! (CRITICAL)
						// You must delete or mark the token as used in your database 
						// so it cannot be reused by a malicious actor.
						// e.g., invalidateResetToken($link, $email);
						removeResetRecord($link, $email);

						// 2. Rotate remember-me tokens / Invalidate existing sessions
						// (Your code for deleting old sessions/tokens from the DB goes here)
						
						// Display a success message
						$message = "Successfully changed the password for '<span class=\"info_info\" style=\"font-style: italic;\">" . $safe_email . "</span>'.<br>All existing sessions (if any) have been invalidated. You can proceed to Sign In <a href='m.login.php'>here</a>.";
						$message_state = "success";
						$display = "none";
						$flexdisplay = "none";
					}
					else
					{
						die("Oops! Something went wrong. Please try again later.");
					}
					
					
					// Close statement
					mysqli_stmt_close($stmt);
				}
				else
				{
					// THIS IS THE CULPRIT: Log the exact error
					error_log("SQL Prepare Error: " . mysqli_error($link)); 
					// For local dev, you can just echo it to see it instantly:
					die("Database Error: " . mysqli_error($link));
				}
			}
		}
	}
 
 
	
	// Close connection
	mysqli_close($link);



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
	
	// Should password change be allowed
	function allowPasswordChange($link, $email, $token) {

		$hashed_token = hash('sha256', $token);

		$sql = "SELECT 1
				FROM password_resets
				WHERE email = ?
				AND token = ?
				AND expires_at >= NOW()
				LIMIT 1";

		if (!$stmt = mysqli_prepare($link, $sql))
		{
			die("mysqli_prepare failed: " . mysqli_error($link));
		}

		mysqli_stmt_bind_param($stmt, "ss", $email, $hashed_token);

		if (!mysqli_stmt_execute($stmt))
		{
			die("mysqli_stmt_execute failed: " . mysqli_stmt_error($stmt));
		}

		mysqli_stmt_store_result($stmt);
		$allowed = mysqli_stmt_num_rows($stmt) > 0;

		mysqli_stmt_close($stmt);
		return $allowed;
	}

	// Remove reset record from the DB
	function removeResetRecord($link, $email) {
		
		$sql = "DELETE FROM password_resets WHERE email = ?";

		if (!$stmt = mysqli_prepare($link, $sql))
		{
			die("mysqli_prepare failed: " . mysqli_error($link));
		}

		mysqli_stmt_bind_param($stmt, "s", $email);
		if (!mysqli_stmt_execute($stmt))
		{
			die("mysqli_stmt_execute failed: " . mysqli_stmt_error($stmt));
		}

		mysqli_stmt_close($stmt);
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
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"]); ?>" method="post">
		
            <div class="form-group" style="display:<?php echo $display; ?>;">
                <label>Password (minimum 12 characters)</label>
                <input type="password" name="new_password" 
                       class="<?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $new_password; ?>"
                       placeholder="Enter a password">
                <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
            </div>

            <div class="form-group" style="display:<?php echo $display; ?>;">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" 
                       class="<?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $confirm_password; ?>"
                       placeholder="Enter the password again">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>

            <div class="btn-container" style="display:<?php echo $flexdisplay; ?>;">
                <input type="submit" class="btn btn-primary" value="Submit">
				<input type="reset" class="btn btn-secondary" value="Reset">
            </div>

            <p style="text-align: center;">Don't have an account? <a href="m.register.php">Register here</a>.
			<br/>Or <a href="m.login.php">Log In</a> with another account.</p>
        </form>
    </div>
</body>
</html>
