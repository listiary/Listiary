<?php

	//https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php
	// TODO: WE NEED:
	// session_regenerate_id(true)
	// CSRF protection on forms
	// Rate limiting login attempts
	// HTTPS-only session cookies
	// Logout script that destroys session properly

	//include config
	require_once __DIR__ . "/php/_config.php";
	
	//read config
	$servername = ""; 
	$db_username = ""; 
	$password = ""; 
	$dbname = "";
	setDbConstants("public");

	//create connection
	$link = createConnection();

	// Check connection
	if(!$link) die("Connection failed: " . mysqli_connect_error());
	//echo "Created connection to '" . $servername . "'<br />";
	
	// Initialize a secure session cookie
	session_set_cookie_params([
		'lifetime' => 0,
		'path' => '/',
		'domain' => '',
		'secure' => true,       // HTTPS only
		'httponly' => true,     // Not accessible via JS
		'samesite' => 'Strict'  // or 'Lax'
	]);

	// Initialize the session
	session_start();
	
	//try to get redirect params
	$redirecturl = "https://development.listiary.net/m.index.php";
	if(isset($_GET['domain']) == true)
	{
		$redirecturl .= "?domain=" . $_GET['domain'];
		if(isset($_GET['article']) == true) 
			$redirecturl .= "&article=" . $_GET['article'];
	}

	// If we logged in - redirect
	if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) 
	{
		header("Location: " . $redirecturl);
		exit;
	}
	else if (isset($_COOKIE['remember_token']))
	{
		$cookie = $_COOKIE['remember_token'];
		if (strpos($cookie, ':') !== false) 
		{
			list($selector, $validator) = explode(':', $cookie, 2);

			// Look up selector in DB
			$sql = "SELECT user_id, token_hash, expires_at FROM persistent_logins WHERE selector = ? LIMIT 1";
			if ($stmt = mysqli_prepare($link, $sql)) 
			{
				mysqli_stmt_bind_param($stmt, "s", $selector);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_bind_result($stmt, $user_id, $token_hash, $expires_at);
				if (mysqli_stmt_fetch($stmt)) 
				{
					// Check expiration
					if (new DateTime() <= new DateTime($expires_at) &&
						hash_equals($token_hash, hash('sha256', $validator))) 
					{

						// Validator matches and token not expired → restore session
						session_regenerate_id(true);
						$_SESSION['loggedin'] = true;
						$_SESSION['id'] = $user_id;

						// Optionally fetch username from accounts table
						$usernameSql = "SELECT username FROM accounts WHERE id = ?";
						if ($userStmt = mysqli_prepare($link, $usernameSql)) 
						{
							mysqli_stmt_bind_param($userStmt, "i", $user_id);
							mysqli_stmt_execute($userStmt);
							mysqli_stmt_bind_result($userStmt, $username);
							mysqli_stmt_fetch($userStmt);
							$_SESSION['username'] = $username;
							mysqli_stmt_close($userStmt);
						}

						// Rotate persistent token (new validator + update DB + reset cookie)
						executePersistentLogin($link, $user_id);

						header("Location: " . $redirecturl);
						exit;
					} 
					else 
					{
						// Invalid or expired token → delete from DB & remove cookie
						$delSql = "DELETE FROM persistent_logins WHERE selector = ?";
						$delStmt = mysqli_prepare($link, $delSql);
						mysqli_stmt_bind_param($delStmt, "s", $selector);
						mysqli_stmt_execute($delStmt);
						mysqli_stmt_close($delStmt);

						setcookie('remember_token', '', time() - 3600, '/', '', true, true);
					}
				}
				mysqli_stmt_close($stmt);
			}
		}
	}
 
	// Define variables and initialize with empty values
	$username = $password = "";
	$username_err = $password_err = $login_err = "";
 
	// Processing form data when form is submitted
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Check if username is empty
		if(empty(trim($_POST["username"])))
		{
			$username_err = "Please enter username.";
		} 
		else
		{
			$username = trim($_POST["username"]);
		}
		
		// Check if password is empty
		if(empty(trim($_POST["password"])))
		{
			$password_err = "Please enter your password.";
		} 
		else
		{
			$password = trim($_POST["password"]);
		}
		
		// Validate credentials
		if(empty($username_err) && empty($password_err))
		{
			// Prepare a select statement
			$sql = "SELECT id, username, password_hash FROM accounts WHERE username = ?";
			
			if($stmt = mysqli_prepare($link, $sql))
			{
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "s", $param_username);
				
				// Set parameters
				$param_username = $username;
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt))
				{
					// Store result
					mysqli_stmt_store_result($stmt);
					
					// Check if username exists, if yes then verify password
					if(mysqli_stmt_num_rows($stmt) == 1)
					{                    
						// Bind result variables
						mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
						if(mysqli_stmt_fetch($stmt))
						{
							if(password_verify($password, $hashed_password))
							{
								// Remember for an year?
								if(isset($_POST['remember_me']))
								{
									executePersistentLogin($link, $id);
								}
								
								// Store data in session variables
								session_regenerate_id(true);
								$_SESSION["loggedin"] = true;
								$_SESSION["id"] = $id;
								$_SESSION["username"] = $username;   
								
								// redirect back
								header("location: " . $redirecturl);
								exit;
							} 
							else
							{
								// Password is not valid, display a generic error message
								$login_err = "Invalid username or password.";
							}
						}
					} 
					else
					{
						// Username doesn't exist, display a generic error message
						$login_err = "Invalid username or password.";
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

		global $servername, $db_username, $password, $dbname;
		
		if($domain == "public")
		{
			$servername = DB_SERVER_PUBLIC;
			$db_username = DB_USERNAME_PUBLIC;
			$password = DB_PASSWORD_PUBLIC;
			$dbname = DB_NAME_PUBLIC;
		}
		else if($domain == "personal")
		{
			$servername = DB_SERVER_PERSONAL;
			$db_username = DB_USERNAME_PERSONAL;
			$password = DB_PASSWORD_PERSONAL;
			$dbname = DB_NAME_PERSONAL;
		}
		else if($domain == "private")
		{
			$servername = DB_SERVER_PRIVATE;
			$db_username = DB_USERNAME_PRIVATE;
			$password = DB_PASSWORD_PRIVATE;
			$dbname = DB_NAME_PRIVATE;
		}
		else if($domain == "normative")
		{
			$servername = DB_SERVER_DOCUMENTATION;
			$db_username = DB_USERNAME_DOCUMENTATION;
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

		global $servername, $db_username, $password, $dbname;

		$connection = mysqli_connect($servername, $db_username, $password, $dbname);
		if(!$connection) die("Connection failed: " . mysqli_connect_error());
		//echo "Created connection to '" . $servername . "'<br />";

		return $connection;
	}
	
	// Stores persistent login token to the database
	function executePersistentLogin($link, $user_id) {
		
		// Generate selector (public) and token (secret)
		$token = bin2hex(random_bytes(32));
		$hashedToken = hash('sha256', $token);
		$selector = bin2hex(random_bytes(8));
		
		// Store in database, let SQL compute expiration (1 year from now)
		$sql = "INSERT INTO persistent_logins (user_id, selector, token_hash, expires_at) 
            VALUES (?, ?, ?, NOW() + INTERVAL 1 YEAR)";
		
		if ($stmt = mysqli_prepare($link, $sql))
		{
			mysqli_stmt_bind_param($stmt, "iss", $user_id, $selector, $hashedToken);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		} 
		else
		{
			die("DB Error: " . mysqli_error($link));
		}

		// Set the cookie
		$cookieValue = $selector . ':' . $token;
		setcookie(
			"remember_token",
			$cookieValue,
			time() + (60*60*24*364),	//tiny safety buffer between the client and the DB to avoid 'just expired' edge cases caused by timezone differences.
			"/",
			"",
			true,
			true
		);
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- CRITICAL FOR MOBILE: This line makes it scale correctly on phones -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
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
		
		.remember-me {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-top: 8px;
			margin-bottom: 10px;
		}

		.remember-me input[type="checkbox"] {
			width: 18px;
			height: 18px;
			accent-color: #1877f2;
			cursor: pointer;
		}

		.remember-me label {
			margin: 0;
			font-size: 14px;
			font-weight: 600;
			color: #606770;
			cursor: pointer;
		}
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign In</h2>
		<br />
		<span><?php if(!empty($login_err)){ echo '<div class="alert alert-danger">' . $login_err . '</div>';} ?></span>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            
			<div class="form-group">
                <label>Email address or Username</label>
                <input type="text" name="username" 
                       class="<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $username; ?>"
                       placeholder="email or username">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" 
                       class="<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $password; ?>"
                       placeholder="password">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
			<div class="remember-me">
				<input type="checkbox" name="remember_me" id="remember_me"
					<?php echo (!empty($_POST['remember_me'])) ? 'checked' : ''; ?>>
				<label for="remember_me">Keep me logged in (for up to a year)</label>
			</div>

            <div class="btn-container">
                <input type="submit" class="btn btn-primary" value="Login">
                <input type="reset" class="btn btn-secondary" value="Reset">
            </div>
			

            <p style="text-align: center;">Don't have an account? <a href="m.register.php">Register here</a>.
			<br/><a href="m.forgotpass.php">Forgot password</a></p>
        </form>
    </div>
</body>
</html>