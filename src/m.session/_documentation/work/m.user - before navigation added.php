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


	// Set UI - avatar
	$userAvatar = getAvatarHtml($user_details);
	
	// Set UI - username
	$username = $user_data['username'];
	$usernameHtml = getUsernameHtml($user_data);
	
	// Set UI - biography
	$bio = getBiographyHtml($user_details);
	
	// Set UI - userspaces links
	$links = getUserspaceLinksHtml($id);

	// Set UI - fields
	$status = getStatusHtml($user_data);
	$email = $user_data['email'];
	$phoneField = getPhoneHtml($user_details);
	$created_at = $user_data['created_at'];
	//$is_active = $user_data['is_active'];
	//$is_bot = $user_data['is_bot'];
	//$is_premium = $user_data['is_premium'];
	$address = getAddressHtml($user_details);
	$timenow = getTimeHtml($user_details);
	
	
	
	
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
			$address = '<p class="info-line"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256"><path d="M200,220H160.73c5.18-5,10.75-10.71,16.33-17.13C205.15,170.57,220,136.37,220,104a92,92,0,0,0-184,0c0,50,34.12,91.94,59.18,116H56a12,12,0,0,0,0,24H200a12,12,0,0,0,0-24ZM60,104a68,68,0,0,1,136,0c0,33.31-20,63.37-36.7,82.71A249.35,249.35,0,0,1,128,216.89a249.35,249.35,0,0,1-31.3-30.18C80,167.37,60,137.31,60,104Zm68,44a44,44,0,1,0-44-44A44.05,44.05,0,0,0,128,148Zm0-64a20,20,0,1,1-20,20A20,20,0,0,1,128,84Z"></path></svg><span>' . $address . '</span></p><br />';
		}
		return $address;
	}
	
	// Creates the HTML for the current time line
	function getTimeHtml(array $user_details): string {
		
		$timenow = '';
		if($user_details['timezone'] !== null && trim($user_details['timezone']) !== '')
		{
			$t = getTimeNow($user_details['timezone']);
			$timenow = "<p class='info-line'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='#000000' viewBox='0 0 256 256'><path d='M128,20A108,108,0,1,0,236,128,108.12,108.12,0,0,0,128,20Zm0,192a84,84,0,1,1,84-84A84.09,84.09,0,0,1,128,212Zm68-84a12,12,0,0,1-12,12H128a12,12,0,0,1-12-12V72a12,12,0,0,1,24,0v44h44A12,12,0,0,1,196,128Z'></path></svg><span>$t</span></p>";
		}
		return $timenow;
	}
	
	// Creates the HTML for the phone
	function getPhoneHtml(array $user_details): string {
		
		$phone = $user_details['phone1'];
		$phoneField = "<p><strong>Phone:</strong> $phone </p>";
		if($phone === null || trim($phone) === '') $phoneField = '';
		return $phoneField;
	}
	
	// Creates the HTML for the biography
	function getBiographyHtml(array $user_details): string {
		
		$bio = '';
		if($user_details['bio'] !== null || trim($user_details['bio']) !== '')
		{
			$bio = '<div class="bio" style="margin-bottom: 15px; font-style: italic; color: black;">' . 
				$user_details['bio'] . '</div>';
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
	
	// Creates the HTML for the status
	function getStatusHtml(array $user_data): string {
		
		$status = '';
		if($user_data['is_active']) $status = 'Active';
		else $status = 'Inactive';
		if($user_data['is_bot']) $status .= ' (Bot)';
		return $status;
	}
	
	// Creates the HTML for the username
	function getUsernameHtml(array $user_data): string {
		
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
		
		$usernameHtml = '<h1 style="color:' . $usernameColor . '">' . $userstar . htmlspecialchars($user_data['username']) . $usernametail . '</h1>';
		return $usernameHtml;
	}

	// Creates the HTML for the user links - contributions, public lists
	function getUserspaceLinksHtml(int $user_id): string {
		
		$links = '<a class="btn btn--purple">User\'s Contributions</a><br /><a class="btn btn--blue">User\'s  Public Lists</a>';
		return $links;
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
			
			<!-- This is the menu '🡠' button -->
			<a id="menuArrow" href="javascript: showNextMenu();" class="closebtn" style="
			position: absolute; top: 0; right: 20; font-size: 25px;
			padding: 8px; text-decoration: none; color: #818181; display: inline; transition: 0.3s;
			padding-left: 30px;
			padding-top: 12px;">
				<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" style="margin-top:9px;" viewBox="0 0 25 25">
					<path d="M1,9 L10,0" stroke="#818181" stroke-width="2" fill="none"/>
					<path d="M1,9 L19,9" stroke="#818181" stroke-width="2" fill="none"/>
					<path d="M0,9 L1,9" stroke="#818181" stroke-width="1" fill="none"/>
					<path d="M1,9 L10,18" stroke="#818181" stroke-width="2" fill="none"/>
				</svg>
			</a>



			<!-- MenuMain -->
			<a id="userlink" href="<?php echo $url; ?>" class="MenuMain"><?php echo $text; ?></a><!-- <a href="javascript:void(0);">Settings</a> -->
			<!-- <a id="modelink_personal" href="javascript:personalClick('<?php echo $username; ?>');" class="MenuMain">public mode</a>
			<a id="modelink_private" href="javascript:privateClick('<?php echo $usercode; ?>');" style="display:none;" class="MenuMain">personal mode</a>
			<a id="modelink_normative" href="javascript:normativeClick();" style="display:none;" class="MenuMain">private mode</a>
			<a id="modelink_public" href="javascript:publicClick();" style="display:none;" class="MenuMain">normative mode</a> -->
			<hr id="MenuMainMiddleAnchor" style="margin: 22px;" class="MenuMain"/>
			<a href="m.index.php" class="MenuMain">Wiki Index</a>
			<a href="session/m.usersindex.php" class="MenuMain">Users Index</a>
			<a href="#" target="_blank" class="MenuMain">About</a>
			<a href="https://forums.listiary.org/" target="_blank" class="MenuMain">Forums</a>
			<a href="contact/mobile/contact.php" class="MenuMain">Contact us</a>
			<a href="contact/mobile/reportbug.php" class="MenuMain">Report a bug</a>
			<a href="contact/mobile/donate.php" class="MenuMain">Donate</a>
			<br class="MenuMain">
			<hr id="MenuMainBottomAnchor" style="margin: 22px;" class="MenuMain"/>
			<a href="docs/m.terms_of_service.php" target="_blank" class="MenuMain">Terms of Use</a>
			<a href="docs/m.privacy_policy.php" target="_blank" class="MenuMain">Privacy Policy</a>
			<a href="https://library.listiary.org/" target="_blank" class="MenuMain">Describe Library</a>
			<a href="https://documentation.listiary.org/" target="_blank" class="MenuMain">Describe Docs</a>
			<a href="https://github.com/viktorchernev/DescribeCompiler" target="_blank" class="MenuMain">Describe Repo</a>



			<div style="position: absolute; bottom: 50; left: 0; right: 0; background-color: #ccc; margin-top: 30px; display: flex; justify-content: center; align-items: center; gap: 10px;">
			<a id="Menu_StyleSwitcher" style="padding: 8px;" class="MenuMain">
				<img src="m.index/img/text-align-right-bold-gray.png" style="background-color: #818181; border: 3px solid #ccc; border-radius: 60px; width: 30px; height: 30px; padding: 3px;">
			</a>
			<a id="Menu_LangSwitcher" style="padding: 8px;" class="MenuMain">
				<img src="m.index/img/translate-bold-gray.png" style="background-color: #818181; border: 3px solid #ccc; border-radius: 60px; width: 30px; height: 30px; padding: 3px;">
			</a>
			</div>



			<!-- MenuNext -->
			<br id="MenuNextTopAnchor" class="MenuNext"/>
			<a style="display: none;" href="javascript:showHighlightersMenu();" class="MenuNext">
				<img src="m.index/img/pencil-simple-line-bold.png" style="margin-top:2px; margin-bottom: 10px; width:24px; height:24px; vertical-align: text-top;" />
				<span style="padding-left:10px;">Highlighters</span>
			</a>
			<a style="display: none;" href="javascript:showSortersMenu();" class="MenuNext">
				<img src="m.index/img/sort-ascending-bold.png" style="margin-top:2px; margin-bottom: 10px; width:24px; height:24px; vertical-align: text-top;" />
				<span style="padding-left:10px;">Sorters</span>
			</a>
			<a style="display: none;" href="javascript:showCopiersMenu();" class="MenuNext">
				<img src="m.index/img/copy-bold.png" style="margin-top:2px; margin-bottom: 10px; width:24px; height:24px; vertical-align: text-top;" />
				<span style="padding-left:10px;">Copiers</span>
			</a>
			<a style="display: none;" href="javascript:showPickersMenu();" class="MenuNext">
				<img src="m.index/img/arrow-fat-down-bold.png" style="margin-top:2px; margin-bottom: 10px; width:24px; height:24px; vertical-align: text-top;" />
				<span style="padding-left:10px;">Pickers</span>
			</a>
			<a style="display: none;" href="javascript:showViewersMenu();" class="MenuNext">
				<img src="m.index/img/eye-bold.png" style="margin-top:2px; margin-bottom: 10px; width:24px; height:24px; vertical-align: text-top;" />
				<span style="padding-left:10px;">Viewers</span>
			</a>
			<a style="display: none;" href="javascript:showFiltersMenu();" class="MenuNext">
				<img src="m.index/img/funnel-bold.png" style="margin-top:2px; margin-bottom: 10px; width:24px; height:24px; vertical-align: text-top;" />
				<span style="padding-left:10px;">Filters</span>
			</a>
			<a style="display: none;" href="javascript:showLanguagesMenu();" class="MenuNext">
				<img src="m.index/img/translate-bold.png" style="margin-top:2px; margin-bottom: 10px; width:24px; height:24px; vertical-align: text-top;" />
				<span style="padding-left:10px;">Languages</span>
			</a>
			<a style="display: none;" href="javascript:showPluginsMenu();" class="MenuNext">
				<img src="m.index/img/dots-three-circle-bold.png" style="margin-top:2px; margin-bottom: 10px; width:24px; height:24px; vertical-align: text-top;" />
				<span style="padding-left:10px;">Plugins</span>
			</a>
			<hr id="MenuNextMiddleAnchor" style="margin: 32px; display: none;" class="MenuNext" />
			<hr id="MenuNextBottomAnchor" style="margin: 32px; display: none;" class="MenuNext" />


			<!-- MenuHighlighters -->
			<br id="MenuHighlightersTopAnchor" class="MenuHighlighters"/>
			<hr id="MenuHighlightersMiddleAnchor" style="margin: 32px; display: none;" class="MenuHighlighters"/>
			<hr id="MenuHighlightersBottomAnchor" style="margin: 32px; display: none;" class="MenuHighlighters"/>

			<!-- MenuSorters -->
			<br id="MenuSortersTopAnchor" class="MenuSorters" />
			<hr id="MenuSortersMiddleAnchor" style="margin: 32px; display: none;" class="MenuSorters" />
			<hr id="MenuSortersBottomAnchor" style="margin: 32px; display: none;" class="MenuSorters" />

			<!-- MenuCopiers -->
			<br id="MenuCopiersTopAnchor" class="MenuCopiers" />
			<hr id="MenuCopiersMiddleAnchor" style="margin: 32px; display: none;" class="MenuCopiers" />
			<hr id="MenuCopiersBottomAnchor" style="margin: 32px; display: none;" class="MenuCopiers" />
			
			<!-- MenuPlugins -->
			<br id="MenuPluginsTopAnchor" class="MenuPlugins" />
			<hr id="MenuPluginsMiddleAnchor" style="margin: 32px; display: none;" class="MenuPlugins" />
			<hr id="MenuPluginsBottomAnchor" style="margin: 32px; display: none;" class="MenuPlugins" />

			<!-- MenuPickers -->
			<br id="MenuPickersTopAnchor" class="MenuPickers" />
			<hr id="MenuPickersMiddleAnchor" style="margin: 32px; display: none;" class="MenuPickers" />
			<hr id="MenuPickersBottomAnchor" style="margin: 32px; display: none;" class="MenuPickers" />

			<!-- MenuViewers -->
			<br id="MenuViewersTopAnchor" class="MenuViewers" />
			<hr id="MenuViewersMiddleAnchor" style="margin: 32px; display: none;" class="MenuViewers" />
			<hr id="MenuViewersBottomAnchor" style="margin: 32px; display: none;" class="MenuViewers" />
			
			<!-- MenuFilters -->
			<br id="MenuFiltersTopAnchor" class="MenuFilters" />
			<hr id="MenuFiltersMiddleAnchor" style="margin: 32px; display: none;" class="MenuFilters" />
			<hr id="MenuFiltersBottomAnchor" style="margin: 32px; display: none;" class="MenuFilters" />
		
			<!-- MenuDroppers -->
			<!-- <br id="MenuDroppersTopAnchor" class="MenuDroppers" /> -->
			<!-- <hr id="MenuDroppersMiddleAnchor" style="margin: 32px; display: none;" class="MenuDroppers" /> -->
			<!-- <hr id="MenuDroppersBottomAnchor" style="margin: 32px; display: none;" class="MenuDroppers" /> -->

			<!-- MenuExpanders -->
			<!-- <br id="MenuExpandersTopAnchor" class="MenuExpanders" /> -->
			<!-- <hr id="MenuExpandersMiddleAnchor" style="margin: 32px; display: none;" class="MenuExpanders" /> -->
			<!-- <hr id="MenuExpandersBottomAnchor" style="margin: 32px; display: none;" class="MenuExpanders" /> -->
			
		</div>

		<!-- Triggers -->
		<!-- <img id="history-trigger" src='m.index/img/clock.png' />
		<!-- <img id="bookmark-trigger" src='m.index/img/star.png' />
		<!-- <img id="translate-trigger" src='m.index/img/translate.png' />
		<!-- <img id="talkpage-trigger" src='m.index/img/chat-text.png' /> -->
		<span id="sidenav-trigger" onclick="openNav();">&#9776;</span>

		<!-- Main page skeleton -->
		<table id="skeleton">
			<tbody>
				<tr>
					<td colspan="10" id="LargeTopContainer"></td>
				</tr>
				<tr>
					<td colspan="10" id="LargeContainer">
						<div class="profile-container">
							<!-- Avatar -->
							<img src="<?php echo $userAvatar; ?>" alt="Avatar" class="avatar">

							<!-- Username -->
							<?php echo $usernameHtml; ?>
							
							<!-- Basic bio -->
							<?php echo $bio; ?>
							<br /><br />

							<!-- Lists -->
							<?php echo $links; ?>
							<br /><br /><br />
							
							<!-- Contact info -->
							<?php echo $address; ?>
							<?php echo $timenow; ?>
							<br /><br />
							<p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
							<?php echo $phoneField; ?>
							<p><strong>Joined:</strong> <?php echo htmlspecialchars(date("F j, Y", strtotime($created_at))); ?></p>
							<p><strong>Status:</strong> <?php echo $status ?> </p>
							<br /><br />

							<!-- Menu -->
							<a href="m.usersindex.php"><strong>Back</strong></a>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>