<?php

	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_sessionlib.php";
	set_exception_handler('catchEx');
	
	// More Presets
	startSecureSession();
	$link = connectDb();
	
	//get user link
	$userUrl = 'm.login.php';
	$userText = 'Log In';
	if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
	{
		$userText = $_SESSION["username"];
		$userUrl = "m.userhome.php";
	}

	// If session empty look for the long login cookie.
	// If available, try to log in with that.
	// If not, we have no business being on the user page, so we redirect to the login page.
	if(isSessionEmpty())
	{
		$result = doRememberedLogin($link);
		if($result === false)
		{			
			//redirect
			header("Location: " . "m.login.php");
			exit;
		}
	}
	
	// Fetch modal to show get param
	$socialModalDisplay = 'none';
	$contactModalDisplay = 'none';
	$locationModalDisplay = 'none';
	$bioModalDisplay = 'none';
	$avatarModalDisplay = 'none';
	if(isset($_GET['showmodal']))
	{
		if($_GET['showmodal'] === "social") $socialModalDisplay = 'block';
		else if($_GET['showmodal'] === 'contact') $contactModalDisplay = 'block';
		else if($_GET['showmodal'] === 'location') $locationModalDisplay = 'block';
		else if($_GET['showmodal'] === 'bio') $bioModalDisplay = 'block';
		else if($_GET['showmodal'] === 'avatar') $avatarModalDisplay = 'block';
	}

	// Fetch user data
	$user_data = fetchUserData($link, $_SESSION['id']);
	$user_details = fetchUserDetailsArr($link, $_SESSION['id']);

	// Set UI - avatar
	$userAvatar = getAvatarHtml($user_details);
	
	// Set UI - username
	$username = $user_data['username'];
	$userstar = getUserstar($user_data);
	$usernameHtml = getUsernameHtml($user_data);
	
	// Set UI - biography
	$bio = getBiographyHtml($user_details);

	// Set UI - fields
	$email = $user_data['email'];
	$phoneField = getPhoneHtml($user_details);
	$created_at = $user_data['created_at'];
	//$is_active = $user_data['is_active'];
	//$is_bot = $user_data['is_bot'];
	//$is_premium = $user_data['is_premium'];
	$address = getAddressHtml($user_details);
	$timenow = getTimeHtml($user_details);
	
	// Socials
	// social fields - the texts shown on the useredit page
	$website_FieldText = ($user_details["link_personal_website"] != null) ? $user_details["link_personal_website"] : "add a link";
	$socialFb_FieldText = ($user_details["link_personal_facebook"] != null) ? $user_details["link_personal_facebook"] : "add a link";
	$socialX_FieldText = ($user_details["link_personal_xcom"] != null) ? $user_details["link_personal_xcom"] : "add a link";
	$socialLi_FieldText = ($user_details["link_personal_linkedin"] != null) ? $user_details["link_personal_linkedin"] : "add a link";
	$socialOther_FieldText = ($user_details["link_personal_other"] != null) ? $user_details["link_personal_other"] : "add a link";
	
	// shorten for mobile
	if(strlen($website_FieldText) > 15) $website_FieldText = "... " . substr($website_FieldText, -15);
	if(strlen($socialFb_FieldText) > 15) $socialFb_FieldText = "... " . substr($socialFb_FieldText, -15);
	if(strlen($socialX_FieldText) > 15) $socialX_FieldText = "... " . substr($socialX_FieldText, -15);
	if(strlen($socialLi_FieldText) > 15) $socialLi_FieldText = "... " . substr($socialLi_FieldText, -15);
	if(strlen($socialOther_FieldText) > 15) $socialOther_FieldText = "... " . substr($socialOther_FieldText, -15);
	
	// the grayed out of unset fields
	$website_Style = ($website_FieldText === 'add a link') ? 'style="color: #ccc;"' : '';
	$socialFb_Style = ($socialFb_FieldText === 'add a link') ? 'style="color: #ccc;"' : '';
	$socialX_Style = ($socialX_FieldText === 'add a link') ? 'style="color: #ccc;"' : '';
	$socialLi_Style = ($socialLi_FieldText === 'add a link') ? 'style="color: #ccc;"' : '';
	$socialOther_Style = ($socialOther_FieldText === 'add a link') ? 'style="color: #ccc;"' : '';

	// social texts - the texts shown in the edit social links modal
	$website = ($user_details["link_personal_website"] != null) ? $user_details["link_personal_website"] : "";
	$socialFb = ($user_details["link_personal_facebook"] != null) ? $user_details["link_personal_facebook"] : "";
	$socialX = ($user_details["link_personal_xcom"] != null) ? $user_details["link_personal_xcom"] : "";
	$socialLi = ($user_details["link_personal_linkedin"] != null) ? $user_details["link_personal_linkedin"] : "";
	$socialOther = ($user_details["link_personal_other"] != null) ? $user_details["link_personal_other"] : "";
	
	// error texts - the errors shown in the edit social links modal
	$website_err = "";
	$socialFb_err = "";
	$socialX_err = "";
	$socialLi_err = "";
	$socialOther_err = "";
	
	
	// Processing form data when form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Verify CSRF token
		if(isCsrfTokenValid() == false)
		{
			http_response_code(403);
			throw new RuntimeException('Invalid CSRF token.');
		}
		if ($_POST['form_type'] === 'social') 
		{
			$post_website = $_POST['website'];
			$post_socialFb = $_POST['facebook'];
			$post_socialX = $_POST['xcom'];
			$post_socialLi = $_POST['linkedin'];
			$post_socialOther = $_POST['other'];

			
			// handle users website
			$post_website = trim($_POST['website'] ?? '');
			if ($post_website !== $website) {
				if($post_website !== '')
				{
					$sanitized = filter_var($post_website, FILTER_SANITIZE_URL);
					$website = $sanitized;
					if (!filter_var($sanitized, FILTER_VALIDATE_URL)) $website_err = 'Invalid URL';
				}
				else $website = '';
			}
			
			// handle users Facebook
			$post_socialFb = trim($_POST['facebook'] ?? '');
			if ($post_socialFb !== $socialFb) {
				if($post_socialFb !== '')
				{
					$sanitized = filter_var($post_socialFb, FILTER_SANITIZE_URL);
					$socialFb = $sanitized;
					if (!filter_var($sanitized, FILTER_VALIDATE_URL)) $socialFb_err = 'Invalid URL';
				}
				else $socialFb = '';
			}
			
			// handle users X.com
			$post_socialX = trim($_POST['xcom'] ?? '');
			if ($post_socialX !== $socialX) {
				if($post_socialX !== '')
				{
					$sanitized = filter_var($post_socialX, FILTER_SANITIZE_URL);
					$socialX = $sanitized;
					if (!filter_var($sanitized, FILTER_VALIDATE_URL)) $socialX_err = 'Invalid URL';
				}
				else $socialX = '';
			}
			
			// handle users LinkedIn
			$post_socialLi = trim($_POST['linkedin'] ?? '');
			if ($post_socialLi !== $socialLi) {
				if($post_socialLi !== '')
				{
					$sanitized = filter_var($post_socialLi, FILTER_SANITIZE_URL);
					$socialLi = $sanitized;
					if (!filter_var($sanitized, FILTER_VALIDATE_URL)) $socialLi_err = 'Invalid URL';
				}
				else $socialLi = '';
			}
			
			// handle users Other Social Media
			$post_socialOther = trim($_POST['other'] ?? '');
			if ($post_socialOther !== $socialOther) {
				if($post_socialOther !== '')
				{
					$sanitized = filter_var($post_socialOther, FILTER_SANITIZE_URL);
					$socialOther = $sanitized;
					if (!filter_var($sanitized, FILTER_VALIDATE_URL)) $socialOther_err = 'Invalid URL';
				}
				else $socialOther = '';
			}

			// Check input errors before inserting in database
			if(empty($website_err) && empty($socialFb_err) && empty($socialX_err) && empty($socialLi_err) && empty($socialOther_err))
			{
				$res = saveSocialLinksChanges($link, $_SESSION['id'], $website, $socialFb, $socialX, $socialLi, $socialOther);
				if(!$res) throw new RuntimeException('Oops. Something went wrong.');

				// Redirect to the same page
				header("location: m.useredit.php?id=" . $_SESSION['id']);
				exit;
			}
		}
		else if ($_POST['form_type'] === 'contact') 
		{
			// process form
		}
		else if ($_POST['form_type'] === 'location') 
		{
			// process form
		}
		else if ($_POST['form_type'] === 'bio') 
		{
			// process form
		}
		else if ($_POST['form_type'] === 'avatar') 
		{
			// process form
		}
		else
		{
			http_response_code(403);
			throw new RuntimeException('Invalid submit form.');
		}
	}
	
	


	// Update values in DB for the social links
	function saveSocialLinksChanges(mysqli $link, int $user_id,
		string $website, string $socialFb, string $socialX, string $socialLi, string $socialOther): bool {

		// Normalize inputs (empty or whitespace -> NULL)
		$website      = trim($website)      !== '' ? trim($website)      : null;
		$socialFb     = trim($socialFb)     !== '' ? trim($socialFb)     : null;
		$socialX      = trim($socialX)      !== '' ? trim($socialX)      : null;
		$socialLi     = trim($socialLi)     !== '' ? trim($socialLi)     : null;
		$socialOther  = trim($socialOther)  !== '' ? trim($socialOther)  : null;

		$sql = "UPDATE account_details
				SET link_personal_website = ?,
					link_personal_facebook = ?,
					link_personal_xcom = ?,
					link_personal_linkedin = ?,
					link_personal_other = ?
				WHERE account_id = ?
				LIMIT 1";

		$stmt = mysqli_prepare($link, $sql);
		if (!$stmt) {
			throw new RuntimeException('DB prepare failed for social links update');
		}

		mysqli_stmt_bind_param(
			$stmt,
			"sssssi",
			$website,
			$socialFb,
			$socialX,
			$socialLi,
			$socialOther,
			$user_id
		);

		$ok = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);

		if (!$ok) {
			throw new RuntimeException('DB execute failed for social links update');
		}

		return true;
	}


	// Get current time
	function getTimeNow(string $timezone): string {

		// Convert "UTC+2" or "UTC-8" to a format PHP's DateTimeZone understands
		$tz = str_replace(['UTC+', 'UTC-'], ['Etc/GMT-', 'Etc/GMT+'], $timezone);
		$date = new DateTime("now", new DateTimeZone($tz));
		return $date->format("H:i") . ' (' . formatUtcOffset($timezone) . ')';
	}
	
	// Format a timezone string
	function formatUtcOffset(string $timezone): string {

		// Match the sign (+/-) and the hour
		if (!preg_match('/UTC([+-]?)(\d{1,2})$/', $timezone, $matches))
		{
			throw new InvalidArgumentException("Invalid timezone format: $timezone");
		}

		$sign = $matches[1] === '' ? '+' : $matches[1]; // default to '+'
		$hour = (int)$matches[2];
		$hourPadded = str_pad($hour, 2, '0', STR_PAD_LEFT);

		return "UTC {$sign}{$hourPadded}:00";
	}

	// Creates the HTML for the address line
	function getAddressHtml(array $user_details): string {
		
		$address = '';
		if($user_details['city'] !== null && trim($user_details['city']) !== '') $address = $user_details['city'];
		if($user_details['country'] !== null && trim($user_details['country']) !== '')
		{
			if(strlen($address) > 0) $address .= ', ';
			$address .= $user_details['country'];
		}
		if(strlen($address) > 0)
		{
			$address = '<div class="field-wrappar"><p class="info-line"><span class="addr-span"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256"><path d="M200,220H160.73c5.18-5,10.75-10.71,16.33-17.13C205.15,170.57,220,136.37,220,104a92,92,0,0,0-184,0c0,50,34.12,91.94,59.18,116H56a12,12,0,0,0,0,24H200a12,12,0,0,0,0-24ZM60,104a68,68,0,0,1,136,0c0,33.31-20,63.37-36.7,82.71A249.35,249.35,0,0,1,128,216.89a249.35,249.35,0,0,1-31.3-30.18C80,167.37,60,137.31,60,104Zm68,44a44,44,0,1,0-44-44A44.05,44.05,0,0,0,128,148Zm0-64a20,20,0,1,1-20,20A20,20,0,0,1,128,84Z"></path></svg><span>' . $address . "</span></span></p><div class='field-badge2'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='#777' style='display:inline-block; vertical-align:middle; position: relative; top: -1px;' viewBox='0 0 256 256'><path d='M230.15,70.54,185.46,25.86a20,20,0,0,0-28.28,0L33.86,149.17A19.86,19.86,0,0,0,28,163.31V208a20,20,0,0,0,20,20H216a12,12,0,0,0,0-24H125L230.15,98.83A20,20,0,0,0,230.15,70.54ZM91,204H52V165l84-84,39,39ZM192,103,153,64l18.34-18.34,39,39Z'></path></svg></div></div><br />";
		}
		return $address;
	}
	
	// Creates the HTML for the current time line
	function getTimeHtml(array $user_details): string {
		
		$timenow = '';
		if($user_details['timezone'] !== null && trim($user_details['timezone']) !== '')
		{
			$t = getTimeNow($user_details['timezone']);
			$timenow = "<div class='field-wrappar' style='margin-top:6px;'><p class='info-line'><span class='time-span'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='#000000' viewBox='0 0 256 256'><path d='M128,20A108,108,0,1,0,236,128,108.12,108.12,0,0,0,128,20Zm0,192a84,84,0,1,1,84-84A84.09,84.09,0,0,1,128,212Zm68-84a12,12,0,0,1-12,12H128a12,12,0,0,1-12-12V72a12,12,0,0,1,24,0v44h44A12,12,0,0,1,196,128Z'></path></svg><span>$t</span></span></p><div class='field-badge2'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='#777' style='display:inline-block; vertical-align:middle; position: relative; top: -1px;' viewBox='0 0 256 256'><path d='M230.15,70.54,185.46,25.86a20,20,0,0,0-28.28,0L33.86,149.17A19.86,19.86,0,0,0,28,163.31V208a20,20,0,0,0,20,20H216a12,12,0,0,0,0-24H125L230.15,98.83A20,20,0,0,0,230.15,70.54ZM91,204H52V165l84-84,39,39ZM192,103,153,64l18.34-18.34,39,39Z'></path></svg></div></div><br />";
		}
		return $timenow;
	}
	
	// Creates the HTML for the phone
	function getPhoneHtml(array $user_details): string {
		
		$phone = $user_details['phone1'];
		$phoneField = "<p><strong>Phone:</strong> $phone </p>";
		if($phone === null || trim($phone) === '') $phoneField = "Add a phone number";
		return $phoneField;
	}
	
	// Creates the HTML for the biography
	function getBiographyHtml(array $user_details): string {
		
		$bio = '';
		if($user_details['bio'] !== null || trim($user_details['bio']) !== '')
		{
			$bio = '<div class="bio" style="padding-bottom: 0px; font-style: italic; color: black;"><span class="bio-span">' . 
				$user_details['bio'] . '</span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#777" style="display:inline-block; vertical-align:bottom; position: relative; bottom: -3px;"viewBox="0 0 256 256"><path d="M230.15,70.54,185.46,25.86a20,20,0,0,0-28.28,0L33.86,149.17A19.86,19.86,0,0,0,28,163.31V208a20,20,0,0,0,20,20H216a12,12,0,0,0,0-24H125L230.15,98.83A20,20,0,0,0,230.15,70.54ZM91,204H52V165l84-84,39,39ZM192,103,153,64l18.34-18.34,39,39Z"></path></svg></div>';
		}
		return $bio;
	}

	// Creates the HTML for the avatar
	function getAvatarHtml(array $user_details): string {
		
		$blancAvatar = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgZmlsbD0iIzAwMDAwMCIgdmlld0JveD0iMCAwIDI1NiAyNTYiPjxwYXRoIGQ9Ik0xMjgsMjhBMTAwLDEwMCwwLDEsMCwyMjgsMTI4LDEwMC4xMSwxMDAuMTEsMCwwLDAsMTI4LDI4Wk02OC44NywxOTguNDJhNjgsNjgsMCwwLDEsMTE4LjI2LDAsOTEuOCw5MS44LDAsMCwxLTExOC4yNiwwWm0xMjQuMy01LjU1YTc1LjYxLDc1LjYxLDAsMCwwLTQ0LjUxLTM0LDQ0LDQ0LDAsMSwwLTQxLjMyLDAsNzUuNjEsNzUuNjEsMCwwLDAtNDQuNTEsMzQsOTIsOTIsMCwxLDEsMTMwLjM0LDBaTTEyOCwxNTZhMzYsMzYsMCwxLDEsMzYtMzZBMzYsMzYsMCwwLDEsMTI4LDE1NloiPjwvcGF0aD48L3N2Zz4=";
		$userAvatar = 'avatars/';
		if($user_details['avatar_path'] === null || trim($user_details['avatar_path']) === '') $userAvatar = $blancAvatar;
		else $userAvatar .= $user_details['avatar_path'];
		
		return $userAvatar;
	}

	// Creates the HTML for the username
	function getUsernameHtml(array $user_data): string {
		
		$username = $user_data['username'];
		$userstar = $usernametail = '';
		$usernameColor = "black";
		if($user_data['is_premium']) 
		{
			$userstar = '✦ ';
			$usernametail = '';
		}
		$user_data['user_role'] = "developer";
		if($user_data['user_role'] !== null)
		{
			if($user_data['user_role'] == 'developer') $usernameColor = "blue";
			else if($user_data['user_role'] == 'contributor') $usernameColor = "green";
			else if($user_data['user_role'] == 'moderator') $usernameColor = "red";
		}
		
		$usernameHtml = '<h1 class="username" style="color:' . $usernameColor . '"><span class="username-span">' . $userstar . htmlspecialchars($user_data['username']) . '</span>' . $usernametail . '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#777" style="display:inline-block; vertical-align:bottom; position: relative; bottom: -3px;"viewBox="0 0 256 256"><path d="M230.15,70.54,185.46,25.86a20,20,0,0,0-28.28,0L33.86,149.17A19.86,19.86,0,0,0,28,163.31V208a20,20,0,0,0,20,20H216a12,12,0,0,0,0-24H125L230.15,98.83A20,20,0,0,0,230.15,70.54ZM91,204H52V165l84-84,39,39ZM192,103,153,64l18.34-18.34,39,39Z"></path></svg></h1>';
		return $usernameHtml;
	}
	
	// Returns string with star or no star
	function getUserstar(array $user_data): string {
		
		$userstar = '';
		if($user_data['is_premium']) 
		{
			$userstar = '✦';
		}
		return $userstar;
	}

	// Creates the HTML for pencil badge / icon
	function getBorderlessBadge(): string {
		
		$badge = '<div class="field-badge"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#777" style="display:inline-block; vertical-align:middle; position: relative; top: -1px;" viewBox="0 0 256 256"><path d="M230.15,70.54,185.46,25.86a20,20,0,0,0-28.28,0L33.86,149.17A19.86,19.86,0,0,0,28,163.31V208a20,20,0,0,0,20,20H216a12,12,0,0,0,0-24H125L230.15,98.83A20,20,0,0,0,230.15,70.54ZM91,204H52V165l84-84,39,39ZM192,103,153,64l18.34-18.34,39,39Z"></path></svg></div>';
		return $badge;
	}

	// Creates the HTML for the avatar badge / icon
	function getAvatarBadge(): string {
		
		$badge = '<div class="avatar-badge"><!-- <svg height="16" width="16" viewBox="0 0 16 16" fill="currentColor" style="display:inline-block; vertical-align:middle;"><path d="M11.013 1.427a1.75 1.75 0 0 1 2.474 0l1.086 1.086a1.75 1.75 0 0 1 0 2.474l-8.61 8.61c-.21.21-.47.364-.756.445l-3.251.93a.75.75 0 0 1-.927-.928l.929-3.25c.081-.286.235-.547.445-.758l8.61-8.61Zm.176 4.823L9.75 4.81l-6.286 6.287a.253.253 0 0 0-.064.108l-.558 1.953 1.953-.558a.253.253 0 0 0 .108-.064Zm1.238-3.763a.25.25 0 0 0-.354 0L10.811 3.75l1.439 1.44 1.263-1.263a.25.25 0 0 0 0-.354Z"></path></svg> --><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" style="display:inline-block; vertical-align:middle; position: relative; top: -1px;" viewBox="0 0 256 256"><path d="M230.15,70.54,185.46,25.86a20,20,0,0,0-28.28,0L33.86,149.17A19.86,19.86,0,0,0,28,163.31V208a20,20,0,0,0,20,20H216a12,12,0,0,0,0-24H125L230.15,98.83A20,20,0,0,0,230.15,70.54ZM91,204H52V165l84-84,39,39ZM192,103,153,64l18.34-18.34,39,39Z"></path></svg>Edit</div>';

		return $badge;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>user <?php echo htmlspecialchars($username); ?> | Edit Profile</title>
		<link rel="stylesheet" href="css/m.useredit.css">
		<link rel="stylesheet" href="css/m.navigatedpage.css">
		<script type="text/javascript" src="js/m.useredit.js"></script>
		<script type="text/javascript" src="js/scripts.dom-manipulate.js"></script>
	</head>
	<body>
	
		<!-- The hamburger menu -->
		<div id="sidenav" >

			<!-- This is the menu 'X' button -->
			<a href="javascript: closeNav();" class="closebtn"
			style="position: absolute; top: 0; right: 0; font-size: 36px; margin-right: 10px;
			padding: 8px; text-decoration: none; color: #818181; display: block; transition: 0.3s;">
				<svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' style='margin-top:14px;' viewBox='0 0 25 25'>
					<path d='M0,0 L16,16' stroke='#818181' stroke-width='2' fill='none'/>
					<path d='M0,16 L16,0' stroke='#818181' stroke-width='2' fill='none'/>
				</svg>
			</a>
		

			<!-- MenuMain -->
			<a id="userlink" href="<?php echo $userUrl; ?>" class="MenuMain"><?php echo $userText; ?></a>
			<hr id="MenuMainMiddleAnchor" style="margin: 22px;" class="MenuMain"/>
			<a href="../m.index.php" class="MenuMain">Wiki Index</a>
			<a href="m.usersindex.php" class="MenuMain">Users Index</a>
			<a href="#" target="_blank" class="MenuMain">About</a>
			<a href="https://forums.listiary.org/" target="_blank" class="MenuMain">Forums</a>
			<a href="../contact/mobile/contact.php" class="MenuMain">Contact us</a>
			<a href="../contact/mobile/reportbug.php" class="MenuMain">Report a bug</a>
			<a href="../contact/mobile/donate.php" class="MenuMain">Donate</a>
			<br class="MenuMain">
			<hr id="MenuMainBottomAnchor" style="margin: 22px;" class="MenuMain"/>
			<a href="../docs/m.terms_of_service.php" target="_blank" class="MenuMain">Terms of Use</a>
			<a href="../docs/m.privacy_policy.php" target="_blank" class="MenuMain">Privacy Policy</a>
			<a href="https://library.listiary.org/" target="_blank" class="MenuMain">Describe Library</a>
			<a href="https://documentation.listiary.org/" target="_blank" class="MenuMain">Describe Docs</a>
			<a href="https://github.com/viktorchernev/DescribeCompiler" target="_blank" class="MenuMain">Describe Repo</a>
		</div>

		<!-- Triggers -->
		<span id="sidenav-trigger" onclick="openNav();">&#9776;</span>
		
		<!-- Add Social Media Links Modal aka 'socialModal' - for phones -->
		<div id="socialModal" style="display: <?php echo $socialModalDisplay; ?>; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px);">
			
			<form method="post" action="m.useredit.php?id=<?php echo $_SESSION['id']; ?>&showmodal=social">
			<div style="background-color: #fff; margin: 55px auto; padding: 0px; border-radius: 8px; width: 92%; max-width: 800px; height: 80vh; overflow-y: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.3); position: relative; display: flex; flex-direction: column;">
				
				<!-- Header Bar -->
				<div style="background-color: #99B3FF; padding: 15px 40px 15px 20px; border-bottom: 1px solid #88a3ef; position: sticky; top: 0; z-index: 10;">
					<h2 style="margin: 0; font-size: 1.5rem;">Edit Social Links</h2>
					<span onclick="hideSocialModal();" style="position: absolute; right: 20px; top: 12px; font-size: 28px; font-weight: bold; cursor: pointer; color: #333;">&times;</span>
				</div>
				
				<!-- The Content Area -->
				<div id="modalInnerContent" style="padding: 0px 20px; flex-grow: 1; display: flex; flex-direction: column;">
				<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
				<input type="hidden" name="form_type" value="social">
					<br />
					<div class="form-group">
						<label>Personal Website</label>
						<input type="text" name="website" 
							   class="<?php echo (!empty($website_err)) ? 'is-invalid' : ''; ?>" 
							   value="<?php echo htmlspecialchars($website); ?>"
							   placeholder="Add a link">
						<span class="invalid-feedback"><?php echo $website_err; ?></span>
					</div>
					<div class="form-group">
						<label>Facebook</label>
						<input type="text" name="facebook" 
							   class="<?php echo (!empty($socialFb_err)) ? 'is-invalid' : ''; ?>" 
							   value="<?php echo htmlspecialchars($socialFb); ?>"
							   placeholder="Add Facebook">
						<span class="invalid-feedback"><?php echo $socialFb_err; ?></span>
					</div>
					<div class="form-group">
						<label>X.com</label>
						<input type="text" name="xcom" 
							   class="<?php echo (!empty($socialX_err)) ? 'is-invalid' : ''; ?>" 
							   value="<?php echo htmlspecialchars($socialX); ?>"
							   placeholder="Add X.com">
						<span class="invalid-feedback"><?php echo $socialX_err; ?></span>
					</div>
					<div class="form-group">
						<label>Linked in</label>
						<input type="text" name="linkedin" 
							   class="<?php echo (!empty($socialLi_err)) ? 'is-invalid' : ''; ?>" 
							   value="<?php echo htmlspecialchars($socialLi); ?>"
							   placeholder="Add LinkedIn">
						<span class="invalid-feedback"><?php echo $socialLi_err; ?></span>
					</div>
					<div class="form-group">
						<label>Other Links</label>
						<input type="text" name="other" 
							   class="<?php echo (!empty($socialOther_err)) ? 'is-invalid' : ''; ?>" 
							   value="<?php echo htmlspecialchars($socialOther); ?>"
							   placeholder="Add another link">
						<span class="invalid-feedback"><?php echo $socialOther_err; ?></span>
					</div>
				</div>

				<!-- Action Buttons -->
				<div style="position: sticky; bottom: 0; background-color: #f9f9f9; padding: 15px; text-align: center; border-top: 1px solid #eee; z-index: 10;">
					<button type="button" onclick="hideSocialModal();" style="padding: 8px 16px; margin-right: 10px; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; background: white;">Cancel</button>
					<button type="submit" style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Save</button>
				</div>
			</div>
			</form>
		</div>

		<!-- Main page skeleton -->
		<table id="skeleton">
			<tbody>
				<tr>
					<td colspan="10" id="LargeTopContainer" 
						style="background: linear-gradient(rgb(153, 179, 255) 0%, rgb(153, 179, 255) calc(60% + 1px), transparent calc(60% + 2px));">
						<div style="left: 0; bottom: 0; width: 100%;">
							<div style="font-size: 25px; margin: 7px; margin-left: 15px; padding: 10px; margin-top: 19px; padding-left: 0px; padding-top: 0px;">
								<div style="margin-right:35px;">
									<a class="breadcrumbmain" style="display: inline;" href="m.userhome.php">User Profile</a>
									<span class="breadcrumbmain" style="display: inline;"> . </span>
									<a class="breadcrumbmain" href="m.useredit.php?id=<?php echo $_SESSION['id'] ?>" style="display: inline;">Edit</a>
								</div>
								<hr>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="10" id="LargeContainer">
						<div class="profile-container">
							<!-- Avatar -->
							<!-- <img src="<?php echo $userAvatar; ?>" alt="Avatar" class="avatar"> -->	
							<div class="avatar-wrapper">
								<img src="<?php echo $userAvatar; ?>" alt="Avatar" class="avatar">
								<?php echo getAvatarBadge(); ?>
							</div>

							<!-- Username -->
							<?php echo $usernameHtml; ?>
							
							<!-- Basic bio -->
							<div class="bio-wrapper">
							<?php echo $bio; ?>
							</div>
							<br /><br />
							
							<!-- Contact info -->
							<?php echo $address; ?>
							<?php echo $timenow; ?>
							<br />
							<div class="field-wrappar">
								<p><span class="email-span"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></span></p>
								<?php echo getBorderlessBadge(); ?>
							</div><br />
							<div class="field-wrappar">
								<p><span class="phone-span"><strong>Phone:</strong> <?php echo htmlspecialchars($phoneField); ?></span></p>
								<?php echo getBorderlessBadge(); ?>
							</div><br />
							<div class="field-wrappar" onclick="showSocialModal();">
								<p><span class="website-span" <?php echo $website_Style; ?>><strong>Website:</strong> <?php echo htmlspecialchars($website_FieldText); ?></span></p>
								<?php echo getBorderlessBadge(); ?>
							</div><br />
							<div class="field-wrappar" onclick="showSocialModal();">
								<p><span class="fb-span" <?php echo $socialFb_Style; ?>><strong>Facebook:</strong> <?php echo htmlspecialchars($socialFb_FieldText); ?></span></p>
								<?php echo getBorderlessBadge(); ?>
							</div><br />
							<div class="field-wrappar" onclick="showSocialModal();">
								<p><span class="xcom-span" <?php echo $socialX_Style; ?>><strong>Twitter/X:</strong> <?php echo htmlspecialchars($socialX_FieldText); ?></span></p>
								<?php echo getBorderlessBadge(); ?>
							</div><br />
							<div class="field-wrappar" onclick="showSocialModal();">
								<p><span class="linkedin-span" <?php echo $socialLi_Style; ?>><strong>LinkedIn:</strong> <?php echo htmlspecialchars($socialLi_FieldText); ?></span></p>
								<?php echo getBorderlessBadge(); ?>
							</div><br />
							<div class="field-wrappar" onclick="showSocialModal();">
								<p><span class="othermedia-span" <?php echo $socialOther_Style; ?>><strong>Other Social Media:</strong> <?php echo htmlspecialchars($socialOther_FieldText); ?></span></p>
								<?php echo getBorderlessBadge(); ?>
							</div>
							<br /><br /><br />

							<!-- Menu -->
							<a href="m.userhome.php"><strong>Back</strong></a><br />
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>