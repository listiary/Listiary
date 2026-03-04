<?php

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_sessionlib.php";
	set_exception_handler('catchEx');
	
	// More Presets
	//startSecureSession();
	$link = connectDb();
	
	try
	{
		// Define variables and initialize with empty values
		$email = "";
		$email_err = "";
		$message = "Enter the email address associated with the account and we will send you a password reset link.";
		$message_state = "info";
		$button_text = "Send Email";
		
		// Processing form data when form is submitted
		if($_SERVER["REQUEST_METHOD"] == "POST")
		{
			// Verify CSRF token
			if(isCsrfTokenValid() == false)
			{
				http_response_code(403);
				throw new RuntimeException('Invalid CSRF token.');
			}
			
			// Check if email is empty
			if(empty(trim($_POST["email"])))
			{
				$email_err = "Please enter a valid email address."; 
			}
			else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) 
			{
				$email_err = "Please enter a valid email address.";
			}
			else
			{
				$email = trim($_POST["email"]);
			}
			
			// rate limit resending
			if(checkIfTooSoonToResend($link, $email)) //TODO: limit by IP as well
			{
				$message = "A password reset link has already been requested in the last 5 minutes.<br>Please wait some more before requesting a new link.";
				$message_state = "error";
				$button_text = "Retry";
			}
			else
			{
				$status = false;
				if(isEmailInDatabase($link, $email))
				{
					// Send email with reset link
					$status = sendPasswordResetEmail($link, $email);
				}
				else
				{
					// Send nothing, pretend to be doing something
					$status = sendPasswordResetEmailDummy($link, $email);
				}
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
	finally 
	{
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
    <title>Reset Password</title>
	<link rel="stylesheet" href="css/m.forgotpass.css">
</head>
<body>
    <div class="wrapper">
        <h2>Reset Password</h2>
		<span class="info_<?php echo $message_state; ?>"><?php echo $message; ?></span>
		<br /><br />
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
			
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
