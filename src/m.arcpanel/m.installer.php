<?php

	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_commonlib.php";
	set_exception_handler('catchEx');
	
	// More Presets
	startSecureSession();
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
	
	// UI - Define variables and initialize with empty values
	// $step1State = "active";		//inactive, active, open, succeeded
	// $step2State = "inactive";	//inactive, active, open, succeeded
	// $step3State = "inactive";	//inactive, active, open, succeeded
	// $step4State = "inactive";	//inactive, active, open, succeeded
	// $step5State = "inactive";	//inactive, active, open, succeeded
	$step1State = "succeeded";		//inactive, active, open, succeeded
	$step2State = "active";	//inactive, active, open, succeeded
	$step3State = "inactive";	//inactive, active, open, succeeded
	$step4State = "inactive";	//inactive, active, open, succeeded
	$step5State = "inactive";	//inactive, active, open, succeeded
	
	//step 1 vars
	$compilerUrl = "";
	$wikiUrl = "";
	$sqlServerUrl = "";
	$sqlDbName = "";
	$sqlDbUser = "";
	$sqlDbPass = "";
	$compilerUrl_err = "";
	$wikiUrl_err = "";
	$sqlServerUrl_err = "";
	$sqlDbName_err = "";
	$sqlDbUser_err = "";
	$sqlDbPass_err = "";

	//step 1 funcs
	function validateCompilerUrl(): void {
		
		global $compilerUrl, $compilerUrl_err;

		// Validate compiler URL
		$compiler = trim($_POST["compilerurl"]);
        if(empty($compiler))
        {
            $compilerUrl_err = " (empty!)";
        }
		elseif (!filter_var($compiler, FILTER_VALIDATE_URL))
		{
			$compilerUrl_err = " (invalid!)";
		}
		else
		{
			$compilerUrl = $compiler;
		}
	}
	function validateWikiUrl(): void {
		
		global $wikiUrl, $wikiUrl_err;

		// Validate wiki URL
		$wiki = trim($_POST["wikiurl"]);
        if(empty($wiki))
        {
            $wikiUrl_err = " (empty!)";
        }
		elseif (!filter_var($wiki, FILTER_VALIDATE_URL))
		{
			$wikiUrl_err = " (invalid!)";
		}
		else
		{
			$wikiUrl = $wiki;
		}
	}
	function validateSqlUrl(): void {
		
		global $sqlServerUrl, $sqlServerUrl_err;
		
		// Validate SQL host name
		$host = trim($_POST["dburl"]);
        if(empty($host))
        {
            $sqlServerUrl_err = " (empty!)";
        }
		elseif (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))
		{
			$sqlServerUrl_err = " (invalid!)";
		}
		else
		{
			$sqlServerUrl = $host;
		}
	}
	function validateDbName(): void {
		
		global $sqlDbName, $sqlDbName_err;
		
		// Validate db name
		$dbname = trim($_POST["dbname"]);
        if(empty($dbname))
        {
            $sqlDbName_err = " (empty!)";
        }
		elseif (!preg_match('/^[A-Za-z0-9_-]{1,128}$/', $dbname))
		{
			$sqlDbName_err = " (invalid!)";
		}
		else
		{
			$sqlDbName = $dbname;
		}
	}
	function validateDbUser(): void {
		
		global $sqlDbUser, $sqlDbUser_err;
		
		// Validate DB username
		$sqluname = trim($_POST["dbuser"]);
		if (empty($sqluname)) 
		{
			$sqlDbUser_err = " (empty!)";
		} 
		elseif (!preg_match('/^[A-Za-z0-9._@\\\\-]{1,128}$/', $sqluname)) 
		{
			$sqlDbUser_err = " (invalid!)";
		} 
		else
		{
			$sqlDbUser = $sqluname;
		}
	}
	function validateDbPass(): void {
		
		global $sqlDbPass, $sqlDbPass_err;
		
		// Validate DB password
		$pwd = $_POST["dbpass"];
		if (empty($pwd)) 
		{
			$sqlDbPass_err = " (empty!)";
		}
		elseif (strlen($pwd) < 8)
		{
			$sqlDbPass_err = " (must 8 chars or more!)";
		} 
		else 
		{
			$sqlDbPass = $pwd;
		}
	}
	function writeConfig(): void {
		
		global $compilerUrl, $wikiUrl, $sqlServerUrl, $sqlDbName, $sqlDbUser, $sqlDbPass;
		
		//read config template
		$path = __DIR__ . '/_installer_templates/_config_template.php';
		if (!is_readable($path)) throw new RuntimeException("Config template not readable.");
		$template = file_get_contents($path);
		
		//fill up values
		$updated = str_replace('*COMPILER_URL_VALUE*', $compilerUrl, $template);
		$updated = str_replace('*BASE_URL_VALUE*', $wikiUrl, $updated);
		$updated = str_replace('*SERVER_VALUE*', $sqlServerUrl, $updated);
		$updated = str_replace('*USERNAME_VALUE*', $sqlDbUser, $updated);
		$updated = str_replace('*PASSWORD_VALUE*', $sqlDbPass, $updated);
		$updated = str_replace('*DATABASE_NAME_VALUE*', $sqlDbName, $updated);
		
		//save config
		$savepath = __DIR__ . '/_configs/_config.php';
		if (file_put_contents($savepath, $updated) === false)
		{
			throw new RuntimeException("Could not save config.");
		}
	}
	function doStep1Success(): void {
		
		global 
			$step1State, $step2State,
			$compilerUrl, $wikiUrl, $sqlServerUrl, $sqlDbName, $sqlDbUser, $sqlDbPass,
			$compilerUrl_err, $wikiUrl_err, $sqlServerUrl_err, $sqlDbName_err, $sqlDbUser_err, $sqlDbPass_err;
		
		//reflect success in the UI
		$compilerUrl = "";
		$wikiUrl = "";
		$sqlServerUrl = "";
		$sqlDbName = "";
		$sqlDbUser = "";
		$sqlDbPass = "";
		$compilerUrl_err = "";
		$wikiUrl_err = "";
		$sqlServerUrl_err = "";
		$sqlDbName_err = "";
		$sqlDbUser_err = "";
		$sqlDbPass_err = "";
		$step1State = "succeeded";
		$step2State = "active";
	}

	//step 2 vars

	

	// Processing form data when form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Verify CSRF token
		if(isCsrfTokenValid() == false)
		{
			http_response_code(403);
			throw new RuntimeException('Invalid CSRF token.' . ' : ' . $_POST['csrf_token']);
		}
			
		//handle DB config creator form (step 1)
		if ($_POST["form_type"] === "setdbconfig"){

			$step1State = 'open';
			
			//validate inputs
			validateCompilerUrl();
			validateWikiUrl();
			validateSqlUrl();
			validateDbName();
			validateDbUser();
			validateDbPass();
			
			// Check input errors before proceeding
			if(empty($compilerUrl_err) && empty($wikiUrl_err) && empty($sqlServerUrl_err) 
				&& empty($sqlDbName_err) && empty($sqlDbUser_err) && empty($sqlDbPass_err))
			{
				writeConfig();
				doStep1Success();
			}
		}
		
		//handle DB table creator form (step 2)
		if ($_POST["form_type"] === "createdbtables"){

		
		}
	}
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
		<link rel="stylesheet" href="css/modals.css">
		<script type="text/javascript" src="js/scripts.dom-manipulate.js"></script>
		<script type="text/javascript" src="js/m.installer.js"></script>
		<script type="text/javascript" src="js/modals.js"></script>
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
						
						<!-- steps UI -->
						<?php 
							//state 1
							if($step1State == "active" || $step1State == "open") 
							{
								echo '<button onclick="javascript:showListiaryModal(\'mainConfigModal\');" class="btn btn--orange">1. Set main config</button><br>';
								echo '<br>'; 
							}
							else if($step1State == "succeeded")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>✔ Set main config</button><br>';
							}
							else
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange">1. Set main config</button><br>';
							}
							
							//state 2
							if($step2State == "inactive")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>2. Create DataBase tables</button><br>';
							}
							else if($step2State == "succeeded")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>✔ Create DataBase tables</button><br>';
							}
							else
							{
								echo '<br>';
								echo '<button onclick="step2_ShowModal();" class="btn btn--orange">2. Create DataBase tables</button>';
								echo '<br><br>'; 
							}
							
							//state 3
							if($step3State == "inactive")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>3. Set up various settings</button><br>';
							}
							else if($step3State == "succeeded")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>✔ Set up various settings</button><br>';
							}
							else
							{
								echo '<br>';
								echo '<button onclick="javascript:showListiaryModal(\'setSettingsModal\');" class="btn btn--orange">3. Set up various settings</button>';
								echo '<br><br>'; 
							}
							
							//state 4
							if($step3State == "inactive")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>4. Upload PHP files</button><br>';
							}
							else if($step3State == "succeeded")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>✔ Upload PHP files</button><br>';
							}
							else
							{
								echo '<br>';
								echo '<button onclick="javascript:showListiaryModal(\'setSettingsModal\');" class="btn btn--orange">4. Upload PHP files</button>';
								echo '<br><br>'; 
							}
							
							//state 5
							if($step3State == "inactive")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>5. Visit your new wiki homepage</button><br>';
							}
							else if($step3State == "succeeded")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>✔ Visit your new wiki homepage</button><br>';
							}
							else
							{
								echo '<br>';
								echo '<button onclick="javascript:showListiaryModal(\'setSettingsModal\');" class="btn btn--orange">5. Visit your new wiki homepage</button>';
								echo '<br><br>'; 
							}
						?>
					</td>
				</tr>
			</tbody>
		</table>
		
		<!-- Yes/No dialog modal box -->
		<div <?php if($step1State != 'open') echo "style='display:none;'" ?> id="mainConfigModal" class="listiaryYesNoModalWrapper">
			<form class="listiaryYesNoModalCorpus" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			<input type="hidden" name="form_type" value="setdbconfig">
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
				
				<!-- Header Bar -->
				<div class="listiaryYesNoModalHeaderBar">
					<h2>Database Config</h2>
					<span onclick="hideListiaryModal('mainConfigModal');">&times;</span>
				</div>
				
				<!-- The Content Area -->
				<div id="modalInnerContent" class="listiaryYesNoModalInnerContent">
					
					<div class="listiaryYesNoModalBody">
						<label for="describeCompilerUrlInput">Describe Compiler <span class="invalid-feedback"><?php echo $compilerUrl_err; ?></span></label>
						<input 
							type="text" name="compilerurl"
							id="describeCompilerUrlInput" 
							class="<?php echo (!empty($compilerUrl_err)) ? 'is-invalid' : ''; ?>" 
							value="<?php echo $compilerUrl; ?>"
							placeholder="AWS microservice url here" />
						
						<label for="wikiHomeUrlInput">Wiki home <span class="invalid-feedback"><?php echo $wikiUrl_err; ?></span></label>
						<input 
							type="text" name="wikiurl"
							id="wikiHomeUrlInput" 
							class="<?php echo (!empty($wikiUrl_err)) ? 'is-invalid' : ''; ?>" 
							value="<?php echo $wikiUrl; ?>"
							placeholder="your wiki home url here" />
						
						<!-- GAP 1: Pushes the text paragraphs away from the editor -->
						<div style="flex-grow: 1;"></div>
						
						<label for="sqlServerNameInput">Sql Server <span class="invalid-feedback"><?php echo $sqlServerUrl_err; ?></span></label>
						<input 
							type="text" name="dburl"
							id="sqlServerNameInput" 
							class="<?php echo (!empty($sqlServerUrl_err)) ? 'is-invalid' : ''; ?>" 
							value="<?php echo $sqlServerUrl; ?>"
							placeholder="your sql server name" />
						
						<label for="sqlDatabaseNameInput">Sql Database Name <span class="invalid-feedback"><?php echo $sqlDbName_err; ?></span></label>
						<input 
							type="text" name="dbname"
							id="sqlDatabaseNameInput" 
							class="<?php echo (!empty($sqlDbName_err)) ? 'is-invalid' : ''; ?>" 
							value="<?php echo $sqlDbName; ?>"
							placeholder="your empty sql database name" />
						
						<label for="sqlDatabaseUserNameInput">Sql Database User <span class="invalid-feedback"><?php echo $sqlDbUser_err; ?></span></label>
						<input 
							type="text" name="dbuser"
							id="sqlDatabaseUserNameInput" 
							class="<?php echo (!empty($sqlDbUser_err)) ? 'is-invalid' : ''; ?>" 
							value="<?php echo $sqlDbUser; ?>"
							placeholder="your sql database username" />
						
						<label for="sqlDatabasePassInput">Sql Database Password <span class="invalid-feedback"><?php echo $sqlDbPass_err; ?></span></label>
						<input 
							type="password" name="dbpass"
							id="sqlDatabasePassInput" 
							class="<?php echo (!empty($sqlDbPass_err)) ? 'is-invalid' : ''; ?>" 
							value="<?php echo $sqlDbPass; ?>"
							placeholder="your sql database password" />
					</div>

					<!-- GAP 1: Pushes the text paragraphs away from the editor -->
					<div style="flex-grow: 1;"></div>
				</div>

				<!-- Action Buttons -->
				<div class="listiaryYesNoModalButtonsWrapper">
					<button class="buttonNo" type="button" onclick="hideListiaryModal('mainConfigModal');">Cancel</button>
					<button class="buttonYes" type="submit" name="form_dbconfig">&nbsp;&nbsp;Save&nbsp;&nbsp;</button>
				</div>
			</form>
		</div>

		<!-- Create DB dialog modal box -->
		<div <?php if($step2State != 'open') echo "style='display:none;'" ?> id="setDbTablesModal" class="listiaryConsoleModalWrapper">
			<form class="listiaryConsoleModalCorpus" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			<input type="hidden" name="form_type" value="createdbtables">
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
			
				<!-- Header Bar -->
				<div class="listiaryConsoleModalHeaderBar">
					<h2>Database Tables</h2>
					<span onclick="step2_CloseModal();">&times;</span>
				</div>
				
				<!-- The Content Area -->
				<div id="modalInnerContent" class="listiaryConsoleModalInnerContent">
					
					<div class="listiaryConsoleModalBody">
						<pre id="step2Console" class="listiaryConsoleModalConsoleBox"></pre>
					</div>
				
				<!-- Action Buttons -->
				<div class="listiaryConsoleModalButtonsWrapper">
					<button class="buttonNo" type="button" onclick="step2_CloseModal();">Cancel</button>
					<button id="step2Button" class="buttonYes" type="button" name="form_dbconfig">Run</button>
				</div>
			</form>
		</div>
		
		
		
	</body>
</html>