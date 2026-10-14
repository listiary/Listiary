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
		// Get token url parameter
		$token = $_GET['token'] ?? '';
		if (empty($token))
		{
			throw new RuntimeException("'token' parameter of the URL is not set");
		}
		//echo "Got token parameter - '" . $token . "'<br />";

		// Validate token format
		if (!preg_match('/^[a-f0-9]{64}$/', $token))
		{
			throw new RuntimeException('Invalid token format');
		}
		
		// Default display variables
		$title = "Verify email address";
		$message_state = "info";
		$display_b1 = "block";
		$display_b2 = "block";
		

		// Check if allowed to activate
		$result = isActivationAllowed($link, $token);
		if($result['output'] == false)
		{
			$title = "Verify email address";
			$message = "Something is wrong with this email verification link. Please log in and request a new verification link.";
			$message_state = "error";
			$display_b1 = "block";
			$display_b2 = "block";
		}
		else
		{
			activateAccount($link, $token);
			$title = "Welcome to Listiary!";
			$safe_email = htmlspecialchars($result['email'], ENT_QUOTES, 'UTF-8');
			$message = "Your email address '<span class=\"info_info\" style=\"font-style: italic;\">" . $safe_email . "</span>' has been verified, and your account is active now.<br>You can proceed to Log In, or browse other Listiary wikis by visiting the index below.<br>Have an awesome journey!";
			$message_state = "success";
			$display_b1 = "block";
			$display_b2 = "block";
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
		<title>Verify email address</title>
		<link rel="stylesheet" href="css/m.verify.css">
	</head>
	<body>
		<div class="wrapper">
			<h2><?php echo $title; ?></h2>
			<span class="info_<?php echo $message_state; ?>"><?php echo $message; ?></span>
			<br /><br />

			<div class="btn-container">
				<input onclick="window.location.href='m.login.php';" 
					   type="button" class="btn btn-primary" value="Log In" style="display:<?php echo $display_b1; ?>;">
				<input onclick="window.location.href='../m.index.php';" 
					   type="button" class="btn btn-secondary" value="Index" style="display:<?php echo $display_b2; ?>;">
			</div>

			<p style="text-align: center;">Don't have an account? <a href="m.register.php">Register here</a>.</p>
		</div>
	</body>
</html>
