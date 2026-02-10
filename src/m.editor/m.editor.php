<?php

	//get URL parameters
	$article = ""; 
	$domain = "";
	$itemid = "";
	getUrlParameters();
	$returnUrl = urlencode($itemid);
	//echo "<script>console.log(" . $returnUrl . ");</script>";
	
	//include config
	require_once "php/_config.php";

	//read config
	$servername = ""; 
	$username = ""; 
	$password = ""; 
	$dbname = "";
	setDbConstants($domain);

	//create connection
	$conn = createConnection();

	//fetch article to load
	$curArticle = fetchCurrentArticle($conn, $article);

	//get new article name and new article url.
	$newname = generateNewArticleNameInSameNamespace($article);
	$newArticleUrl = "m.editornewfile.php?article=" . $newname . "&domain=" . $domain;
		//echo $newArticleUrl;

	//$curPath = $_SERVER['SERVER_NAME'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
	$editfileUrl = "php/_editfile.php?article=" . $article . "&domain=" . $domain;
	if($domain == "personal") $editfileUrl .= "&username=" . $_GET['username'];

	// do breadcrumb
	$breadcrumb = getBreadcrumb($domain, $article);




	// Read Database connection constants from the config into our global variables.
	function setDbConstants($domain) {

		global $servername, $username, $password, $dbname;
		
		if($domain == "public")
		{
			$servername = DB_SERVER_PUBLIC;
			$username = DB_USERNAME_PUBLIC;
			$password = DB_PASSWORD_PUBLIC;
			$dbname = DB_NAME_PUBLIC;
		}
		else if($domain == "personal")
		{
			$servername = DB_SERVER_PERSONAL;
			$username = DB_USERNAME_PERSONAL;
			$password = DB_PASSWORD_PERSONAL;
			$dbname = DB_NAME_PERSONAL;
		}
		else if($domain == "private")
		{
			$servername = DB_SERVER_PRIVATE;
			$username = DB_USERNAME_PRIVATE;
			$password = DB_PASSWORD_PRIVATE;
			$dbname = DB_NAME_PRIVATE;
		}
		else if($domain == "normative")
		{
			$servername = DB_SERVER_DOCUMENTATION;
			$username = DB_USERNAME_DOCUMENTATION;
			$password = DB_PASSWORD_DOCUMENTATION;
			$dbname = DB_NAME_DOCUMENTATION;
		}
		else
		{
			die("Connection failed: Unknown value for url parameter domain - '" . $domain . "'");
		}
	}
	// Create a connection to the database
	function createConnection() {

		global $servername, $username, $password, $dbname;

		$connection = mysqli_connect($servername, $username, $password, $dbname);
		if(!$connection) die("Connection failed: " . mysqli_connect_error());
		//echo "Created connection to '" . $servername . "'<br />";

		return $connection;
	}
	// Read URL parameters into our global variables
	function getUrlParameters() {

		global $article, $domain, $itemid;

		//get parameters
		if(isset($_GET['article']) == false) die("'article' parameter of the url is not set");
		$article = $_GET['article'];
			//echo "Got article parameter - '" . $article . "'<br />";

		if(isset($_GET['itemid']) == false) die("'itemid' parameter of the url is not set");
		$itemid = $_GET['itemid'];
			//echo "Got itemid parameter - '" . itemid . "'<br />";
		
		if(isset($_GET['domain']) == false) die("'domain' parameter of the url is not set");
		$domain = $_GET['domain'];
			//echo "Got domain parameter - '" . $domain . "'<br />";
		
		if($_GET['domain'] == "personal" && isset($_GET['username']) == false)
		die("'domain' is 'personal' and 'username' parameter of the url is not set");
			//echo "Got username parameter - '" . $_GET['username'] . "'<br />";
	}
	// Fetch the current article from the DB
	function fetchCurrentArticle($conn, $article) {

		//get old article
		$sql = "SELECT `content` FROM `describe_documents` WHERE filename='" . $article . "'";
		//echo "Executing query - '" . $sql . "'<br />";
		$result = mysqli_query($conn, $sql);
		if($result == false || mysqli_num_rows($result) < 1) 
		{
			die("Query result is false");
		}
		$row = mysqli_fetch_assoc($result);
		$oldArticle = $row["content"];
		//echo "Got the old article <br />";

		return $oldArticle;
	}
	// Generate Random Article Name
	function generateNewArticleNameInSameNamespace($oldArticleName) {

		//we get the namespace from the current article name, and change the last name with a random string.
		//so "radiowatch.artists.adele" becomes "radiowatch.atrists.RANDOM"
		//then we create the "create new article" url - to create an article in the same namespace

		$newname = '';
		$dotindex = strrpos($oldArticleName, '.');
		if($dotindex !== false && $dotindex > 0)
		{
			$newname = substr($oldArticleName, 0, $dotindex);
			$newname = $newname . "." . generateRandomString(10);
		}
		else
		{
			$newname = generateRandomString(10);
		}
		return $newname;
	}
	// Generate random string
	function generateRandomString($length = 10) {

		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[random_int(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	// Generate the breadcrumb
	function getBreadcrumb($reqDomain, $oldArticleName) {

		$before = " <div style=\"left: 0; bottom: 0; width: 100%; margin-bottom: 0px; padding-bottom: 0px;\">";
		$before .= "<div style=\"font-size: 25px; margin: 7px; margin-left: 15px; padding: 10px; margin-top: 19px; padding-left: 0px; padding-top: 0px; margin-bottom: 0px; padding-bottom: 0px;\">";
		$before .= "<div style=\"margin-right:35px; margin-bottom: 0px; padding-bottom: 0px;\">";
		$after = "</div><hr style='margin-bottom:-2px; padding-bottom:0px; visibility: hidden;'></div></div>";

		$breadcrumb = "";
		if(strtoupper($reqDomain) != "PUBLIC")
		{
			$breadcrumb = "<span style='font-size: 1.5em; color: black; font-weight: bold;'>[" .
				strtoupper($reqDomain) . "]</span>";
		}

		$crumbs = explode(".", $oldArticleName);
		$arrLength = count($crumbs);
		for ($x = 0; $x < $arrLength - 1; $x++)
		{
			if(strlen($breadcrumb) > 0)
			{
				$breadcrumb .= "<span style='color: black; font-weight: bold;'> • </span>";
			}
			$breadcrumb .= "<span style='color: black; font-weight: bold;'>" . $crumbs[$x] . "</span>";
		}
		if(strlen($breadcrumb) > 0)
		{
			$breadcrumb .= "<span style='color: black; font-weight: bold;'> • </span>";
		}
		$breadcrumb .= "<span style='color: black; font-weight: bold;'>" . $crumbs[$arrLength - 1] . ".ds</span>";
		
		
		return $before . $breadcrumb . $after;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="robots" content="noindex">
	<meta name="description" content="Created by Describe Compiler v0.9.2">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="author" content="Demon of reason">
	<link rel="stylesheet" type="text/css" href="css/styles.css" />
	<title>Listiary | Edit Article</title>
</head>
<body style="height:100%;">

	<div id="loadingModal" class="loading-modal" style="display:none;">
		<div class="loading-modal-content">
			<img src="img/LoadingGifs/spin2.gif" alt="loading">
		</div>
	</div>

	<!-- https://www.branchcms.com/learn/docs/overview/editor-v4/toolbar-buttons -->
	<div id="sidenav" style="z-index: 999;">
		<a href="javascript:void(0);" class="closebtn" onclick="closeNav()"
		style="
		position: absolute; top: 0; right: 0; font-size: 36px; margin-right: 10px;
		padding: 8px; text-decoration: none; color: #818181; display: block; transition: 0.3s;">&times;</a>
		
		<a href="javascript:void(0);" id="backbtn" onclick="backNav()"
		style="
		position: absolute; top: 0; right: 10; font-size: 20px; margin-right: 10px; margin-top: 7px;
		padding: 8px; text-decoration: none; color: #818181; display: none; transition: 0.3s;">🡠</a>
		<!-- &larr; -->
		
		<!-- MAIN -->
		<br class="Menu"/>
		<a href="javascript:showFileMenu();" class="Menu">
			<img src="img/Menu/folder-open-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">FILE</span>
		</a>
		<br class="Menu"/>
		<a href="javascript:showEditMenu();" class="Menu">
			<img src="img/Menu/pencil-line-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">EDIT</span>
		</a>
		<br class="Menu"/>
		<a href="javascript:showViewMenu();" class="Menu">
			<img src="img/Menu/eye-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">VIEW</span>
		</a>
		<br class="Menu"/>
		<a href="javascript:showInsertMenu();" class="Menu">
			<img src="img/Menu/circles-three-plus-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">INSERT</span>
		</a>
		<br class="Menu"/>
		<a href="javascript:showFormatMenu();" class="Menu">
			<img src="img/Menu/paragraph-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">FORMAT</span>
		</a>
		<br class="Menu"/>
		<a href="javascript:publicClick();" class="Menu">
			<img src="img/Menu/gear-six-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">OPTIONS</span>
		</a>
		
		<!-- FILE -->
		<br class="MenuFile" />
		<a href="<?php echo $newArticleUrl ?>" target="_blank" class="MenuFile">
			<img src="img/MenuFile/file-dashed-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">New Article</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFile">
			<img src="img/MenuFile/file-arrow-up-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Import</span>
		</a>
		<hr style="margin: 20px; display: none;" class="MenuFile" />
		<!-- <br class="MenuFile" /> -->
		<a href="javascript:publicClick();" class="MenuFile">
			<img src="img/MenuFile/file-arrow-down-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Save</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFile">
			<img src="img/MenuFile/file-arrow-down-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">SaveAs</span>
		</a>
		<hr style="margin: 20px; display: none;" class="MenuFile" />
		<!-- <br class="MenuFile" /> -->
		<a href="javascript:publicClick();" class="MenuFile">
			<img src="img/MenuFile/file-code-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">SaveAs .ds</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFile">
			<img src="img/MenuFile/file-text-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">SaveAs .txt</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFile">
			<img src="img/MenuFile/file-html-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">SaveAs .html</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFile">
			<img src="img/MenuFile/file-pdf-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">SaveAs .pdf</span>
		</a>
		
		<!-- EDIT -->
		<br  class="MenuEdit" />
		<a href="javascript:publicClick();" class="MenuEdit">
			<img src="img/MenuEdit/arrow-circle-left-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Undo</span>
		</a>
		<a href="javascript:publicClick();" class="MenuEdit">
			<img src="img/MenuEdit/arrow-circle-right-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Redo</span>
		</a>
		<br  class="MenuEdit" />
		<a href="javascript:publicClick();" class="MenuEdit">
			<img src="img/MenuEdit/scissors-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Cut</span>
		</a>
		<a href="javascript:publicClick();" class="MenuEdit">
			<img src="img/MenuEdit/copy-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Copy</span>
		</a>
		<a href="javascript:publicClick();" class="MenuEdit">
			<img src="img/MenuEdit/clipboard-text-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Paste</span>
		</a>
		<a href="javascript:publicClick();" class="MenuEdit">
			<img src="img/MenuEdit/backspace-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Delete</span>
		</a>
		<br  class="MenuEdit" />
		<a href="javascript:publicClick();" class="MenuEdit">
			<img src="img/MenuEdit/selection-all-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Select All</span>
		</a>
		<a href="javascript:publicClick();" class="MenuEdit">
			<img src="img/MenuEdit/magnifying-glass-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Find</span>
		</a>
		
		<!-- VIEW -->
		<br class="MenuView" />
		<a href="javascript:zoomIn();" class="MenuView">
			<img src="img/MenuView/magnifying-glass-plus-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Zoom In</span>
		</a>
		<a href="javascript:zoomOut();" class="MenuView">
			<img src="img/MenuView/magnifying-glass-minus-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Zoom Out</span>
		</a>
		<br class="MenuView" />
		<a href="javascript:publicClick();" class="MenuView">
			<img src="img/MenuView/eye-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px; font-style: italic;">Preview</span>
		</a>
		<a href="javascript:publicClick();" class="MenuView">
			<img src="img/MenuView/frame-corners-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px; font-style: italic;">Full Screen</span>
		</a>
		<a href="javascript:publicClick();" class="MenuView">
			<img src="img/MenuView/presentation-chart-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px; font-style: italic;">Visual Editor</span>
		</a>
		<br class="MenuView" />
		<a href="javascript:publicClick();" class="MenuView">
			<img src="img/MenuView/paragraph-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px; font-style: italic;">All Characters</span>
		</a>
		<a href="javascript:toggleHighlighting();" class="MenuView">
			<img src="img/MenuView/pencil-simple-line-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Highlighting</span>
		</a>
		
		<!-- INSERT -->
		<br class="MenuInsert" />
		<a href="javascript:publicClick();" class="MenuInsert">
			<img src="img/MenuInsert/smiley-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Special Character</span>
		</a>
		<br class="MenuInsert" />
		<a href="javascript:publicClick();" class="MenuInsert">
			<img src="img/MenuInsert/code-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Tag</span>
		</a>
		<a href="javascript:publicClick();" class="MenuInsert">
			<img src="img/MenuInsert/link-simple-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Link</span>
		</a>
		<a href="javascript:publicClick();" class="MenuInsert">
			<img src="img/MenuInsert/brackets-curly-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Decorator</span>
		</a>
		<a href="javascript:publicClick();" class="MenuInsert">
			<img src="img/MenuInsert/brackets-round-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Value</span>
		</a>
		<br class="MenuInsert" />
		<a href="javascript:publicClick();" class="MenuInsert">
			<img src="img/MenuInsert/git-commit-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Entry</span>
		</a>
		<a href="javascript:publicClick();" class="MenuInsert">
			<img src="img/MenuInsert/git-commit-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Empty Line</span>
		</a>
		<a href="javascript:publicClick();" class="MenuInsert">
			<img src="img/MenuInsert/git-commit-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Comment</span>
		</a>
		<a href="javascript:publicClick();" class="MenuInsert">
			<img src="img/MenuInsert/git-commit-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Line Comment</span>
		</a>
		
		<!-- FORMAT -->
		<br class="MenuFormat" />
		<a href="javascript:publicClick();" class="MenuFormat">
			<img src="img/MenuFormat/text-b-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Bold</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFormat">
			<img src="img/MenuFormat/text-italic-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Italic</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFormat">
			<img src="img/MenuFormat/text-underline-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Underline</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFormat">
			<img src="img/MenuFormat/text-strikethrough-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Strikethrough</span>
		</a>
		<br class="MenuFormat" />
		<a href="javascript:publicClick();" class="MenuFormat">
			<img src="img/MenuFormat/paint-brush-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Fore-Color</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFormat">
			<img src="img/MenuFormat/paint-bucket-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Back-Color</span>
		</a>
		<br class="MenuFormat" />
		<a href="javascript:publicClick();" class="MenuFormat">
			<img src="img/MenuFormat/spiral-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Clear Formatting</span>
		</a>
		<a href="javascript:publicClick();" class="MenuFormat">
			<img src="img/MenuFormat/palette-bold.png" style="width:24px; height:24px; vertical-align: text-top;" />
			<span style="padding-left:10px;">Clear Color</span>
		</a>
	</div>
	<span id="sidenav-trigger" onclick="openNav();">&#9776;</span>
	
	
	<!-- Add Edit Info Modal aka 'makeEditModal' version 1 - for bigger screens -->
	<!-- <div id="makeEditModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px);">
		
		<div style="background-color: #fff; margin: 50px auto; padding: 0px; border-radius: 8px; width: 80%; max-width: 800px; height: 80vh; overflow-y: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.3); position: relative; display: flex; flex-direction: column;">
			
			<!-- Header Bar --\>
			<div style="background-color: #99B3FF; padding: 15px 40px; border-bottom: 1px solid #88a3ef; position: sticky; top: 0; z-index: 10;">
				<h2 style="margin: 0; font-size: 1.5rem;">Save Changes</h2>
				<span onclick="closeMakeEditModal()" style="position: absolute; right: 20px; top: 12px; font-size: 28px; font-weight: bold; cursor: pointer; color: #333;">&times;</span>
			</div>
			
			<!-- The Content Area --\>
			<div id="modalInnerContent" style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column;">
				
				<div id="modalBody">
					<h4 style="margin-bottom: 10px;">Edit summary (Briefly describe your changes)</h4>

					<textarea id="summaryBoxModal" placeholder="Enter summary here..." rows="5" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 10px; resize: vertical; font-family: inherit; font-style: italic; font-size: 1.2em; color: green;" name="summary"></textarea>

					<div style="display: flex; align-items: center; gap: 8px;">
						<input type="checkbox" id="minorEditModal" name="minorEditCheckBox" style="width: 18px; height: 18px; cursor: pointer;">
						<label for="minorEditModal" style="cursor: pointer; font-size: 14px; font-style: italic; user-select: none;">
							This is a minor edit
						</label>
					</div>
				</div>

				<!-- GAP 1: Pushes the text paragraphs away from the editor --\>
				<div style="flex-grow: 1;"></div>
				
				<div id="paragraphs">
					<div style="margin-top: 10px;">
						Lorem ipsum dolor sit amet, consectetur adipiscing elit.<br/>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
					</div>
					<div style="margin-top: 15px;">
						Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
					</div>
				</div>

				<!-- GAP 3: Pushes everything away from the bottom buttons --\>
				<div style="flex-grow: 1;"></div>
			</div>

			<!-- Action Buttons --\>
			<div style="position: sticky; bottom: 0; background-color: #f9f9f9; padding: 15px; text-align: center; border-top: 1px solid #eee; z-index: 10;">
				<button type="button" onclick="closeMakeEditModal()" style="padding: 8px 16px; margin-right: 10px; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; background: white;">Cancel</button>
				<button type="button" onclick="saveSummaryAndSubmit()" style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Save & Publish</button>
			</div>
		</div>
	</div> -->

	
	<!-- Add Edit Info Modal aka 'makeEditModal' version 2 - for phones -->
	<div id="makeEditModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px);">
		
		<div style="background-color: #fff; margin: 55px auto; padding: 0px; border-radius: 8px; width: 92%; max-width: 800px; height: 80vh; overflow-y: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.3); position: relative; display: flex; flex-direction: column;">
			
			<!-- Header Bar -->
			<div style="background-color: #99B3FF; padding: 15px 40px 15px 20px; border-bottom: 1px solid #88a3ef; position: sticky; top: 0; z-index: 10;">
				<h2 style="margin: 0; font-size: 1.5rem;">Save Changes</h2>
				<span onclick="hideMakeEditModal();" style="position: absolute; right: 20px; top: 12px; font-size: 28px; font-weight: bold; cursor: pointer; color: #333;">&times;</span>
			</div>
			
			<!-- The Content Area -->
			<div id="modalInnerContent" style="padding: 0px 20px; flex-grow: 1; display: flex; flex-direction: column;">
				
				<div id="modalBody">
					<h4 style="margin-bottom: 10px;">Edit summary</h4>

					<textarea id="makeEditModal_SummaryBox" placeholder="Enter summary here..." rows="5" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 10px; resize: vertical; font-family: inherit; font-style: italic; font-size: 1em; color: green;" name="summary" onkeyup="document.getElementById('makeEditModal_isMinor').checked = this.value.trim().length === 0"></textarea>

					<div style="display: flex; align-items: center; gap: 8px;">
						<input type="checkbox" id="makeEditModal_isMinor" name="minorEditCheckBox" style="width: 18px; height: 18px; cursor: pointer;" checked>
						<label for="makeEditModal_isMinor" style="cursor: pointer; font-size: 14px; font-style: italic; user-select: none;">
							This is a minor edit
						</label>
					</div>
				</div>

				<!-- GAP 1: Pushes the text paragraphs away from the editor -->
				<div style="flex-grow: 1;"></div>
				
				<div id="paragraphs">
					<div style="margin-top: 10px;">
						Suggested edit captions:<br />
						
						"<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Add data.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Add data.
						</a>", "
						<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Remove data.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Remove data.
						</a>", "
						<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Update data.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Update data.
						</a>", "
						<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Enlist old data.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Enlist old data.
						</a>"<br />
						
						
						"<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Add entry to journal.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Add entry to journal.
						</a>", "
						<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Add entries to journal.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Add entries to journal.
						</a>", "
						<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Add to a journal.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Add to a journal.
						</a>"<br />
						
						
						"<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Add data.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Add data.
						</a>", "
						<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Correcting data.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Correcting data.
						</a>", "
						<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Housekeeping.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Housekeeping.
						</a>", "
						<a href="javascript:void(0);"
						   style="color: #006400; font-style: italic; text-decoration: none; cursor: pointer;"
						   onmouseover="this.style.textDecoration='underline';"
						   onmouseout="this.style.textDecoration='none';"
						   onclick="document.getElementById('makeEditModal_SummaryBox').value='Fix a typo.'; document.getElementById('makeEditModal_isMinor').checked = false">
						   Fix a typo.
						</a>"<br />
						
						
						
					</div>
					<div style="margin-top: 15px;">
						By publishing changes, you agree to the Terms of Use, and you irrevocably agree to release your contribution under the <a href="https://spdx.org/licenses/0BSD.html" target="_blank">0BSD License</a>.
						<br /><br />
						<span style="color:gray;">This is a developer copy of the software. Administrators installing or deploying the software should select a license that fits their needs. If no license is explicitly set during setup, the software will default to the <a href="https://spdx.org/licenses/0BSD.html" style="color:gray;" target="_blank">0BSD (Zero-Clause BSD) License</a>, the most permissive option.</span>
						<br><br>
					</div>
				</div>

				<!-- GAP 3: Pushes everything away from the bottom buttons -->
				<div style="flex-grow: 1;"></div>
			</div>

			<!-- Action Buttons -->
			<div style="position: sticky; bottom: 0; background-color: #f9f9f9; padding: 15px; text-align: center; border-top: 1px solid #eee; z-index: 10;">
				<button type="button" onclick="hideMakeEditModal();" style="padding: 8px 16px; margin-right: 10px; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; background: white;">Cancel</button>
				<button type="button" onclick='submitFormS("<?php echo $editfileUrl; ?>", editor.getValue()); hideMakeEditModal();' style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Save & Publish</button>
			</div>
		</div>
	</div>
	
	
	<!-- Skeleton -->
	<table id="skeleton">
		<tbody>
			<tr>
				<td colspan="10" id="LargeTopContainer">
					<?php echo $breadcrumb; ?>
				</td>
			</tr>
			<tr>
				<td colspan="10" id="LargeContainer">
					<form id="editForm" style='display: flex; flex-direction: column; width:100%; height:100%;'>
						<div style="display: none; flex-grow: 1; display: flex; flex-direction: column; border: 1px solid #bfbaba;">
							<textarea spellcheck="false" id="editArea" style='flex-grow: 1; width: 100%; height: auto; resize: vertical; box-sizing: border-box;' name='content'><?php echo $curArticle; ?></textarea>
						</div>
						<div id="summary-div" style='margin-top: 0px; width:100%; min-height:60px; background-color: #fff; padding-top: 0px; padding-bottom: 0px; border: 2px white;'>
							<div style='display: none; margin-bottom: 10px; margin-left: 1%;'>
								Edit summary (Briefly describe your changes)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="checkbox" id="minorEdit" name="minorEditCheckBox">
								<label for="minorEdit"> This is a minor edit</label>
							</div>
							<input id="summaryBox" type='text' style='display: none; margin-left: 1%; width:96%; margin-bottom: 10px;' name='summary' spellcheck='true'>
							<input type='button' style='display: none; margin-left: 1%; background-color: #0000ff; border: 1; background: none; box-shadow: none; border-radius: 0px;' value='Publish changes' onclick='submitFormS("<?php echo $editfileUrl; ?>", editor.getValue());'>
							<input type='button' style='display: none; margin-left: 1%; color: red; border: 1; background: none; box-shadow: none; border-radius: 0px; margin-left: 10px;' value='Cancel' onclick='hideEditor(null);'>

							<div id="BottomToolbar" colspan="10" style="text-align: center; padding: 10px; background-color: #fff;">
								<img src="img/check-thin.png" style="border: 1px solid black; border-radius: 40px; background-color: #ccc; padding:4px; height: 30px; width: 30px; cursor: pointer; margin: 7px;" onclick='showMakeEditModal();' />
								<img src="img/eye-closed-thin.png" style="border: 1px solid black; border-radius: 40px; background-color: #ccc; padding:4px; height: 30px; width: 30px; cursor: pointer; margin: 7px;" onclick='showMakeEditModal();' />

								<!-- <img src="img/x-thin.png" style="border: 1px solid black; border-radius: 40px; background-color: #FF7F7F; padding:4px; height: 30px; width: 30px; cursor: pointer; margin: 7px;" onclick="javascript:hideEditor('https://development.listiary.net/m.index.php?article=radiowatch.rnode');" /> -->
								<img src="img/x-thin.png" style="border: 1px solid black; border-radius: 40px; background-color: #FF7F7F; padding:4px; height: 30px; width: 30px; cursor: pointer; margin: 7px;" onclick="hideEditor('https://development.listiary.net/m.index.php?article=<?php echo $returnUrl; ?>');" />
							</div>
						</div>
					</form>
				</td>
			</tr>
			
		</tbody>
	</table>
	
	<!-- Create a simple CodeMirror instance -->
	<link rel="stylesheet" href="js/lib/codemirror.css">
	<script src="js/lib/codemirror.js"></script>
	<script>
		var myTextarea = document.getElementById('editArea');
		var editor = CodeMirror.fromTextArea(myTextarea, {lineNumbers: true});
		
		//var lines = editor.lineCount();
		// Find the wrapping div (textarea-wrapper)
		var wrapper = myTextarea.parentElement; // this should be the div with border

		// Get its computed height (pixels)
		var wrapperHeight = wrapper.clientHeight; // height available for textarea

		// Set CodeMirror editor size to fill wrapper height
		editor.setSize(null, wrapperHeight + 20);

		// Optional: update size on window resize
		window.addEventListener('resize', function() {
			var newHeight = wrapper.clientHeight;
			editor.setSize(null, newHeight + 20);
		});
	</script>
	<script src="js/scripts.js"></script>
</body>
</html>
