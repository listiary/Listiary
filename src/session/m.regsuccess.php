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
            margin-top: 30px;
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
        <h2><?php echo $title; ?></h2>
		<span class="info_<?php echo $message_state; ?>"><?php echo $message; ?></span>
		<br /><br />
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"]); ?>" method="post">

            <div class="btn-container">
                <input onclick="window.location.href='m.login.php';" type="button" class="btn btn-primary" value="Log In" style="display:<?php echo $display_b1; ?>;">
				<input onclick="window.location.href='../m.index.php';" type="button" class="btn btn-secondary" value="Index" style="display:<?php echo $display_b2; ?>;">
            </div>

            <p style="text-align: center;">Don't have an account? <a href="m.register.php">Register here</a>.</p>
        </form>
    </div>
</body>
</html>
