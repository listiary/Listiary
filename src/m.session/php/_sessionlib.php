<?php

	require_once __DIR__ . "/_sql_model.php";



	/* READ GET PARAMETERS */
	/**
	 * Retrieve the ID of the user to be shown from URL GET parameter 'id'
	 *
	 * @return int The id
	 * @throws RuntimeException When there is no such get argument or it is not valid
	 */
	function getUserId(): int {

		$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
		if ($id === null || $id === false) 
		{
			throw new RuntimeException('Invalid or missing "id" parameter in URL');
		}

		return $id;
	}

	/**
	 * Get the article link to return to after logging in.
	 *
	 * @return string The link
	 * @throws RuntimeException When there are missing or wrong parameters
	 */
	function getRedirectLink(): string {

		$base = rtrim(BASE_URL, '/') . '/m.index.php';
		$params = [];

		//if we have the domain parameter set, it can only contain letters
		if (isset($_GET['domain']))
		{
			if(!preg_match('/^[a-zA-Z]+$/', $_GET['domain']))
			{
				throw new RuntimeException('Invalid characters in "domain" parameter');
			}
			$params['domain'] = $_GET['domain'];
		}
		
		//if we have the article parameter set, it can only contain letters, numbers and dot - '/^[a-zA-Z0-9.]+$/'.
		//in this version regex, it also cannot start with a dot or have 2 or more dot clusters
		if (isset($_GET['article']))
		{
			if(!preg_match('/^[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)*$/', $_GET['article']))
			{
				throw new RuntimeException('Invalid characters in "article" parameter');
			}
			$params['article'] = $_GET['article'];
		}
		
		//build
		if (!empty($params)) 
		{
			$base .= '?' . http_build_query($params);
		}

		return $base;
	}





	/* FETCHING USER DATA FROM DB */
	/**
	 * Fetch a user's id and password hash from the accounts table.
	 *
	 * This function retrieves the user's credentials for authentication. 
	 *
	 * @param mysqli $link Database connection object
	 * @param string $email Email of the user to look up
	 * @return array|null ['id' => int, 'password_hash' => string] if user exists, null otherwise
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function fetchUserCredentials(mysqli $link, string $email): ?array {

		// Fetch user info
		$columns = [
			TableAccounts::$id,
			TableAccounts::$passwordHash,
		];
		$sql = 'SELECT ' . implode(', ', $columns) .
			' FROM ' . TableAccounts::$tableName . ' WHERE ' . 
			TableAccounts::$email . ' = ?';

		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt) 
		{
			throw new RuntimeException('Database prepare failed.');
		}
		mysqli_stmt_bind_param($stmt, "s", $email);
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Database execution failed.');
		}

		mysqli_stmt_bind_result($stmt, $id, $password_hash);
		if (!mysqli_stmt_fetch($stmt))
		{
			// No such user
			mysqli_stmt_close($stmt);
			return null;
		}

		mysqli_stmt_close($stmt);

		return [
			'id' => $id,
			'password_hash' => $password_hash,
		];
	}
	
	/**
	 * Fetch username and email for not logged in user.
	 *
	 * @param mysqli $link Database connection object
	 * @param int $user_id Id of the user to look up
	 * @return array{
	 *   username: string,
	 *   email: string,
	 *   is_bot: bool,
	 *   is_active: bool,
	 *   is_premium: bool,
	 *   created_at: string
	 * }
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function fetchUserData(mysqli $link, int $user_id): array {
		
		// Fetch user info
		$columns = [
			TableAccounts::$userName,
			TableAccounts::$email,
			TableAccounts::$isBot,
			TableAccounts::$isActive,
			TableAccounts::$isPremium,
			TableAccounts::$createdAt,
		];
		$userSql = "SELECT " . implode(', ', $columns) .
				   " FROM " . TableAccounts::$tableName .
				   " WHERE " . TableAccounts::$id . " = ? LIMIT 1";
					
		$userStmt = mysqli_prepare($link, $userSql);
		if (!$userStmt) {
			throw new RuntimeException('DB prepare failed for user info');
		}

		mysqli_stmt_bind_param($userStmt, "i", $user_id);
		mysqli_stmt_execute($userStmt);
		
		$result = mysqli_stmt_get_result($userStmt);
		$row = mysqli_fetch_assoc($result);
		mysqli_stmt_close($userStmt);
		
		if (!$row)
		{
			throw new RuntimeException('User not found for session restoration');
		}

		// Populate session
		return [
			"username" => $row['username'],
			"email" => $row['email'],
			"is_bot" => (bool)$row['is_bot'],
			"is_active" => (bool)$row['is_active'],
			"is_premium" => (bool)$row['is_premium'],
			"created_at" => $row['created_at']
		];
	}

	/**
	 * Fetch user details and store them in the session superglobal.
	 *
	 * @param mysqli $link Database connection object
	 * @param int $user_id ID of the user to look up
	 * @return void
	 * @throws RuntimeException When database prepare/execute fails or user is not found
	 * @sideEffects Populates $_SESSION with user detail fields:
	 *   - avatar_path
	 *   - avatar_shape
	 *   - avatar_shape_radius
	 *   - bio
	 *   - city
	 *   - country
	 *   - timezone
	 *   - phone1
	 *   - link_personal_website
	 *   - link_personal_facebook
	 *   - link_personal_xcom
	 *   - link_personal_linkedin
	 *   - link_personal_other
	 */
	function fetchUserDetails(mysqli $link, int $user_id): void {

		// Fetch user details
		$columns = [
			TableAccountDetails::$avatarPath,
			TableAccountDetails::$avatarShape,
			TableAccountDetails::$avatarShapeRadius,
			TableAccountDetails::$bio,
			TableAccountDetails::$city,
			TableAccountDetails::$country,
			TableAccountDetails::$timezone,
			TableAccountDetails::$phoneMain,
			TableAccountDetails::$linkPersonalWebsite,
			TableAccountDetails::$linkPersonalFacebook,
			TableAccountDetails::$linkPersonalXcom,
			TableAccountDetails::$linkPersonalLinkedin,
			TableAccountDetails::$linkPersonalOther,
		];
		$userSql = "SELECT " . implode(', ', $columns) .
					" FROM " . TableAccountDetails::$tableName . 
					" WHERE " . TableAccountDetails::$accountId . " = ? LIMIT 1";

		$userStmt = mysqli_prepare($link, $userSql);
		if (!$userStmt)
		{
			throw new RuntimeException('DB prepare failed for user details');
		}

		mysqli_stmt_bind_param($userStmt, "i", $user_id);
		mysqli_stmt_execute($userStmt);
		$result = mysqli_stmt_get_result($userStmt);
		$row = mysqli_fetch_assoc($result); // associative array

		mysqli_stmt_close($userStmt);
		if (!$row) 
		{
			throw new RuntimeException('User not found for fetching details');
		}

		// Populate session
		$_SESSION['avatar_path'] = $row['avatar_path'];
		$_SESSION['avatar_shape'] = $row['avatar_shape'];
		$_SESSION['avatar_shape_radius'] = $row['avatar_shape_radius'];
		$_SESSION['bio'] = $row['bio'];
		$_SESSION['city'] = $row['city'];
		$_SESSION['country'] = $row['country'];
		$_SESSION['timezone'] = $row['timezone'];
		$_SESSION['phone1'] = $row['phone1'];
		$_SESSION['link_personal_website'] = $row['link_personal_website'];
		$_SESSION['link_personal_facebook'] = $row['link_personal_facebook'];
		$_SESSION['link_personal_xcom'] = $row['link_personal_xcom'];
		$_SESSION['link_personal_linkedin'] = $row['link_personal_linkedin'];
		$_SESSION['link_personal_other'] = $row['link_personal_other'];
	}

	/**
	 * Fetch user details and return them in an associative array.
	 *
	 * @param mysqli $link Database connection object
	 * @param int $user_id ID of the user to look up
	 * @throws RuntimeException When database prepare/execute fails or user is not found
	 * @return array{
	 *   avatar_path: string|null,
	 *   avatar_shape: string,
	 *   avatar_shape_radius: int,
	 *   bio: string|null,
	 *   city: string|null,
	 *   country: string|null,
	 *   timezone: string,
	 *   phone1: string|null,
	 *   link_personal_website: string|null,
	 *   link_personal_facebook: string|null,
	 *   link_personal_xcom: string|null,
	 *   link_personal_linkedin: string|null,
	 *   link_personal_other: string|null
	 * }
	 */
	function fetchUserDetailsArr(mysqli $link, int $user_id): array {

		// Fetch user details
		$columns = [
			TableAccountDetails::$avatarPath,
			TableAccountDetails::$avatarShape,
			TableAccountDetails::$avatarShapeRadius,
			TableAccountDetails::$bio,
			TableAccountDetails::$city,
			TableAccountDetails::$country,
			TableAccountDetails::$timezone,
			TableAccountDetails::$phoneMain,
			TableAccountDetails::$linkPersonalWebsite,
			TableAccountDetails::$linkPersonalFacebook,
			TableAccountDetails::$linkPersonalXcom,
			TableAccountDetails::$linkPersonalLinkedin,
			TableAccountDetails::$linkPersonalOther,
		];
		$userSql = "SELECT " . implode(', ', $columns) .
					" FROM " . TableAccountDetails::$tableName . 
					" WHERE " . TableAccountDetails::$accountId . " = ? LIMIT 1";

		$userStmt = mysqli_prepare($link, $userSql);
		if (!$userStmt)
		{
			throw new RuntimeException('DB prepare failed for user details');
		}

		mysqli_stmt_bind_param($userStmt, "i", $user_id);
		mysqli_stmt_execute($userStmt);
		$result = mysqli_stmt_get_result($userStmt);
		$row = mysqli_fetch_assoc($result); // associative array

		mysqli_stmt_close($userStmt);
		if (!$row) 
		{
			throw new RuntimeException('User not found for fetching details');
		}

		return $row;
	}

	/**
	 * Fetch user names along with some flags for all the user accounts and return them in an associative array.
	 * Only for now, we return only 1 user for testing
	 *
	 * @param mysqli $link Database connection object
	 * @throws RuntimeException When database prepare/execute fails
	 * @return array{id: int, username: string, is_premium: bool}
	 */
	function fetchUserNames(mysqli $link): array {

		$sql = "SELECT id, username, is_premium FROM accounts";
		$stmt = mysqli_prepare($link, $sql);

		if (!$stmt) 
		{
			throw new RuntimeException('Database prepare failed: ' . mysqli_error($link));
		}

		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);

		$accounts = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$accounts[] = [
				'id' => (int) $row['id'],
				'username' => (string) $row['username'],
				'is_premium' => (bool) $row['is_premium'],
			];
		}

		mysqli_stmt_close($stmt);

		return $accounts;
	}
	
	/**
	 * Create random real-sounding account names for testing
	 *
	 * @param mysqli $link Database connection object
	 * @return list<string>
	 */
	function getDummyAccountNames(int $count): array {

		$count = min($count, 200);
		
		$names = ["ArchiveBuilder", "CitationHunter", "HistoryMapper", "SourceVerifier", "FootnoteForge", "PageGardener", "TemplateSmith", "SectionRefactor", "EditNavigator", "KnowledgeCat", "ArchiveNomad", "FactTrail", "DataChronicler", "ReferenceKeeper", "PageWeaver", "RecordCurator", "CatalogPilot", "InfoSurveyor", "ManuscriptMiner", "ContextBuilder", "AtlasEditor", "ChronicleWriter", "DocumentSeeker", "FootnoteCartel", "AnnotationPilot", "PageArchitect", "ArticleScout", "EditArchivist", "ContentRanger", "SourcePathfinder", "KnowledgeHarbor", "CitationSailor", "ArchiveCompass", "DataScribe", "ReferenceNavigator", "ArticleGardener", "CatalogExplorer", "EditMechanic", "RecordArchitect", "FootnoteMapper", "SourceMiner", "KnowledgeSmith", "PageSurveyor", "ChronicleBuilder", "ContextMiner", "ArchiveTactician", "FactAssembler", "ReferencePilot", "SourceAssembler", "ContentArchivist", "QuietLibrarian", "NightOwlReader", "WanderingFootnote", "BlueInkWriter", "PaperTrailFox", "LanternScholar", "RustyCompass", "EchoArchivist", "SilverNotebook", "CuriousAtlas", "WanderingEditor", "HiddenLibrary", "OldMapSeeker", "AmberNotebook", "SilentCartographer", "WanderingQuill", "MarbleNotebook", "CuriousChronicler", "UrbanHistorian", "PaperVoyager", "LanternArchivist", "BronzeAtlas", "QuietSurveyor", "StoryNavigator", "WanderingScribe", "FoggyArchive", "IvoryNotebook", "AtlasDreamer", "EchoNavigator", "CedarArchivist", "CuriousVoyager", "DistantFootnote", "OpenNotebook", "MarbleHistorian", "LanternVoyager", "NorthboundReader", "ArchiveDreamer", "IronNotebook", "PaperArchivist", "SilentNavigator", "AtlasFootnote", "DriftHistorian", "ArchivePilgrim", "QuietChronicler", "FoggyNavigator", "PaperSurveyor", "WanderingAtlas", "CedarNotebook", "HiddenCartographer", "StoryArchivist", "alex_m92", "lina_edit87", "mkovacs73", "historyfan21", "daniel_k_dev", "nika404", "reader_steve", "ivanwiki88", "rafael_works", "mike_writer77", "anna_source92", "pavel_reader", "sara_archive", "tom_data83", "lena_maps", "alex_context", "nina_page91", "viktor_docs", "matej_archive77", "dan_reader24", "luca_builder", "kira_editor66", "geo_source88", "ivan_notes42", "alex_record", "nina_mapper", "mark_edit55", "lena_page21", "tom_source19", "sara_archive91", "nikolay_docs", "martin_builder", "lena_context", "alex_refactor", "tanya_reader", "boris_archive33", "pavel_maps88", "dan_editor74", "nina_record17", "alex_footnote", "sara_chronicle", "viktor_context55", "matej_docs", "lena_builder82", "ivan_archive19", "mark_mapper", "tom_refactor", "nina_article", "alex_citation"];

		shuffle($names);
		return array_slice($names, 0, $count);
	}
	
	
	
	
	
	/* REGISTER / PASSWORD-RESET RELATED */
	/**
	 * Check if we can resend password reset email (5 minutes have passed from the last sending)
	 *
	 * @param mysqli $link Database connection object
	 * @param string $email Email of the user to look up
	 * @throws RuntimeException When database prepare or execute fail
	 * @return bool True if it is too soon to resend the password reset email, false otherwise
	 */
	function checkIfTooSoonToResend(mysqli $link, string $email): bool {

		//should we add `created_at` field in the DB as well?
		$sql = "SELECT 1
				FROM " . TablePasswordResets::$tableName .
				" WHERE " . TablePasswordResets::$email . " = ?" .
				" AND " . TablePasswordResets::$expiresAt . " > DATE_ADD(NOW(), INTERVAL 55 MINUTE)
				LIMIT 1";

		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException("Database error.");
		}

		mysqli_stmt_bind_param($stmt, "s", $email);
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException("Database error.");
		}

		mysqli_stmt_store_result($stmt);
		$res = mysqli_stmt_num_rows($stmt);
		$tooSoon = $res > 0;
		
		mysqli_stmt_close($stmt);
		return $tooSoon;
	}
	
	/**
	 * Send password reset email and save reset token in the DB
	 *
	 * @param mysqli $link Database connection object
	 * @param string $email Email to send the email to
	 * @throws RuntimeException When database prepare or execute fail
	 * @return bool True if successful
	 */
	function sendPasswordResetEmail(mysqli $link, string $email): bool {
    
		// 1. Generate a raw, cryptographically secure token
		// This is what we will email to the user.
		$raw_token = bin2hex(random_bytes(32));

		// 2. Hash the token for database storage
		// If a hacker steals your DB, they only get the hashes, not the usable links.
		$hashed_token = hash('sha256', $raw_token);

		// 3. Insert or Update the token in MariaDB
		// ON DUPLICATE KEY UPDATE ensures that if a user requests a reset 3 times,
		// they don't get 3 rows. It just overwrites their existing token and extends the timer.
		$sql = "INSERT INTO password_resets (email, token, expires_at) 
				VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR)) 
				ON DUPLICATE KEY UPDATE 
				token = VALUES(token), 
				expires_at = VALUES(expires_at)";
            
		if ($stmt = mysqli_prepare($link, $sql)) 
		{
			// Bind parameters: 's' for email (string), 's' for hashed_token (string)
			mysqli_stmt_bind_param($stmt, "ss", $email, $hashed_token);
			
			// Execute and check for failure
			if (!mysqli_stmt_execute($stmt)) 
			{
				// In a real app, you might want to log this error silently
				//error_log("Database error creating reset token: " . mysqli_error($link));
				mysqli_stmt_close($stmt);
				return false;
			}
			mysqli_stmt_close($stmt);
		} 
		else 
		{
			return false; // Statement preparation failed
		}

		// 4. Construct the Reset URL
		// We send the RAW token in the URL, not the hashed one!
		// We also pass the email in the URL to make looking up the database row easier later.
		$reset_url = "https://development.listiary.org/m.session/m.resetpass.php?token=" . $raw_token;

		// 5. Send the Email
		$subject = "Password Reset Request";
		
		$message = "Hello,\n\n";
		$message .= "We received a request to reset the password for your account.\n";
		$message .= "You can reset your password by clicking the link below:\n\n";
		$message .= $reset_url . "\n\n";
		$message .= "This link will expire in 1 hour. If you did not request a password reset, please ignore this email.\n";

		// Standard email headers
		$headers = "From: noreply@listiary.org\r\n";
		$headers .= "Reply-To: noreply@listiary.org\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion();

		// Use PHP's built-in mail function (Note: In production, consider using PHPMailer or Symfony Mailer for better deliverability)
		if (mail($email, $subject, $message, $headers)) 
		{
			return true;
		}
		else 
		{
			return false;
		}
	}

	/**
	 * This is Dummy version of sendPasswordResetEmail function that does nothing meaningful but take time to execute.
	 * Use to twart timing attacks aiming at email enumeration
	 *
	 * @param mysqli $link Database connection object
	 * @param string $email Email to send the email to
	 * @return bool True, always
	 */
	function sendPasswordResetEmailDummy(mysqli $link, string $email): bool {

		// 1. Generate token (same CPU cost)
		$raw_token = bin2hex(random_bytes(32));

		// 2. Hash token (same CPU cost)
		$hashed_token = hash('sha256', $raw_token);

		// 3. Perform a harmless dummy query to simulate DB timing
		$sql = "SELECT SLEEP(0.05)"; // 50ms artificial DB delay
		if ($stmt = mysqli_prepare($link, $sql)) {
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		}

		// 4. Add small randomized delay to reduce timing fingerprinting
		usleep(random_int(20000, 60000)); // 20–60ms

		// 5. Always return true to avoid leaking account existence
		return true;
	}
	
	/**
	 * Send account activation link
	 *
	 * @param mysqli $link Database connection object
	 * @param string $username Username to be used in the message
	 * @param string $email Email to send the verification email to
	 * @param string $raw_token The raw token to be stent as part of the verification link
	 * @return bool True if successful, otherwise false
	 */
	function sendVerificationEmail(mysqli $link, string $username, string $email, string $raw_token): bool {
		
		// Construct the verification URL
		// We send the RAW token in the URL, not the hashed one!
		$verify_url = "https://development.listiary.org/m.session/m.verify.php?token=" . $raw_token;

		// Send the Email
		$subject = "Welcome - account verification";
		$message  = "Hello {$username},\n\n";
		$message .= "Welcome.\n\n";
		$message .= "Your account is ready. To activate it, please verify your email address using the link below:\n\n";
		$message .= $verify_url . "\n\n";
		$message .= "If you did not create this account, you can safely ignore this message.\n\n";
		$message .= "We look forward to seeing what you create.\n\n";
		$message .= "If you did not register this account, you can safely ignore this email.\n";

		// Standard email headers
		$headers = "From: noreply@listiary.org\r\n";
		$headers .= "Reply-To: noreply@listiary.org\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion();

		// Use PHP's built-in mail function (Note: In production, consider using PHPMailer or Symfony Mailer for better deliverability)
		if (mail($email, $subject, $message, $headers)) 
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
	
	/**
	 * Check if account with a given email exists in the database
	 *
	 * @param mysqli $link Database connection object
	 * @param string $email Email to check
	 * @throws RuntimeException When database prepare or execute fail
	 * @return bool True if account with that email exists, otherwise false
	 */
	function isEmailInDatabase(mysqli $link, string $email): bool {
		
		$sql = "SELECT 1 FROM " . TableAccounts::$tableName . " WHERE " . TableAccounts::$email . " = ? LIMIT 1";
		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt)
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}

		// Bind variables to the prepared statement as parameters
		if (!mysqli_stmt_bind_param($stmt, "s", $email)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}

		// Attempt to execute the prepared statement
		if (!mysqli_stmt_execute($stmt)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}

		mysqli_stmt_store_result($stmt);
		$exists = mysqli_stmt_num_rows($stmt) > 0;

		mysqli_stmt_close($stmt);
		return $exists;
	}
	
	/**
	 * Update password in the DB
	 *
	 * @param mysqli $link Database connection object
	 * @param string $email Email of the user account
	 * @param string $new_password The new password
	 * @throws RuntimeException When database prepare or execute fail
	 * @return void
	 */
	function updatePassword(mysqli $link, string $email, string $new_password) {
		
		$sql = "UPDATE " . TableAccounts::$tableName . 
			" SET " . TableAccounts::$passwordHash . " = ?" . 
			" WHERE " . TableAccounts::$email . " = ?";
			
		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt)
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		
		// Bind variables to the prepared statement as parameters
		if (!mysqli_stmt_bind_param($stmt, "ss", $param_new_password_hash, $param_email))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		
		// Set parameters
		$param_email = $email;
		$param_new_password_hash = password_hash($new_password, PASSWORD_BCRYPT); // Creates a password hash
		
		// Attempt to execute the prepared statement
		if (!mysqli_stmt_execute($stmt)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
	}

	/**
	 * Check if a user with a given username exists in the DB
	 *
	 * @param mysqli $link Database connection object
	 * @param string $username The username to check for
	 * @throws RuntimeException When database prepare or execute fail
	 * @return bool True if username is taken, otherwise false
	 */
	function isUsernameTaken(mysqli $link, string $username): bool {
		
		// Prepare a select statement
		$sql = "SELECT " . TableAccounts::$id . 
			" FROM " . TableAccounts::$tableName . 
			" WHERE " . TableAccounts::$userName . " = ?";
	
		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		if(!mysqli_stmt_bind_param($stmt, "s", $param_username))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		$param_username = trim($username);
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
		
		/* store result */
		mysqli_stmt_store_result($stmt);
		if(mysqli_stmt_num_rows($stmt) == 1) return true;
		else return false;
	}
	
	/**
	 * Check if a user with a given email exists in the DB
	 *
	 * @param mysqli $link Database connection object
	 * @param string $email The email to check for
	 * @throws RuntimeException When database prepare or execute fail
	 * @return bool True if email is taken, otherwise false
	 */
	function isEmailTaken(mysqli $link, string $email): bool {
		
		// Prepare a select statement
		$sql = "SELECT " . TableAccounts::$id . 
			" FROM " . TableAccounts::$tableName . 
			" WHERE " . TableAccounts::$email . " = ?";
	
		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		if(!mysqli_stmt_bind_param($stmt, "s", $param_email))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		$param_email = trim($email);
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
		
		/* store result */
		mysqli_stmt_store_result($stmt);
		if(mysqli_stmt_num_rows($stmt) == 1) return true;
		else return false;
	}
	
	/**
	 * Create a new account in the DB
	 *
	 * @param mysqli $link Database connection object
	 * @param string $email The email of the account
	 * @param string $password The password of the account
	 * @param bool $isBot A flag denoting if this is a bot account or a human user
	 * @throws RuntimeException When database prepare or execute fail
	 * @return void
	 */
	function createAccount($link, $username, $email, $password, $isBot = 0): void {
		
		// Prepare an insert statement
		$columns = [
			TableAccountDetails::$userName,
			TableAccountDetails::$email,
			TableAccountDetails::$passwordHash,
			TableAccountDetails::$userCode,
			TableAccountDetails::$isBot,
			TableAccountDetails::$verificationToken,
		];
		$sql = "INSERT INTO " . TableAccounts::$tableName . 
			" (" . implode(', ', $columns) . ") VALUES (?, ?, ?, ?, ?, ?)";

		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		
		// Bind variables to the prepared statement as parameters
		if(!mysqli_stmt_bind_param($stmt, "ssssis", $param_username, $param_email, $param_password_hash, $param_usercode, $param_is_bot, $param_verification_token_hash))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		
		// Set parameters
		$param_username = $username;
		$param_email = $email;
		$param_password_hash = password_hash($password, PASSWORD_BCRYPT); // Creates a password hash
		$raw = hash_hmac('sha256', $param_username . $param_email, $secret_key, true);
		$param_usercode = substr(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='), 0, 16);
		$param_is_bot = 0;

		// Set parameters - verification token
		$raw_token = bin2hex(random_bytes(32));
		$hashed_token = hash('sha256', $raw_token);
		$param_verification_token_hash = $hashed_token;
		
		// Attempt to execute the prepared statement
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
		mysqli_stmt_close($stmt);
	}
	
	
	
	
	
	/* ACCOUNT VERIFICATION - M.VERIFY.PHP */
	/**
	 * Should activation be allowed, also fetches the email for that account
	 * @param mysqli $link Database connection object
	 * @param string $token Verification token
	 * @return array{
	 *   output: bool,
	 *   email: string
	 * }
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function isActivationAllowed(mysqli $link, string $token): array {
		
		// Hash the token as stored in the DB
		$hashed_token = hash('sha256', $token);
		
		$sql = "SELECT " TableAccounts::$email
				" FROM " . TableAccounts::$tableName .
				" WHERE " . TableAccounts::$verificationToken . " = ?" .
				" LIMIT 1";

		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt)
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}

		if (!mysqli_stmt_bind_param($stmt, "s", $hashed_token)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}

		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}

		// Bind result
		mysqli_stmt_bind_result($stmt, $email);
		$has_row = mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		// Return array with output flag and email
		return [
			'output' => $has_row !== null && $has_row !== false && $email !== null,
			'email' => $email ?? null
		];
	}

	/**
	 * Activate an account, remove activation token
	 * @param mysqli $link Database connection object
	 * @param string $token Verification token
	 * @return void
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function activateAccount(mysqli $link, string $token): void {

		// Hash the token as stored in the DB
		$hashed_token = hash('sha256', $token);

		// Prevare an SQL statement
		$sql = "UPDATE " . TableAccounts::$tableName .
				" SET " .  TableAccounts::$verificationToken . " = NULL, " . TableAccounts::$isActive . " = 1" .
				" WHERE " . TableAccounts::$verificationToken . " = ?";

		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt)
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}

		// Bind variables to the prepared statement as parameters
		if (!mysqli_stmt_bind_param($stmt, "s", $hashed_token)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}

		// Attempt to execute the prepared statement
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}

		// Optional: check if any row was actually updated
		if (mysqli_stmt_affected_rows($stmt) === 0)
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('No account was activated. Token may be invalid or already used.');
		}

		mysqli_stmt_close($stmt);
	}


	
	
	
	/* PASSWORD CHANGE - M.RESETPASS.PHP */
	/**
	 * Should password change be allowed
	 * @param mysqli $link Database connection object
	 * @param string $token Password reset token
	 * @return array{
	 *   output: bool,
	 *   email: string
	 * }
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function allowPasswordChange(mysqli $link, string $token): array {

		$hashed_token = hash('sha256', $token);

		$sql = "SELECT " . TablePasswordResets::$email
				" FROM " . TablePasswordResets::$tableName
				" WHERE " . TablePasswordResets::$token . " = ?" .
				" AND " . TablePasswordResets::$expiresAt . " >= NOW()" .
				" LIMIT 1";

		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		if(!mysqli_stmt_bind_param($stmt, "s", $hashed_token))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}
		
		// Bind result
		mysqli_stmt_bind_result($stmt, $email);
		$has_row = mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		// Return array with output flag and email
		return [
			'output' => $has_row !== null && $has_row !== false && $email !== null,
			'email' => $email ?? null
		];
	}

	/**
	 * Remove reset record from the DB
	 * @param mysqli $link Database connection object
	 * @param string $email Email of the user
	 * @return void
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function removeResetRecord(mysqli $link, string $email): void {
		
		$sql = "DELETE FROM " . TablePasswordResets::$tableName . 
			" WHERE " . TablePasswordResets::$email . " = ?";

		if (!$stmt = mysqli_prepare($link, $sql))
		{
			throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($link));
		}
		if (!mysqli_stmt_bind_param($stmt, "s", $email)) 
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
		}
		if (!mysqli_stmt_execute($stmt))
		{
			mysqli_stmt_close($stmt);
			throw new RuntimeException('Failed to execute statement: ' . mysqli_stmt_error($stmt));
		}

		mysqli_stmt_close($stmt);
	}


	
	
	
	/* PERSISTENT LOGIN */
	/**
	 * Log in with a remember me token we have inside a cookie
	 * @param mysqli $link Database connection object
	 * @return bool True if login was successful, false otherwise
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function doRememberedLogin(mysqli $link): bool {

		/* Attempt a "remember me" login using persistent cookie
		 * Returns true if login succeeds, false if cookie invalid or expired
		 * Throws RuntimeException for malformed cookie or serious DB issues */
	 
		if (!isset($_COOKIE['remember_token'])) return false;
		$cookie = $_COOKIE['remember_token'];

		// Split cookie into selector and validator
		if (strpos($cookie, ':') === false)
		{
			throw new RuntimeException('Invalid remember_token cookie format');
		}
		list($selector, $validator) = explode(':', $cookie, 2);
		if (empty($selector) || empty($validator)) 
		{
			throw new RuntimeException('Invalid remember_token cookie content');
		}

		// Look up selector in DB
		$columns = [
			TablePersistentLogins::$userId,
			TablePersistentLogins::$tokenHash,
			TablePersistentLogins::$expiresAt,
		];
		$sql = "SELECT " . implode(', ', $columns) .
				" FROM " . TablePersistentLogins::$tableName . 
				" WHERE " . TablePersistentLogins::$selector . " = ? LIMIT 1";

		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt) 
		{
			throw new RuntimeException('DB prepare failed');
		}
		mysqli_stmt_bind_param($stmt, "s", $selector);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $user_id, $token_hash, $expires_at);
		$rowExists = mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		if (!$rowExists) 
		{
			// No such selector - possibly stale or forged cookie
			setcookie('remember_token', '', time() - 3600, '/', '', true, true);
			return false;
		}

		// Verify expiration and token
		$now = new DateTime();
		$expires = new DateTime($expires_at);
		$validatorHash = hash('sha256', $validator);

		// If token invalid or expired - delete DB row + remove cookie
		if ($now > $expires || !hash_equals($token_hash, $validatorHash))
		{
			invalidateRememberToken($link, $selector);
			return false;
		}

		// Token valid thus restore session
		restoreUserSession($link, $user_id);

		// Rotate persistent token safely
		executePersistentTokenRotation($link, $user_id, $selector, $validator);

		return true;
	}

	/**
	 * Delete token from DB
	 * @param mysqli $link Database connection object
	 * @param string $selector The selector of the token to be deleted
	 * @return void
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function invalidateRememberToken(mysqli $link, string $selector): void {
		
		/* Invalidate a persistent login token.
		 * Deletes the token row from the database and expires the client cookie. */
		
		$delSql = "DELETE FROM " . TablePersistentLogins::$tableName . 
			" WHERE " . TablePersistentLogins::$selector . " = ?";

		$delStmt = mysqli_prepare($link, $delSql);
		if ($delStmt) {
			mysqli_stmt_bind_param($delStmt, "s", $selector);
			mysqli_stmt_execute($delStmt);
			mysqli_stmt_close($delStmt);
		}

		// Expire cookie on client
		setcookie('remember_token', '', time() - 3600, '/', '', true, true);
	}

	/**
	 * Delete all the tokens from DB for an user
	 * @param mysqli $link Database connection object
	 * @param int $id The id of the user whose tokens are to be deleted
	 * @return void
	 * @throws RuntimeException When database prepare or execute fails
	 */
	function invalidateAllRememberTokens(mysqli $link, int $id): void {
		
		/* Invalidate a persistent login token.
		 * Deletes all the token row from the database for an user and expires the client cookie. */
		
		$delSql = "DELETE FROM " . TablePersistentLogins::$tableName . 
			" WHERE " . TablePersistentLogins::$userId . " = ?";

		$delStmt = mysqli_prepare($link, $delSql);
		if ($delStmt) {
			mysqli_stmt_bind_param($delStmt, "i", $id);
			mysqli_stmt_execute($delStmt);
			mysqli_stmt_close($delStmt);
		}

		// Expire cookie on client
		setcookie('remember_token', '', time() - 3600, '/', '', true, true);
	}

	/**
	 * Restore session for a given user ID.
	 * Fetches user info from accounts table and populates session superglobal
	 *
	 * @param mysqli $link Database connection object
	 * @param int $user_id The id of the user to restore session for
	 * @return void
	 * @throws RuntimeException if DB fails or user not found
	 * @sideEffects Populates $_SESSION with fields:
	 *   - username
	 *   - email
	 *   - usercode
	 *   - is_bot
	 *   - is_active
	 *   - is_premium
	 *   - created_at
	 */
	function restoreUserSession(mysqli $link, int $user_id): void {

		// Regenerate session ID to prevent session fixation
		session_regenerate_id(true);
		$_SESSION['loggedin'] = true;
		$_SESSION['id'] = $user_id;

		// Fetch user info
		$columns = [
			TableAccounts::$userName,
			TableAccounts::$email,
			TableAccounts::$userCode,
			TableAccounts::$isBot,
			TableAccounts::$isActive,
			TableAccounts::$isPremium,
			TableAccounts::$createdAt,
		];
		$userSql = "SELECT " . implode(', ', $columns) .
					" FROM " . TableAccounts::$tableName .
					" WHERE " . TableAccounts::$id . " = ?" .
					" LIMIT 1";

		$userStmt = mysqli_prepare($link, $userSql);
		if (!$userStmt) {
			throw new RuntimeException('DB prepare failed for user info');
		}

		mysqli_stmt_bind_param($userStmt, "i", $user_id);
		mysqli_stmt_execute($userStmt);
		
		$result = mysqli_stmt_get_result($userStmt);
		$row = mysqli_fetch_assoc($result);
		mysqli_stmt_close($userStmt);
		
		if (!$row)
		{
			throw new RuntimeException('User not found for session restoration');
		}

		// Populate session
		$_SESSION['username'] = $row['username'];
		$_SESSION['email'] = $row['email'];
		$_SESSION['usercode'] = $row['usercode'];
		$_SESSION['is_bot'] = (bool)$row['is_bot'];
		$_SESSION['is_active'] = (bool)$row['is_active'];
		$_SESSION['is_premium'] = (bool)$row['is_premium'];
		$_SESSION['created_at'] = $row['created_at'];
	}
	
	/**
	 * Stores persistent login token hash in the database and token in the user cookie
	 *
	 * @param mysqli $link Database connection object
	 * @param int $user_id The id of the user to execute the login for
	 * @return void
	 * @throws RuntimeException if DB fails or setting cookie fails
	 */
	function executePersistentLogin(mysqli $link, int $user_id): void {

		// Generate token and selector
		$token = bin2hex(random_bytes(32));
		$hashedToken = hash('sha256', $token);
		$selector = bin2hex(random_bytes(8));

		// SQL query
		$columns = [
			TablePersistentLogins::$userId,
			TablePersistentLogins::$selector,
			TablePersistentLogins::$tokenHash,
			TablePersistentLogins::$expiresAt,
		];
		$sql = "INSERT INTO " . TablePersistentLogins::$tableName . 
				" (" . implode(', ', $columns) . ")" .
				" VALUES (?, ?, ?, NOW() + INTERVAL 1 YEAR)";

		// Prepare statement (throws exception if it fails)
		$stmt = mysqli_prepare($link, $sql);

		// Bind parameters
		mysqli_stmt_bind_param($stmt, "iss", $user_id, $selector, $hashedToken);

		// Execute statement (throws exception if it fails)
		mysqli_stmt_execute($stmt);

		// Close statement
		mysqli_stmt_close($stmt);

		// Build cookie value
		$cookieValue = $selector . ':' . $token;

		// Set cookie
		$result = setcookie(
			"remember_token",
			$cookieValue,
			time() + (60 * 60 * 24 * 364),
			"/",
			"",
			true,
			true
		);

		// Explicitly check cookie result
		if ($result === false)
		{
			throw new Exception("Failed to set remember_token cookie.");
		}
	}

	/**
	 * Rotates stored persistent login token
	 *
	 * @param mysqli $link Database connection object
	 * @param int $user_id The id of the user
	 * @param string $selector The selector of the record to be rotated
	 * @param string $token The token of the record to be rotated
	 * @return void
	 * @throws RuntimeException if DB fails or setting cookie fails
	 */
	function executePersistentTokenRotation(mysqli $link, int $user_id, string $selector, string $token): void {

		$oldTokenHash = hash('sha256', $token);

		// Start transaction
		mysqli_begin_transaction($link);
		try
		{
			// Generate new selector and token
			$newToken = bin2hex(random_bytes(32));
			$newTokenHash = hash('sha256', $newToken);

			// Update database
			$sql = "UPDATE " TablePersistentLogins::$tableName .
					" SET " . TablePersistentLogins::$tokenHash . " = ?, " . 
						TablePersistentLogins::$expiresAt . " = NOW() + INTERVAL 1 YEAR" .
					" WHERE " . TablePersistentLogins::$userId . " = ?" . 
					" AND " . TablePersistentLogins::$selector . " = ?" . 
					" AND " . TablePersistentLogins::$tokenHash . " = ?";

			$stmt = mysqli_prepare($link, $sql);
			mysqli_stmt_bind_param(
				$stmt,
				"siss",
				$newTokenHash,
				$user_id,
				$selector,
				$oldTokenHash
			);

			mysqli_stmt_execute($stmt);
			if (mysqli_stmt_affected_rows($stmt) !== 1)
			{
				mysqli_stmt_close($stmt);
				throw new RuntimeException('Token rotation failed: row not found');
			}
			mysqli_stmt_close($stmt);

			// Commit DB changes
			mysqli_commit($link);
		}
		catch (Exception $e)
		{
			// Roll back DB to previous valid state
			mysqli_rollback($link);
			throw $e;
		}

		// Only now touch the cookie
		$cookieValue = $selector . ':' . $newToken;
		$result = setcookie(
			"remember_token",
			$cookieValue,
			time() + (60 * 60 * 24 * 364),
			"/",
			"",
			true,
			true
		);
		if ($result === false)
		{
			// DB is already correct; force re-login later
			throw new RuntimeException('Failed to set rotated remember_token cookie');
		}
	}





	/* SERVICE FUNCTIONS - MOST PAGES */
	/**
	 * Start a session with a hardened cookie.
	 * Must call before any output.
	 * @return void
	 */
	function startSecureSession(): void {

		if (session_status() === PHP_SESSION_NONE) 
		{
			// Detect HTTPS properly
			$isHttps = (
				(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
				$_SERVER['SERVER_PORT'] == 443
			);
			
			// If we don't allow HTTP sessions
			if ($isHttps == false && ALLOW_SESSION_OVER_HTTP == false)
			{
				throw new RuntimeException('HTTPS is required for secure sessions.');
			}

			// Set secure cookie rules
			session_set_cookie_params([
				'lifetime' => 0,
				'path'     => '/',
				'secure'   => $isHttps,
				'httponly' => true,
				'samesite' => 'Strict'
			]);

			// Start the session
			if (!session_start()) 
			{
				throw new RuntimeException('Failed to start a session.');
			}
			
			// Generate CSRF token if not present
			if (empty($_SESSION['csrf_token'])) 
			{
				$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
			}
		}
	}

	/**
	 * Do we have a logged in session or a useless empty one
	 * @return bool True if we are logged in, otherwise false
	 */
	function isSessionEmpty(): bool {
		
		if(!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
		{
			return false;
		} 
		else 
		{
			return true;
		}
	}

	/**
	 * Check if CSRF token is valid and regenerate it if it is valid
	 * @return bool True if the token CSRF is valid, otherwise false
	 */
	function isCsrfTokenValid(): bool {

		if (
			empty($_POST['csrf_token']) ||
			empty($_SESSION['csrf_token']) ||
			!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
		) 
		{
			return false;
		}

		// Rotate token after successful validation
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		return true;
	}

	/**
	 * Open a connection to the DB
	 * @return mysqli The connection object
	 */
	function connectDb(): mysqli {

		static $connection = null;

		if ($connection === null) 
		{
			try
			{
				$connection = new mysqli(
					DB_SERVER_PUBLIC,
					DB_USERNAME_PUBLIC,
					DB_PASSWORD_PUBLIC,
					DB_NAME_PUBLIC
				);
			} 
			catch (mysqli_sql_exception $e) 
			{
				// Log safely (no password exposure)
				error_log('Database connection failed: ' . $e->getMessage());

				// Show generic message
				throw new RuntimeException('Database connection failed.');
			}
		}

		//return
		return $connection;
	}

	/**
	 * Default Exception handler
	 * @param Throwable $ex The exception handle
	 * @return void
	 */
	function catchEx(Throwable $ex): void {

		error_log($ex);
		http_response_code(500);
		if (!IS_PRODUCTION)
		{
			header('Content-Type: text/html; charset=utf-8');
			echo "<pre>" . htmlspecialchars((string)$ex) . "</pre>";
		}
		else
		{
			echo "An internal error occurred.";
		}
		exit;
	}
	