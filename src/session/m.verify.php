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
	
	if(isset($_GET['token']) == false) die("'token' parameter of the url is not set");
	$token = $_GET['token'];
	//echo "Got token parameter - '" . $token . "'<br />";
	
	if(isset($_GET['email']) == false) die("'email' parameter of the url is not set");
	$email = urldecode($_GET['email']);
	//echo "Got email parameter - '" . $email . "'<br />";

	// Check if allowed to activate
	if(allowActivation($link, $email, $token) == false)
	{
		$title = "Verify email address";
		$message = "Something is wrong with this email verification link. Please log in and request a new verification link.";
		$message_state = "error";
		$display_b1 = "block";
		$display_b2 = "block";
	}
	else
	{
		// Prepare an SQL statement
		$sql = "UPDATE accounts
			SET verification_token = NULL,
			is_active = 1
			WHERE email = ?;";

        if($stmt = mysqli_prepare($link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);
                
            // Set parameters
			$param_email = $email;

			// Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt))
            {
				$title = "Welcome to Listiary!";
				$safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
				$message = "Your email address '<span class=\"info_info\" style=\"font-style: italic;\">" . $safe_email . "</span>' has been verified, and your account is active now.<br>You can proceed to Log In, or browse other Listiary wikis by visiting the index below.<br>Have an awesome journey!";
				$message_state = "success";
				$display_b1 = "block";
				$display_b2 = "block";
			}
			else
			{
				die("Prepare failed: " . mysqli_error($link));
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
	
	// Should activation be allowed
	function allowActivation($link, $email, $token) {
		
		$hashed_token = hash('sha256', $token);
		
		$sql = "SELECT 1
				FROM accounts
				WHERE email = ?
				AND verification_token = ?
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
    <title>Verify email address</title>
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
