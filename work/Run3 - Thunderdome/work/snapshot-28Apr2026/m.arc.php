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
	
	// Processing form data when form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		
	}
?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>ARC Panel | Listiary</title>
		<link rel="icon" href="img/favicon.svg" type="image/svg+xml">
		<link rel="stylesheet" href="css/m.arc.css">
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
			
			
			<a href="m.installer.php" class="MenuMain">Installer</a>
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
									<a class="breadcrumbmain" href="javascript:void(0);" style="display: inline;">Home</a>
								</div>
								<hr>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="10" id="LargeContainer">
						<h1 style="margin-top: 0px;">About ARC Panel v0.1</h1>
						<p style="max-width: 700px;">
							The ARC Panel is the default control interface for the Listiary Wiki — a central instrument for configuring, maintaining, and interacting with wiki instances and their underlying systems.

							Listiary is designed with modularity in mind. While ARC provides the standard toolkit, additional panels may be developed and introduced over time.
						</p>
						<p style="max-width: 700px;">
							ARC stands for <strong>Archive, Reupload, Control</strong> — a name that reflects some of its core capabilities and the general spirit of the system, rather than a strict boundary of what it does.
						</p>
						<p style="max-width: 700px;">
							With the ARC Panel, you can:
						</p>

						<ul>
							<li>Install or uninstall Listiary wiki instances</li>
							<li>Export database content into multiple formats</li>
							<li>Import and upload data from supported formats</li>
							<li>Manage system settings and configuration</li>
							<li>Control moderator and administrator access permissions</li>
						</ul>

						<br>

						<a href="m.installer.php" class="btn btn--orange"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M216,80V192H40V64H200A16,16,0,0,1,216,80Z" opacity="0.2"></path><path d="M117.31,134l-72,64a8,8,0,1,1-10.63-12L100,128,34.69,70A8,8,0,1,1,45.32,58l72,64a8,8,0,0,1,0,12ZM216,184H120a8,8,0,0,0,0,16h96a8,8,0,0,0,0-16Z"></path></svg>Installer</a><br>
						<a href="m.uninstaller.php" class="btn btn--orange" aria-disabled="true"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M216,80V192H40V64H200A16,16,0,0,1,216,80Z" opacity="0.2"></path><path d="M117.31,134l-72,64a8,8,0,1,1-10.63-12L100,128,34.69,70A8,8,0,1,1,45.32,58l72,64a8,8,0,0,1,0,12ZM216,184H120a8,8,0,0,0,0,16h96a8,8,0,0,0,0-16Z"></path></svg>Uninstaller</a><br>
						<a href="m.downloader.php" class="btn btn--orange" aria-disabled="true"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M216,80V192H40V64H200A16,16,0,0,1,216,80Z" opacity="0.2"></path><path d="M117.31,134l-72,64a8,8,0,1,1-10.63-12L100,128,34.69,70A8,8,0,1,1,45.32,58l72,64a8,8,0,0,1,0,12ZM216,184H120a8,8,0,0,0,0,16h96a8,8,0,0,0,0-16Z"></path></svg>Downloader</a><br>
						<a href="m.uploader.php" class="btn btn--orange" aria-disabled="true"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M216,80V192H40V64H200A16,16,0,0,1,216,80Z" opacity="0.2"></path><path d="M117.31,134l-72,64a8,8,0,1,1-10.63-12L100,128,34.69,70A8,8,0,1,1,45.32,58l72,64a8,8,0,0,1,0,12ZM216,184H120a8,8,0,0,0,0,16h96a8,8,0,0,0,0-16Z"></path></svg>Uploader</a><br>
						<a href="m.accessmanager.php" class="btn btn--orange" aria-disabled="true"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M224,96a64,64,0,0,1-94.94,56L73,217A24,24,0,0,1,39,183L104,126.94a64,64,0,0,1,80-90.29L144,80l5.66,26.34L176,112l43.35-40A63.8,63.8,0,0,1,224,96Z" opacity="0.2"></path><path d="M226.76,69a8,8,0,0,0-12.84-2.88l-40.3,37.19-17.23-3.7-3.7-17.23,37.19-40.3A8,8,0,0,0,187,29.24,72,72,0,0,0,88,96,72.34,72.34,0,0,0,94,124.94L33.79,177c-.15.12-.29.26-.43.39a32,32,0,0,0,45.26,45.26c.13-.13.27-.28.39-.42L131.06,162A72,72,0,0,0,232,96,71.56,71.56,0,0,0,226.76,69ZM160,152a56.14,56.14,0,0,1-27.07-7,8,8,0,0,0-9.92,1.77L67.11,211.51a16,16,0,0,1-22.62-22.62L109.18,133a8,8,0,0,0,1.77-9.93,56,56,0,0,1,58.36-82.31l-31.2,33.81a8,8,0,0,0-1.94,7.1L141.83,108a8,8,0,0,0,6.14,6.14l26.35,5.66a8,8,0,0,0,7.1-1.94l33.81-31.2A56.06,56.06,0,0,1,160,152Z"></path></svg>Access Manager</a><br>
						<a href="m.contentmanager.php" class="btn btn--orange" aria-disabled="true"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M224,96a64,64,0,0,1-94.94,56L73,217A24,24,0,0,1,39,183L104,126.94a64,64,0,0,1,80-90.29L144,80l5.66,26.34L176,112l43.35-40A63.8,63.8,0,0,1,224,96Z" opacity="0.2"></path><path d="M226.76,69a8,8,0,0,0-12.84-2.88l-40.3,37.19-17.23-3.7-3.7-17.23,37.19-40.3A8,8,0,0,0,187,29.24,72,72,0,0,0,88,96,72.34,72.34,0,0,0,94,124.94L33.79,177c-.15.12-.29.26-.43.39a32,32,0,0,0,45.26,45.26c.13-.13.27-.28.39-.42L131.06,162A72,72,0,0,0,232,96,71.56,71.56,0,0,0,226.76,69ZM160,152a56.14,56.14,0,0,1-27.07-7,8,8,0,0,0-9.92,1.77L67.11,211.51a16,16,0,0,1-22.62-22.62L109.18,133a8,8,0,0,0,1.77-9.93,56,56,0,0,1,58.36-82.31l-31.2,33.81a8,8,0,0,0-1.94,7.1L141.83,108a8,8,0,0,0,6.14,6.14l26.35,5.66a8,8,0,0,0,7.1-1.94l33.81-31.2A56.06,56.06,0,0,1,160,152Z"></path></svg>Content Manager</a><br>
						<a href="m.settingsmanager.php" class="btn btn--orange" aria-disabled="true"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M224,96a64,64,0,0,1-94.94,56L73,217A24,24,0,0,1,39,183L104,126.94a64,64,0,0,1,80-90.29L144,80l5.66,26.34L176,112l43.35-40A63.8,63.8,0,0,1,224,96Z" opacity="0.2"></path><path d="M226.76,69a8,8,0,0,0-12.84-2.88l-40.3,37.19-17.23-3.7-3.7-17.23,37.19-40.3A8,8,0,0,0,187,29.24,72,72,0,0,0,88,96,72.34,72.34,0,0,0,94,124.94L33.79,177c-.15.12-.29.26-.43.39a32,32,0,0,0,45.26,45.26c.13-.13.27-.28.39-.42L131.06,162A72,72,0,0,0,232,96,71.56,71.56,0,0,0,226.76,69ZM160,152a56.14,56.14,0,0,1-27.07-7,8,8,0,0,0-9.92,1.77L67.11,211.51a16,16,0,0,1-22.62-22.62L109.18,133a8,8,0,0,0,1.77-9.93,56,56,0,0,1,58.36-82.31l-31.2,33.81a8,8,0,0,0-1.94,7.1L141.83,108a8,8,0,0,0,6.14,6.14l26.35,5.66a8,8,0,0,0,7.1-1.94l33.81-31.2A56.06,56.06,0,0,1,160,152Z"></path></svg>Settings Manager</a><br>

						<br><br>

						<a href="https://documentation.listiary.org/" target="_blank" class="btn btn--orange"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M208,32V192H72a24,24,0,0,0-24,24V56A24,24,0,0,1,72,32Z" opacity="0.2"></path><path d="M208,24H72A32,32,0,0,0,40,56V224a8,8,0,0,0,8,8H192a8,8,0,0,0,0-16H56a16,16,0,0,1,16-16H208a8,8,0,0,0,8-8V32A8,8,0,0,0,208,24Zm-8,160H72a31.82,31.82,0,0,0-16,4.29V56A16,16,0,0,1,72,40H200Z"></path></svg>Documentation</a><br>
						<a href="https://github.com/listiary/Listiary" target="_blank" class="btn btn--orange"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M208,104v8a48,48,0,0,1-48,48H136a32,32,0,0,1,32,32v40H104V192a32,32,0,0,1,32-32H112a48,48,0,0,1-48-48v-8a49.28,49.28,0,0,1,8.51-27.3A51.92,51.92,0,0,1,76,32a52,52,0,0,1,43.83,24h32.34A52,52,0,0,1,196,32a51.92,51.92,0,0,1,3.49,44.7A49.28,49.28,0,0,1,208,104Z" opacity="0.2"></path><path d="M208.3,75.68A59.74,59.74,0,0,0,202.93,28,8,8,0,0,0,196,24a59.75,59.75,0,0,0-48,24H124A59.75,59.75,0,0,0,76,24a8,8,0,0,0-6.93,4,59.78,59.78,0,0,0-5.38,47.68A58.14,58.14,0,0,0,56,104v8a56.06,56.06,0,0,0,48.44,55.47A39.8,39.8,0,0,0,96,192v8H72a24,24,0,0,1-24-24A40,40,0,0,0,8,136a8,8,0,0,0,0,16,24,24,0,0,1,24,24,40,40,0,0,0,40,40H96v16a8,8,0,0,0,16,0V192a24,24,0,0,1,48,0v40a8,8,0,0,0,16,0V192a39.8,39.8,0,0,0-8.44-24.53A56.06,56.06,0,0,0,216,112v-8A58,58,0,0,0,208.3,75.68ZM200,112a40,40,0,0,1-40,40H112a40,40,0,0,1-40-40v-8a41.74,41.74,0,0,1,6.9-22.48A8,8,0,0,0,80,73.83a43.81,43.81,0,0,1,.79-33.58,43.88,43.88,0,0,1,32.32,20.06A8,8,0,0,0,119.82,64h32.35a8,8,0,0,0,6.74-3.69,43.87,43.87,0,0,1,32.32-20.06A43.81,43.81,0,0,1,192,73.83a8.09,8.09,0,0,0,1,7.65A41.76,41.76,0,0,1,200,104Z"></path></svg>Listiary Wiki Platform (GitHub)</a><br>
						<a href="https://github.com/listiary/DescribeCompiler" target="_blank" class="btn btn--orange"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M208,104v8a48,48,0,0,1-48,48H136a32,32,0,0,1,32,32v40H104V192a32,32,0,0,1,32-32H112a48,48,0,0,1-48-48v-8a49.28,49.28,0,0,1,8.51-27.3A51.92,51.92,0,0,1,76,32a52,52,0,0,1,43.83,24h32.34A52,52,0,0,1,196,32a51.92,51.92,0,0,1,3.49,44.7A49.28,49.28,0,0,1,208,104Z" opacity="0.2"></path><path d="M208.3,75.68A59.74,59.74,0,0,0,202.93,28,8,8,0,0,0,196,24a59.75,59.75,0,0,0-48,24H124A59.75,59.75,0,0,0,76,24a8,8,0,0,0-6.93,4,59.78,59.78,0,0,0-5.38,47.68A58.14,58.14,0,0,0,56,104v8a56.06,56.06,0,0,0,48.44,55.47A39.8,39.8,0,0,0,96,192v8H72a24,24,0,0,1-24-24A40,40,0,0,0,8,136a8,8,0,0,0,0,16,24,24,0,0,1,24,24,40,40,0,0,0,40,40H96v16a8,8,0,0,0,16,0V192a24,24,0,0,1,48,0v40a8,8,0,0,0,16,0V192a39.8,39.8,0,0,0-8.44-24.53A56.06,56.06,0,0,0,216,112v-8A58,58,0,0,0,208.3,75.68ZM200,112a40,40,0,0,1-40,40H112a40,40,0,0,1-40-40v-8a41.74,41.74,0,0,1,6.9-22.48A8,8,0,0,0,80,73.83a43.81,43.81,0,0,1,.79-33.58,43.88,43.88,0,0,1,32.32,20.06A8,8,0,0,0,119.82,64h32.35a8,8,0,0,0,6.74-3.69,43.87,43.87,0,0,1,32.32-20.06A43.81,43.81,0,0,1,192,73.83a8.09,8.09,0,0,0,1,7.65A41.76,41.76,0,0,1,200,104Z"></path></svg>Describe Language Compiler (GitHub)</a><br>
						<a id="userlink" href="javascript:void(0);" class="btn btn--orange" aria-disabled="true"><svg style="margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#000000" viewBox="0 0 256 256"><path d="M224,56V200a16,16,0,0,1-16,16H48V40H208A16,16,0,0,1,224,56Z" opacity="0.2"></path><path d="M120,216a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V40a8,8,0,0,1,8-8h64a8,8,0,0,1,0,16H56V208h56A8,8,0,0,1,120,216Zm109.66-93.66-40-40a8,8,0,0,0-11.32,11.32L204.69,120H112a8,8,0,0,0,0,16h92.69l-26.35,26.34a8,8,0,0,0,11.32,11.32l40-40A8,8,0,0,0,229.66,122.34Z"></path></svg>Log Out</a>
						
						<br><br><br><br>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>