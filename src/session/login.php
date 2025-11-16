<?php

	//https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php

	// Initialize the session
	session_start();
	
	//try to get redirect params
	$redirecturl = "https://development.worldinlists.net/m.index.php";
	if(isset($_GET['domain']) == true)
	{
		$redirecturl .= "?domain=" . $_GET['domain'];
		if(isset($_GET['article']) == true) 
			$redirecturl .= "&article=" . $_GET['article'];
	}
 
	// Check if the user is already logged in, if yes then redirect him to welcome page
	if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
	{
		header("location: " . $redirecturl);
		exit;
	}
 
	//include config
	require_once "_config.php";

	// Check connection
	if(!$link) die("Connection failed: " . mysqli_connect_error());
	//echo "Created connection to '" . $servername . "'<br />";
 
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
			$sql = "SELECT id, username, password FROM users WHERE username = ?";
			
			if($stmt = mysqli_prepare($link, $sql)){
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
								// Password is correct, so start a new session
								session_start();
								
								// Store data in session variables
								$_SESSION["loggedin"] = true;
								$_SESSION["id"] = $id;
								$_SESSION["username"] = $username;                            
								
								// Redirect user to welcome page
								header("location: " . $redirecturl);
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
		}
		
		// Close connection
		mysqli_close($link);
	}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
	<!-- https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css -->
    <link rel="stylesheet" href="register.styles.bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper" style="margin-left: auto; margin-right: auto; width: 30%;">
        <br /><br /><br /><br /><br />
		<h2 style="text-align: center;">Sign In</h2>
		<br />
		<?php if(!empty($login_err)){ echo '<div class="alert alert-danger">' . $login_err . '</div>';} ?>
        <form action="" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group" style="text-align: center;">
                <input type="submit" class="btn btn-primary" value="Log in">
            </div>
            <p style="text-align: center;">Don't have an account? <a href="register.php">Register here</a>.</p>
        </form>
    </div>    
</body>
</html>