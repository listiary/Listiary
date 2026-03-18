function doRememberedLogin($link): bool {
		
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
						$usernameSql = "SELECT username, email, usercode, is_bot, is_active, created_at FROM accounts WHERE id = ?";
						if ($userStmt = mysqli_prepare($link, $usernameSql)) 
						{
							mysqli_stmt_bind_param($userStmt, "i", $user_id);
							mysqli_stmt_execute($userStmt);
							mysqli_stmt_bind_result($userStmt, $username, $email, $usercode, $is_bot, $is_active, $created_at);
							if (mysqli_stmt_fetch($userStmt))
							{
								// populate session
								$_SESSION['username'] = $username;
								$_SESSION['email'] = $email;
								$_SESSION['usercode'] = $usercode;
								$_SESSION['is_bot'] = $is_bot;
								$_SESSION['is_active'] = $is_active;
								$_SESSION['created_at'] = $created_at;
							}
							else
							{
								mysqli_stmt_close($userStmt);
								throw new RuntimeException('Invalid remember_token cookie');
							}
							mysqli_stmt_close($userStmt);
						}

						// Rotate persistent token (new validator + update DB + reset cookie)
						executePersistentTokenRotation($link, $user_id, $selector, $validator);
						return true;
					} 
					else 
					{
						// Invalid or expired token → delete from DB & remove cookie
						$delSql = "DELETE FROM persistent_logins WHERE selector = ?";
						$delStmt = mysqli_prepare($link, $delSql);
						mysqli_stmt_bind_param($delStmt, "s", $selector);
						mysqli_stmt_execute($delStmt);
						mysqli_stmt_close($delStmt);
						
						// Expire the cookie
						setcookie('remember_token', '', time() - 3600, '/', '', true, true);
						return false;
					}
				}
				else
				{
					throw new RuntimeException('Invalid remember_token cookie');
				}
				mysqli_stmt_close($stmt);
			}
			else
			{
				throw new RuntimeException('Invalid remember_token cookie');
			}
		}
		else
		{
			throw new RuntimeException('Invalid remember_token cookie');
		}
	}
	