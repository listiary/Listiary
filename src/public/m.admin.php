<?php

	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/../config/database-config.php";
	require_once __DIR__ . "/../includes/commonlib.php";
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
		// $userUrl = "userhome.php";
	// }
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Admin Panel | Listiary</title>
		<link rel="icon" href="/img/favicon.svg" type="image/svg+xml">
		<link rel="stylesheet" href="/css/m.admin.css">
		<link rel="stylesheet" href="/css/navigatedpage.css">
		<script type="text/javascript" src="/lib/dom-manipulate.js"></script>
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

			<a href="m.installer.php" class="MenuMain">Installer</a>
			<a href="m.uninstaller.php" class="MenuMain">Uninstaller</a>
			<a href="m.downloader.php" class="MenuMain">Downloader</a>
			<a href="m.uploader.php" class="MenuMain">Uploader</a>
			<a href="m.accessmanager.php" class="MenuMain">Access Manager</a>
			<a href="m.contentmanager.php" class="MenuMain">Content Manager</a>
			<a href="m.settingsmanager.php" class="MenuMain">Settings Manager</a>

			<hr id="MenuMainMiddleAnchor" style="margin: 22px;" class="MenuMain"/>
			<a href="https://documentation.listiary.org/" target="_blank" class="MenuMain">Documentation</a>
			<a href="m.contact.php" class="MenuMain">Contact us</a>
			<a href="https://github.com/listiary/DescribeCompiler" target="_blank" class="MenuMain">Describe Repo</a>
			<a href="https://github.com/listiary/Listiary" target="_blank" class="MenuMain">Listiary Repo</a>
			<!-- 
				About will have the following links there:
				<a href="https://forums.listiary.org/" target="_blank" class="MenuMain">Forums</a>
				<a href="m.contact.php" class="MenuMain">Contact us</a>
				<a href="m.report-bug.php" class="MenuMain">Report a bug</a>
				<a href="m.donate.php" class="MenuMain">Donate</a>
				
				<a href="m.terms-of-service.php" target="_blank" class="MenuMain">Terms of Use</a>
				<a href="m.privacy-policy.php" target="_blank" class="MenuMain">Privacy Policy</a>
				<a href="https://library.listiary.org/" target="_blank" class="MenuMain">Describe Library</a>
				<a href="https://documentation.listiary.org/" target="_blank" class="MenuMain">Describe Docs</a>
				<a href="https://github.com/viktorchernev/DescribeCompiler" target="_blank" class="MenuMain">Describe Repo</a>
			-->
			<hr id="MenuMainBottomAnchor" style="margin: 22px;" class="MenuMain"/>
			<a id="userlink" href="javascript:void(0);" class="MenuMain">Log Out</a>
			<!-- logout will redirect to the wiki index <a href="../index.php" class="MenuMain">Wiki Index</a> -->

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
						style="background: linear-gradient(rgb(196, 196, 196) 0%, rgb(196, 196, 196) calc(60% + 1px), transparent calc(60% + 2px));">
						<div style="left: 0; bottom: 0; width: 100%;">
							<div style="font-size: 25px; margin: 7px; margin-left: 15px; padding: 10px; margin-top: 19px; padding-left: 0px; padding-top: 0px;">
								<div style="margin-right:35px;">
									<a class="breadcrumbmain" style="display: inline;" href="admin.php">Admin Panel</a>
								</div>
								<hr>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="10" id="LargeContainer">
						<h1 style="margin-top: 0px;">About Admin Panel</h1>
						<p style="max-width: 700px;">
							The Admin Panel is the default control interface for the Listiary Wiki — a central instrument for configuring, maintaining, and administering Listiary wikis.

						</p>
						<p style="max-width: 700px;">
							With the Admin Panel, you can:
						</p>
						<ul>
							<li>Install or uninstall Listiary wiki instances</li>
							<li>Export database content into multiple formats</li>
							<li>Import and upload data from supported formats</li>
							<li>Manage system settings and configuration</li>
							<li>Control moderator and administrator access permissions</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	
	</body>
</html>