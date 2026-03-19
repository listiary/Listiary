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
								restoreUserSession($link, $id);

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