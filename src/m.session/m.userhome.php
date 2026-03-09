<?php

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_sessionlib.php";
	set_exception_handler('catchEx');

	// More Presets
	startSecureSession();
	$link = connectDb();
	
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
	
	// Fetch user data
	$id = $_SESSION['id'];
	$user_data = fetchUserData($link, $id);
	$user_details = fetchUserDetailsArr($link, $id);
	fetchUserDetails($link, $_SESSION['id']);			//redundant
	
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
	
	// Get data from session into variables that will be showed on our page
	$user_id = $_SESSION['id'];
	$email = $_SESSION['email'];
	$usercode = $_SESSION['usercode'];
	$is_bot = $_SESSION['is_bot'];
	$is_active = $_SESSION['is_active'];
	$is_premium = $_SESSION['is_premium'];
	$created_at = $_SESSION['created_at'];
	$avatar_path = $_SESSION['avatar_path'];
	$avatar_shape = $_SESSION['avatar_shape']; 
	$avatar_shape_radius = $_SESSION['avatar_shape_radius']; 
	$bio = $_SESSION['bio'];
	$city = $_SESSION['city']; 
	$country = $_SESSION['country']; 
	$timezone = $_SESSION['timezone']; 
	$phone1 = $_SESSION['phone1'];

	// Set UI - userspaces links
	$links = '<a class="btn btn--purple">Contributions</a><br /><a class="btn btn--blue">My Public Lists</a><br /><a class="btn btn--green">My Personal Lists</a>';
	if($is_premium) $links .= '<br /><a class="btn btn--orange">My Private Lists</a>';
	//var_dump($is_premium); die();

	// Set UI - avatar
	$blancAvatar = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgZmlsbD0iIzAwMDAwMCIgdmlld0JveD0iMCAwIDI1NiAyNTYiPjxwYXRoIGQ9Ik0xMjgsMjhBMTAwLDEwMCwwLDEsMCwyMjgsMTI4LDEwMC4xMSwxMDAuMTEsMCwwLDAsMTI4LDI4Wk02OC44NywxOTguNDJhNjgsNjgsMCwwLDEsMTE4LjI2LDAsOTEuOCw5MS44LDAsMCwxLTExOC4yNiwwWm0xMjQuMy01LjU1YTc1LjYxLDc1LjYxLDAsMCwwLTQ0LjUxLTM0LDQ0LDQ0LDAsMSwwLTQxLjMyLDAsNzUuNjEsNzUuNjEsMCwwLDAtNDQuNTEsMzQsOTIsOTIsMCwxLDEsMTMwLjM0LDBaTTEyOCwxNTZhMzYsMzYsMCwxLDEsMzYtMzZBMzYsMzYsMCwwLDEsMTI4LDE1NloiPjwvcGF0aD48L3N2Zz4=";
	$userAvatar = 'avatars/';
	if($avatar_path === null || trim($avatar_path) === '') $userAvatar = $blancAvatar;
	else $userAvatar .= $avatar_path;

	// Set UI - biography
	if($bio !== null || trim($bio) !== '') $bio = '<div class="bio" style="margin-bottom: 15px; font-style: italic; color: black;">' . $bio . '</div>';
	else $bio = '';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Profile – <?php echo htmlspecialchars($username); ?></title>
		<link rel="stylesheet" href="css/m.user.css">
	</head>
	<body>
		<div class="profile-container">
			<!-- Avatar -->
			<!-- <img src="avatars/snail.jpg" alt="Avatar" class="avatar"> -->
			<img src="<?php echo $userAvatar; ?>" alt="Avatar" class="avatar">

			<!-- Username -->
			<h1 style="color:<?php echo $usernameColor; ?>;"><?php echo $userstar . htmlspecialchars($user_data['username']) . $usernametail; ?></h1>
			
			<!-- Basic bio -->
			<?php echo $bio; ?>
			<br /><br />

			<!-- Lists -->
			<?php echo $links; ?>
			<br /><br /><br /><br />

			<!-- Menu -->
			<a href="<?php echo rtrim(BASE_URL, '/') . '/m.session/m.userpreview.php?id=' . $_SESSION['id']; ?>"><strong>Preview</strong></a><br />
			<a href="m.usersettings.php?id=<?php echo $_SESSION['id']; ?>"><strong>Settings</strong></a><br />
			<a href="m.useredit.php?id=<?php echo $_SESSION['id']; ?>"><strong>Edit Account</strong></a><br />
			<a href="php/_logout_device.php"><strong>Log Out</strong></a><br />
		</div>
	</body>
</html>