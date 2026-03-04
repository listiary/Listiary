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
	

	// Fetch user data
	$id = getUserId();
	$user_data = fetchUserData($link, $id);
	$user_details = fetchUserDetailsArr($link, $id);


	// Set UI - userspaces links
	$links = '<a class="btn btn--purple">User\'s Contributions</a><br /><a class="btn btn--blue">User\'s  Public Lists</a>';
	
	
	// Set UI - username element
	$username = $user_data['username'];
	$userstar = $usernametail = '';
	$usernameColor = "black";
	if($user_data['is_premium']) 
	{
		$userstar = '✦ ';
		$usernametail = '&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	$user_data['user_role'] = "developer";
	if($user_data['user_role'] !== null)
	{
		if($user_data['user_role'] == 'developer') $usernameColor = "blue";
		else if($user_data['user_role'] == 'contributor') $usernameColor = "green";
		else if($user_data['user_role'] == 'moderator') $usernameColor = "red";
	}
	
	
	//Set UI - status
	$status = '';
	if($user_data['is_active']) $status = 'Active';
	else $status = 'Inactive';
	if($user_data['is_bot']) $status .= ' (Bot)';


	// Set UI - avatar
	$blancAvatar = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgZmlsbD0iIzAwMDAwMCIgdmlld0JveD0iMCAwIDI1NiAyNTYiPjxwYXRoIGQ9Ik0xMjgsMjhBMTAwLDEwMCwwLDEsMCwyMjgsMTI4LDEwMC4xMSwxMDAuMTEsMCwwLDAsMTI4LDI4Wk02OC44NywxOTguNDJhNjgsNjgsMCwwLDEsMTE4LjI2LDAsOTEuOCw5MS44LDAsMCwxLTExOC4yNiwwWm0xMjQuMy01LjU1YTc1LjYxLDc1LjYxLDAsMCwwLTQ0LjUxLTM0LDQ0LDQ0LDAsMSwwLTQxLjMyLDAsNzUuNjEsNzUuNjEsMCwwLDAtNDQuNTEsMzQsOTIsOTIsMCwxLDEsMTMwLjM0LDBaTTEyOCwxNTZhMzYsMzYsMCwxLDEsMzYtMzZBMzYsMzYsMCwwLDEsMTI4LDE1NloiPjwvcGF0aD48L3N2Zz4=";
	$userAvatar = 'avatars/';
	if($user_details['avatar_path'] === null || trim($user_details['avatar_path']) === '') $userAvatar = $blancAvatar;
	else $userAvatar .= $user_details['avatar_path'];

	// Set UI - biography
	$bio = '';
	if($user_details['bio'] !== null || trim($user_details['bio']) !== '')
	{
		$bio = '<div class="bio" style="margin-bottom: 15px; font-style: italic; color: black;">' . 
			$user_details['bio'] . '</div>';
	}

	// Set UI - contacts
	$email = $user_data['email'];
	
	$phone = $user_details['phone1'];
	$phoneField = "<p><strong>Phone:</strong> $phone </p>";
	if($phone === null || trim($phone) === '') $phoneField = '';
	
	$created_at = $user_data['created_at'];
	//$is_active = $user_data['is_active'];
	//$is_bot = $user_data['is_bot'];
	//$is_premium = $user_data['is_premium'];
	
	// Set UI - location
	$adress = '';
	if($user_details['city'] !== null && trim($user_details['city']) !== '') $adress = $user_details['city'];
	if($user_details['country'] !== null && trim($user_details['country']) !== '')
	{
		if(strlen($adress) > 0) $adress .= ', ';
		$adress .= $user_details['country'];
	}
	if(strlen($adress) > 0)
	{
		$adress = '<p class="info-line"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256"><path d="M200,220H160.73c5.18-5,10.75-10.71,16.33-17.13C205.15,170.57,220,136.37,220,104a92,92,0,0,0-184,0c0,50,34.12,91.94,59.18,116H56a12,12,0,0,0,0,24H200a12,12,0,0,0,0-24ZM60,104a68,68,0,0,1,136,0c0,33.31-20,63.37-36.7,82.71A249.35,249.35,0,0,1,128,216.89a249.35,249.35,0,0,1-31.3-30.18C80,167.37,60,137.31,60,104Zm68,44a44,44,0,1,0-44-44A44.05,44.05,0,0,0,128,148Zm0-64a20,20,0,1,1-20,20A20,20,0,0,1,128,84Z"></path></svg><span>' . $adress . '</span></p><br />';
	}
	$timenow = "";
	if($user_details['timezone'] !== null && trim($user_details['timezone']) !== '')
	{
		$t = getTimeNow($user_details['timezone']);
		$timenow = "<p class='info-line'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='#000000' viewBox='0 0 256 256'><path d='M128,20A108,108,0,1,0,236,128,108.12,108.12,0,0,0,128,20Zm0,192a84,84,0,1,1,84-84A84.09,84.09,0,0,1,128,212Zm68-84a12,12,0,0,1-12,12H128a12,12,0,0,1-12-12V72a12,12,0,0,1,24,0v44h44A12,12,0,0,1,196,128Z'></path></svg><span>$t</span></p>";
	}


	// Get current time
	function getTimeNow(string $timezone): string {

		// Convert "UTC+2" or "UTC-8" to a format PHP's DateTimeZone understands
		$tz = str_replace(['UTC+', 'UTC-'], ['Etc/GMT-', 'Etc/GMT+'], $timezone);
		$date = new DateTime("now", new DateTimeZone($tz));
		return $date->format("H:i") . ' (' . formatUtcOffset($timezone) . ')';
	}
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
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>user <?php echo htmlspecialchars($username); ?></title>
		<link rel="stylesheet" href="css/m.user.css">
	</head>
	<body>
		<div class="profile-container">
			<!-- Avatar -->
			<img src="<?php echo $userAvatar; ?>" alt="Avatar" class="avatar">

			<!-- Username -->
			<h1 style="color:<?php echo $usernameColor; ?>;"><?php echo $userstar . htmlspecialchars($user_data['username']) . $usernametail; ?></h1>
			
			<!-- Basic bio -->
			<?php echo $bio; ?>
			<br /><br />

			<!-- Lists -->
			<?php echo $links; ?>
			<br /><br /><br />
			
			<!-- Contact info -->
			<?php echo $adress; ?>
			<?php echo $timenow; ?>
			<br /><br />
			<p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
			<?php echo $phoneField; ?>
			<p><strong>Joined:</strong> <?php echo htmlspecialchars(date("F j, Y", strtotime($created_at))); ?></p>
			<p><strong>Status:</strong> <?php echo $status ?> </p>
			<br /><br />

			<!-- Menu -->
			<a href="<?php echo INDEX_URL; ?>"><strong>Back to Index</strong></a>
		</div>
	</body>
</html>