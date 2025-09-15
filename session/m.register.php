<?php

	//https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php
	
	//include config
	require_once "_config.php";

	// Check connection
	if(!$link) die("Connection failed: " . mysqli_connect_error());
	//echo "Created connection to '" . $servername . "'<br />";
 
	// Define variables and initialize with empty values
	$username = ""; 
	$password = "";
	$confirm_password = "";
	$username_err = "";
	$password_err = "";
	$confirm_password_err = "";
 
	// Processing form data when form is submitted
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Validate username
		if(empty(trim($_POST["username"])))
		{
			$username_err = "Please enter a username.";
		} 
		elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"])))
		{
			$username_err = "Username can only contain letters, numbers, and underscores.";
		} 
		else
		{
			// Prepare a select statement
			$sql = "SELECT id FROM users WHERE username = ?";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "s", $param_username);
				
				// Set parameters
				$param_username = trim($_POST["username"]);
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt))
				{
					/* store result */
					mysqli_stmt_store_result($stmt);
					
					if(mysqli_stmt_num_rows($stmt) == 1)
					{
						$username_err = "This username is already taken.";
					} 
					else
					{
						$username = trim($_POST["username"]);
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
		
		// Validate password
		if(empty(trim($_POST["password"])))
		{
			$password_err = "Please enter a password.";     
		} 
		elseif(strlen(trim($_POST["password"])) < 12)
		{
			$password_err = "Password must have atleast 12 characters.";
		}
		else
		{
			$password = trim($_POST["password"]);
		}
		
		// Validate confirm password
		if(empty(trim($_POST["confirm_password"])))
		{
			$confirm_password_err = "Please confirm password.";     
		} 
		else
		{
			$confirm_password = trim($_POST["confirm_password"]);
			if(empty($password_err) && ($password != $confirm_password))
			{
				$confirm_password_err = "Password did not match.";
			}
		}
		
		// Check input errors before inserting in database
		if(empty($username_err) && empty($password_err) && empty($confirm_password_err))
		{
			
			// Prepare an insert statement
			$sql = "INSERT INTO users (username, password) VALUES (?, ?)";
			 
			if($stmt = mysqli_prepare($link, $sql))
			{
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
				
				// Set parameters
				$param_username = $username;
				$param_password = password_hash($password, PASSWORD_BCRYPT); // Creates a password hash
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt))
				{
					// Redirect to login page
					header("location: login.php");
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
    <title>Sign Up</title>
	<!-- https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css -->
    <link rel="stylesheet" href="register.styles.bootstrap.min.css">
    <style>
        body{ font: 70px sans-serif; }
        .wrapper{ width: 100px; padding: 100px; }
    </style>
</head>
<body>
    <div class="wrapper" style="margin-left: auto; margin-right: auto; width: 100%;">
		<h2 style="font: 70px sans-serif; text-align: center;">Sign Up</h2>
		<br />
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label style="font: 50px sans-serif;">Username</label>
                <input style="font: 70px sans-serif;"  type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label style="font: 50px sans-serif;">Password</label>
                <input style="font: 70px sans-serif;" type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label style="font: 50px sans-serif;">Confirm Password</label>
                <input style="font: 70px sans-serif;" type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
			<br />
            <div class="form-group" style="text-align: center;">
                <input style="font: 70px sans-serif;" type="submit" class="btn btn-primary" value="Submit">
                <input style="font: 70px sans-serif;" type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
			<br />
            <p style="text-align: center;">Already have an account? <a href="m.login.php">Login here</a>.</p>
        </form>
    </div>    
</body>
</html>