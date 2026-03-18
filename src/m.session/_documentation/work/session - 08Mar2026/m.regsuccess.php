<?php

	$title = "Welcome to Listiary!";
	$message = "Your account has been successfully created. We've sent you an email with a verification link. Until you verify your email, some features will be unavailable.<br><br>You can proceed to log in, or browse other Listiary wikis by visiting the index below.";
	$message_state = "success";
	$display_b1 = "block";
	$display_b2 = "block";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- CRITICAL FOR MOBILE: This line makes it scale correctly on phones -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
	<link rel="stylesheet" href="css/m.regsuccess.css">
</head>
<body>
    <div class="wrapper">
        <h2><?php echo $title; ?></h2>
		<span class="info_<?php echo $message_state; ?>"><?php echo $message; ?></span>
		<br /><br />
		
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"]); ?>" method="post">
            <div class="btn-container">
                <input onclick="window.location.href='m.login.php';" 
					type="button" class="btn btn-primary" value="Log In" 
					style="display:<?php echo $display_b1; ?>;">
				<input onclick="window.location.href='../m.index.php';"
					type="button" class="btn btn-secondary" value="Index"
					style="display:<?php echo $display_b2; ?>;">
            </div>
            <p style="text-align: center;">Don't have an account? <a href="m.register.php">Register here</a>.</p>
        </form>
    </div>
</body>
</html>
