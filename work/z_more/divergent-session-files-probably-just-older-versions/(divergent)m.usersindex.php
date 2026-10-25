<?php

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/config/database_config.php";
	require_once __DIR__ . "/api/sessionlib.php";
	set_exception_handler('catchEx');
	
	// More Presets
	//startSecureSession();
	$link = connectDb();
	
	// Get users
	$dummyUsers = getDummyAccountNames(74);
	$realUsers = fetchUserNames($link);
	$users = [];
	foreach ($dummyUsers as $name)
	{
		$r = random_int(1, 100);
		$color = 'color:#ccc;';
		if ($r <= 3) $color = 'color:blue;';
		elseif ($r <= 8) $color = 'color:red;';      	// 3 + 5
		elseif ($r <= 16) $color = 'color:green;';		// 8 + 8
		elseif ($r <= 86) $color = 'color:black;';		// 16 + 70

		$star = random_int(1, 100) <= 10;
		$users[] = 
		[
			'username' => $name,
			'star' => $star,
			'id' => 0,
			'style' => $color . ($star ? 'margin-left:-19px;' : '')
		];
	}
	foreach ($realUsers as $user)
	{
		$star = $user['is_premium'];
		$users[] = 
		[
			'username' => $user['username'],
			'star' => $star,
			'id' => $user['id'],
			'style' => 'color:blue;' . ($star ? 'margin-left:-19px;' : '')
		];
	}

	// Sort alphabetically by username
	usort($users, function($a, $b)
	{
		return strcmp($a['username'], $b['username']);
	});
	
	//var_dump($users); die();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Users | Listiary</title>
	<link rel="stylesheet" href="css/m.usersindex.css">
	<link rel="stylesheet" href="css/m.navigatedpage.css">
	<script src="js/m.usersindex.js"></script>
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
									<a class="breadcrumbmain" style="display: inline;" href="m.usersindex.php">Users Index</a>
								</div>
								<hr>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="10" id="LargeContainer" >
					
						<div class="logo" style="display: flex; justify-content: center;">
							<a href="../m.index.php"><img src="img/listiary-logo.png" alt="Listiary Logo"></a>
						</div>
						<div class="search-container">
							<input id="searchInput" type="text" placeholder="Start typing a username ...">
							<button onclick="search()">Search</button>
						</div>
						<div class="users-grid">
							<?php foreach ($users as $user): ?>
								<div class="user-item">
									<a style="<?= $user['style'] ?>" 
										href="https://development.listiary.org/session/m.user.php?id=<?= $user['id'] ?>">
										<?= ($user['star'] ? '✦ ' : '') . htmlspecialchars($user['username']) ?>
									</a>
								</div>
							<?php endforeach; ?>
						</div>
						<script>
							// Optional: search while typing
							document.getElementById("searchInput").addEventListener("keyup", search);
						</script>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>