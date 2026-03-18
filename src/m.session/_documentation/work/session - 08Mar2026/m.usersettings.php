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

	// Set UI - avatar
	$blancAvatar = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgZmlsbD0iIzAwMDAwMCIgdmlld0JveD0iMCAwIDI1NiAyNTYiPjxwYXRoIGQ9Ik0xMjgsMjhBMTAwLDEwMCwwLDEsMCwyMjgsMTI4LDEwMC4xMSwxMDAuMTEsMCwwLDAsMTI4LDI4Wk02OC44NywxOTguNDJhNjgsNjgsMCwwLDEsMTE4LjI2LDAsOTEuOCw5MS44LDAsMCwxLTExOC4yNiwwWm0xMjQuMy01LjU1YTc1LjYxLDc1LjYxLDAsMCwwLTQ0LjUxLTM0LDQ0LDQ0LDAsMSwwLTQxLjMyLDAsNzUuNjEsNzUuNjEsMCwwLDAtNDQuNTEsMzQsOTIsOTIsMCwxLDEsMTMwLjM0LDBaTTEyOCwxNTZhMzYsMzYsMCwxLDEsMzYtMzZBMzYsMzYsMCwwLDEsMTI4LDE1NloiPjwvcGF0aD48L3N2Zz4=";
	$userAvatar = 'avatars/';
	if($user_details['avatar_path'] === null || trim($user_details['avatar_path']) === '') $userAvatar = $blancAvatar;
	else $userAvatar .= $user_details['avatar_path'];
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Settings - user <?php echo htmlspecialchars($username); ?></title>
		<link rel="stylesheet" href="css/m.user.css">
	</head>
	<body>
		<div class="profile-container">
			<!-- Avatar -->
			<img src="<?php echo $userAvatar; ?>" alt="Avatar" class="avatar">

			<!-- Username -->
			<h1 style="color:<?php echo $usernameColor; ?>;"><?php echo $userstar . htmlspecialchars($user_data['username']) . $usernametail; ?></h1>
			<br /><br />

			<p><label><input type="checkbox" checked> <strong>Lorem:</strong> ipsum mmsam</label></p>
			<p><label><input type="checkbox" checked> <strong>Ipsum:</strong> dolor sit amet</label></p>
			<p><label><input type="checkbox"> <strong>Dolor:</strong> consectetur adipiscing</label></p>
			<p><label><input type="checkbox" checked> <strong>Sit:</strong> sed do eiusmod</label></p>
			<p><label><input type="checkbox"> <strong>Amet:</strong> tempor incididunt</label></p>
			<p><label><input type="checkbox"> <strong>Elit:</strong> ut labore et dolore</label></p>
			<p><label><input type="checkbox" checked> <strong>Sed:</strong> magna aliqua</label></p>
			<p><label><input type="checkbox"> <strong>Do:</strong> enim ad minim</label></p>
			<p><label><input type="checkbox"> <strong>Eiusmod:</strong> quis nostrud exercitation</label></p>
			<p><label><input type="checkbox"> <strong>Irure:</strong> dolor in reprehenderit</label></p>
			<p><label><input type="checkbox" checked> <strong>Reprehenderit:</strong> voluptate velit esse</label></p>
			<p><label><input type="checkbox" checked> <strong>Velit:</strong> cillum dolore eu</label></p>
			<p><label><input type="checkbox" checked> <strong>Cillum:</strong> fugiat nulla pariatur</label></p>

			<br /><br />
			<!-- Menu -->
			<a href="m.userhome.php?id=<?php echo $_SESSION['id']; ?>"><strong>Back</strong></a>
		</div>
	</body>
</html>