<?php


	// APEARANCE										//index
		// Dark theme/Light theme toggle
		// Interface language
		// Prefered language(s)
		
	// EDITOR											//editor
		// Default edit message
		
	// SEARCH											//search

	// PLUGINS											//plugins

	// CPANEL											//arc panel

	// MY BOTS											//bots
		
	// SECURITY										//session
		// Change email
		// Add recovery email(s)
		// Active Sessions
		// Security events
		// Log out all ...
		// Archive account ...
		// Delete account ...













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
	

	// Fetch user data
	$user_data = fetchUserData($link, $_SESSION['id']);
	$user_details = fetchUserDetailsArr($link, $_SESSION['id']);


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
									<a class="breadcrumbmain" href="m.usersettings.php" style="display: inline;">Settings</a>
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

							<!-- Username -->
							<h1 style="color:<?php echo $usernameColor; ?>;"><?php echo $userstar . htmlspecialchars($user_data['username']) . $usernametail; ?></h1>
							<br /><br />


								<strong>APEARANCE (settings for index module)</strong><br />
								<div>• Dark theme/Light theme toggle</div>
								<div>• Interface language</div>
								<div>• Prefered language(s)</div><br />

								<strong>EDITOR (settings for editor module)</strong><br />
								<div>• Default edit message</div><br />
									
								<strong>SEARCH (settings for search module)</strong><br />

								<strong>PLUGINS (settings for plugins module)</strong><br />

								<strong>CPANEL (settings for arc panel module)</strong><br />

								<strong>MY BOTS (settings for bots module)</strong><br /><br />
									
								<strong>SECURITY (settings for session module)</strong><br />
								<div>• Change email</div>
								<div>• Add recovery email(s)</div>
								<div>• Active Sessions</div>
								<div>• Security events</div>
								<div>• Log out all ...</div>
								<div>• Archive account ...</div>
								<div>• Delete account ...</div>
							


							<!-- Dummies -->
							<!-- <p><label><input type="checkbox" checked> <strong>Lorem:</strong> ipsum mmsam</label></p>
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
							<p><label><input type="checkbox" checked> <strong>Cillum:</strong> fugiat nulla pariatur</label></p> -->

							<br /><br /><br />
							<!-- Menu -->
							<a href="m.userhome.php?id=<?php echo $_SESSION['id']; ?>"><strong>Back</strong></a>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>