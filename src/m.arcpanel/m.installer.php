<?php

	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_commonlib.php";
	set_exception_handler('catchEx');
	
	// More Presets
	//startSecureSession();
	//$link = connectDb();
	
	//get user link
	$userUrl = 'javascript:void(0);';
	$userText = 'Test Mode';
	// $userText = 'Log In';
	// if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
	// {
		// $userText = $_SESSION["username"];
		// $userUrl = "m.userhome.php";
	// }
?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>ARC Installer | Listiary</title>
		<link rel="icon" href="img/favicon.svg" type="image/svg+xml">
		<link rel="stylesheet" href="css/m.installer.css">
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
			<hr id="MenuMainTopAnchor" style="margin: 22px;" class="MenuMain"/>
			
			
			<a href="m.arc.php" class="MenuMain">Home</a>
			<a href="m.uninstaller.php" class="MenuMain">Uninstaller</a>
			<a href="m.downloader.php" class="MenuMain">Downloader</a>
			<a href="m.uploader.php" class="MenuMain">Uploader</a>
			<a href="m.accessmanager.php" class="MenuMain">Access Manager</a>
			<a href="m.contentmanager.php" class="MenuMain">Content Manager</a>
			<a href="m.settingsmanager.php" class="MenuMain">Settings Manager</a>

			<hr id="MenuMainMiddleAnchor" style="margin: 22px;" class="MenuMain"/>
			<a id="userlink" href="about.php" class="MenuMain">About ARC 0.1</a> 
			<a href="https://documentation.listiary.org/" target="_blank" class="MenuMain">Describe Docs</a>
			<a href="https://github.com/listiary/DescribeCompiler" target="_blank" class="MenuMain">Describe Repo</a>
			<a href="https://github.com/listiary/Listiary" target="_blank" class="MenuMain">Listiary Repo</a>
			<!-- 
				About will have the following links there:
				<a href="https://forums.listiary.org/" target="_blank" class="MenuMain">Forums</a>
				<a href="../contact/mobile/contact.php" class="MenuMain">Contact us</a>
				<a href="../contact/mobile/reportbug.php" class="MenuMain">Report a bug</a>
				<a href="../contact/mobile/donate.php" class="MenuMain">Donate</a>
				
				<a href="../docs/m.terms_of_service.php" target="_blank" class="MenuMain">Terms of Use</a>
				<a href="../docs/m.privacy_policy.php" target="_blank" class="MenuMain">Privacy Policy</a>
				<a href="https://library.listiary.org/" target="_blank" class="MenuMain">Describe Library</a>
				<a href="https://documentation.listiary.org/" target="_blank" class="MenuMain">Describe Docs</a>
				<a href="https://github.com/viktorchernev/DescribeCompiler" target="_blank" class="MenuMain">Describe Repo</a>
			-->
			<hr id="MenuMainBottomAnchor" style="margin: 22px;" class="MenuMain"/>
			<a id="userlink" href="javascript:void(0);" class="MenuMain">Log Out</a>
			<!-- logout will redirect to the wiki index <a href="../m.index.php" class="MenuMain">Wiki Index</a> -->

		</div>

		<!-- Triggers -->
		<span id="sidenav-trigger" onclick="openNav();">&#9776;</span>

		<!-- Main page skeleton -->
		<table id="skeleton">
			<tbody>
				<tr>
				<!-- Vivid orange: rgb(255, 120, 0) -->
				<!-- Neon-ish orange: rgb(255, 100, 0) -->
				<!-- Softer but still bright: rgb(255, 165, 0) (classic orange) -->
					<td colspan="10" id="LargeTopContainer" 
						style="background: linear-gradient(rgb(255, 140, 0) 0%, rgb(255, 140, 0) calc(60% + 1px), transparent calc(60% + 2px));">
						<div style="left: 0; bottom: 0; width: 100%;">
							<div style="font-size: 25px; margin: 7px; margin-left: 15px; padding: 10px; margin-top: 19px; padding-left: 0px; padding-top: 0px;">
								<div style="margin-right:35px;">
									<a class="breadcrumbmain" style="display: inline;" href="m.arc.php">Arc Panel</a>
									<span class="breadcrumbmain" style="display: inline;"> . </span>
									<a class="breadcrumbmain" href="javascript:void(0);" style="display: inline;">Installer</a>
								</div>
								<hr>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="10" id="LargeContainer">
						<h1 style="margin-top: 0px;">Listiary Installer</h1>
						<p style="max-width: 700px;">
						Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
						</p><br>
						<button onclick="javascript:void(0);" class="btn btn--orange">1. Set main config</button><br><br>
						<button onclick="javascript:void(0);" class="btn btn--orange" disabled>2. Create DataBase tables</button><br>
						<button onclick="javascript:void(0);" class="btn btn--orange" disabled>3. Set up various settings</button><br>
						<button onclick="javascript:void(0);" class="btn btn--orange" disabled>4. Upload PHP files</button><br>
						<button onclick="javascript:void(0);" class="btn btn--orange" disabled>5. Visit your new wiki homepage</button><br>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>