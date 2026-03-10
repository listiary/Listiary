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
	if($user_data['is_premium']) 
	{
		$userstar = '✦ ';
		$usernametail = '&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	
	$usernameColor = getUserColor($user_data);
	
	// Get data from session into variables that will be showed on our page
	// $user_id = $_SESSION['id'];
	// $email = $_SESSION['email'];
	// $usercode = $_SESSION['usercode'];
	// $is_bot = $_SESSION['is_bot'];
	// $is_active = $_SESSION['is_active'];
	// $is_premium = $_SESSION['is_premium'];
	// $created_at = $_SESSION['created_at'];
	// $avatar_path = $_SESSION['avatar_path'];
	// $avatar_shape = $_SESSION['avatar_shape']; 
	// $avatar_shape_radius = $_SESSION['avatar_shape_radius']; 
	// $bio = $_SESSION['bio'];
	// $city = $_SESSION['city']; 
	// $country = $_SESSION['country']; 
	// $timezone = $_SESSION['timezone']; 
	// $phone1 = $_SESSION['phone1'];

	// Set UI - userspaces links
	$links = getLinksHtml();

	// Set UI - avatar
	$userAvatar = getAvatarHtml();

	// Set UI - biography
	$bio = getBiographyHtml();


	// Get the CSS color for the username
	function getUserColor(array $user_data): string {
		
		$usernameColor = "black";
		$user_data['user_role'] = "developer";
		
		if($_SESSION['is_active'] == false)
		{
			$usernameColor = "#ccc";
		}			
		else if($user_data['user_role'] !== null)
		{
			if($user_data['user_role'] == 'developer') $usernameColor = "blue";
			else if($user_data['user_role'] == 'contributor') $usernameColor = "green";
			else if($user_data['user_role'] == 'moderator') $usernameColor = "red";
		}
		
		return $usernameColor;
	}

	// Creates the HTML for the links for userspaces
	function getLinksHtml(): string {
		
		$links = '<a class="btn btn--purple">Contributions</a><br /><a class="btn btn--blue">My Public Lists</a><br /><a class="btn btn--green">My Personal Lists</a>';
		if($_SESSION['is_premium']) $links .= '<br /><a class="btn btn--orange">My Private Lists</a>';
		
		//var_dump($is_premium); die();
		return $links;
	}

	// Creates the HTML for the avatar
	function getAvatarHtml(): string {

		$blancAvatar = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgZmlsbD0iIzAwMDAwMCIgdmlld0JveD0iMCAwIDI1NiAyNTYiPjxwYXRoIGQ9Ik0xMjgsMjhBMTAwLDEwMCwwLDEsMCwyMjgsMTI4LDEwMC4xMSwxMDAuMTEsMCwwLDAsMTI4LDI4Wk02OC44NywxOTguNDJhNjgsNjgsMCwwLDEsMTE4LjI2LDAsOTEuOCw5MS44LDAsMCwxLTExOC4yNiwwWm0xMjQuMy01LjU1YTc1LjYxLDc1LjYxLDAsMCwwLTQ0LjUxLTM0LDQ0LDQ0LDAsMSwwLTQxLjMyLDAsNzUuNjEsNzUuNjEsMCwwLDAtNDQuNTEsMzQsOTIsOTIsMCwxLDEsMTMwLjM0LDBaTTEyOCwxNTZhMzYsMzYsMCwxLDEsMzYtMzZBMzYsMzYsMCwwLDEsMTI4LDE1NloiPjwvcGF0aD48L3N2Zz4=";
		$userAvatar = 'avatars/';
		if($_SESSION['avatar_path'] === null || trim($_SESSION['avatar_path']) === '') $userAvatar = $blancAvatar;
		else $userAvatar .= $_SESSION['avatar_path'];
		
		return $userAvatar;
	}

	// Creates the HTML for the biography
	function getBiographyHtml(): string {
		
		$bio = '';
		if($_SESSION['bio'] !== null || trim($_SESSION['bio']) !== '')
		{
			$bio = '<div class="bio" style="margin-bottom: 0px; font-style: italic; color: black;">' . 
				$_SESSION['bio'] . '</div>';
		}
		return $bio;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Profile – <?php echo htmlspecialchars($username); ?></title>
		<link rel="stylesheet" href="css/m.user.css">
		<link rel="stylesheet" href="css/m.navigatedpage.css">
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
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>