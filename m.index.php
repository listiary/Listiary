<?php

	// Initialize the session
	session_start();

	$text = "Log in/Sign up";
	$url = "session/m.login.php?domain=public";
	if(isset($_GET['article']) == true) $url .= "&article=" . $_GET['article'];
	$username = "";

	//hardcoded for now. Should be calculated
	//from session values in the future, when we have
	//more than just 1 test user
	$usercode = "94fd9ec55ffd";

	// Check if the user is logged in, otherwise redirect to login page
	if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
	{
		$text = $_SESSION["username"];//"My profile";
		$url = "session/user.php";
		$username = $_SESSION['username'];
	}

	//echo '<script>alert("' . $username . '")</script>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="description" content="Created by Describe Compiler v0.9.2">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0">
	<meta name="author" content="Demon of reason">
	<link rel="stylesheet" type="text/css" href="m.index/css/styles.css" />
	<title>Radiowatch</title>
</head>
<body style="height:100%;">

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
		<a id="modelink_personal" href="javascript:personalClick('<?php echo $username; ?>');" class="MenuMain">public mode</a>
		<a id="modelink_private" href="javascript:privateClick('<?php echo $usercode; ?>');" style="display:none;" class="MenuMain">personal mode</a>
		<a id="modelink_normative" href="javascript:normativeClick();" style="display:none;" class="MenuMain">private mode</a>
		<a id="modelink_public" href="javascript:publicClick();" style="display:none;" class="MenuMain">normative mode</a>
		<hr id="MenuMainMiddleAnchor" style="margin: 22px;" class="MenuMain"/>
		<a href="#" target="blank" class="MenuMain">About</a>
		<a href="https://forums.worldinlists.net/" target="blank" class="MenuMain">Forums</a>
		<a href="contact/mobile/contact.php" class="MenuMain">Contact us</a>
		<a href="contact/mobile/reportbug.php" class="MenuMain">Report a bug</a>
		<a href="contact/mobile/donate.php" class="MenuMain">Donate</a>
		<br class="MenuMain">
		<hr id="MenuMainBottomAnchor" style="margin: 22px;" class="MenuMain"/>
		<a href="docs/m.terms_of_service.php" target="blank" class="MenuMain">Terms of Use</a>
		<a href="docs/m.privacy_policy.php" target="blank" class="MenuMain">Privacy Policy</a>
		<a href="https://library.listiary.net/" target="blank" class="MenuMain">Describe Library</a>
		<a href="https://documentation.listiary.net/" target="blank" class="MenuMain">Describe Docs</a>
		<a href="https://github.com/viktorchernev/DescribeCompiler" target="blank" class="MenuMain">Describe Repo</a>



		<div style="position: absolute; bottom: 50; left: 0; right: 0; background-color: #ccc; margin-top: 30px; display: flex; justify-content: center; align-items: center; gap: 10px;">
		<a style="padding: 8px;" href="javascript:void(0);" class="MenuMain">
			<img src="m.index/img/arrows-out-line-vertical-bold-gray.png" style="background-color: #818181; border: 3px solid #ccc; border-radius: 60px; width: 30px; height: 30px; padding: 3px;">
		</a>
		<a style="padding: 8px;" href="javascript:void(0);" class="MenuMain">
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
				<td colspan="10" id="LargeContainer"></td>
			</tr>
			<tr style="max-height: 60px; height: 60px;">				
				<td id="BottomToolbar" colspan="10" style="text-align: center; padding: 10px; padding-bottom: 20px; padding-top: 20px; background-color: #fff; display: none;">
					<img id="BottomToolbarScrollButton" src="m.index/img/arrow-line-up-thin.png" style="border: 1px solid black; border-radius: 40px; background-color: #ccc; padding:4px; height: 30px; width: 30px; cursor: pointer; margin: 7px;" onclick="javascript:scrollToTop();" />
					<img id="BottomToolbarEditButton" src="m.index/img/code-thin.png" style="border: 1px solid black; border-radius: 40px; background-color: #ccc; padding:4px; height: 30px; width: 30px; cursor: pointer; margin: 7px;" />
					<img id="BottomToolbarBackButton" src="m.index/img/arrow-elbow-up-left-thin.png" style="border: 1px solid black; border-radius: 40px; background-color: #ccc; padding:4px; height: 30px; width: 30px; cursor: pointer; margin: 7px;" onclick="javascript:navigateBreadcrumbBack();" />
				</td>
			</tr>
		</tbody>
	</table>
	
	
	<!-- Scripts -->
	<script type="text/javascript" src="m.index/js/scripts.options.js"></script>
	<script type="text/javascript" src="m.index/js/plugin.base.js"></script>
	<script type="text/javascript" src="m.index/js/plugin.stream-player.js"></script>
	<script type="text/javascript" src="m.index/js/plugin.local-loader.js"></script>
	<script type="text/javascript" src="sources/payload.js"></script>

	<!-- tooling -->
	<script type="text/javascript" src="m.index/js/listiaring.tool-management.js"></script>
	<script type="text/javascript" src="m.index/js/listiaring.tooling.js"></script>
	<script type="text/javascript" src="m.index/js/listiaring.common.js"></script>
	<script type="text/javascript" src="m.index/js/listiaring.common-tools.js"></script>
	<script type="text/javascript" src="m.index/js/listiaring.js"></script>

	<!-- other scripts -->
	<script type="text/javascript" src="m.index/js/mobile-detector.js"></script>
	<script type="text/javascript" src="m.index/js/main-tree.js"></script>
	<script type="text/javascript" src="m.index/js/crypto-js.min.js"></script>
	<!-- https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js -->

	<!-- main scripts -->
	<script type="text/javascript" src="m.index/js/scripts.more.js"></script>
	<script type="text/javascript" src="m.index/js/scripts.dom-manipulate.js"></script>
	<script type="text/javascript" src="m.index/js/scripts.dom-create.js"></script>
	<script type="text/javascript" src="m.index/js/scripts.redirects.js"></script>
	<script type="text/javascript" src="m.index/js/scripts.crypto.js"></script>
	<script type="text/javascript" src="m.index/js/scripts.json-loader.js"></script>
	<script type="text/javascript" src="m.index/js/scripts.js"></script>
</body>
</html>
