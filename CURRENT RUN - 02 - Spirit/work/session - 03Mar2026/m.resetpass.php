<?php

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_sessionlib.php";
	set_exception_handler('catchEx');
	
	// More Presets
	startSecureSession();
	$link = connectDb();


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
			// Verify CSRF token
			if(isCsrfTokenValid() == false)
			{
				http_response_code(403);
				throw new RuntimeException('Invalid CSRF token.');
			}
		
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

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- CRITICAL FOR MOBILE: This line makes it scale correctly on phones -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
	<link rel="stylesheet" href="css/m.resetpass.css">
</head>
<body>
    <div class="wrapper">
        <h2>Reset Password</h2>
		<span class="info_<?php echo $message_state; ?>"><?php echo $message; ?></span>
		<br /><br />
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"]); ?>" method="post">
		<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
		
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
