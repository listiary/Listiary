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
		// $step1State = "succeeded";		//inactive, active, open, succeeded
		// $step2State = "active";	//inactive, active, open, succeeded
		// $step3State = "inactive";	//inactive, active, open, succeeded
		// $step4State = "inactive";	//inactive, active, open, succeeded
		// $step5State = "inactive";	//inactive, active, open, succeeded
	$step1State = "active";		//inactive, active, open, succeeded
	$step2State = "inactive";	//inactive, active, open, succeeded
	$step3State = "active";	//inactive, active, open, succeeded
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

	//step 3 vars
	$allowSessionOverHttp = false;
	$isProduction = false;

	$maxLoginAttemptsPerIp = 10;
	$maxLoginAttemptsPerEmail = 5;
	$waitTimeLogin = 15;
	$keepOldRecordsLogin = 7;
	
	$maxResetAttemptsPerIp = 10;
	$maxResetAttemptsPerEmail = 5;
	$waitTimeReset = 16;
	$keepOldRecordsReset = 7;
	
	$maxRegisterSuccessesPerIp = 2;
	$waitTimeRegister = 10080;
	$keepOldRecordsRegister = 14;

	//step 3 funcs
	function setIsProductionDeployment(): void {

		global $isProduction;

		$isProduction = (
			isset($_POST['isprod']) && $_POST['isprod'] === '1'
		) ? 'true' : 'false';
	}
	function setIsHttpSessionAllowed(): void {
	
		global $allowSessionOverHttp;

		$isDisallowed = isset($_POST['ishttpsesdisallowed']) 
			&& $_POST['ishttpsesdisallowed'] === '1';

		$allowSessionOverHttp = $isDisallowed ? 'false' : 'true';
	}

	function setMaxLoginAttemptsPerIp(): void {

		global $maxLoginAttemptsPerIp;

		if (isset($_POST['maxLoginAttemptsIp'])) {
			$value = filter_input(INPUT_POST, 
				'maxLoginAttemptsIp', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 5, 'max_range' => 50]]
			);

			if ($value !== false) 
			{
				$maxLoginAttemptsPerIp = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("maxLoginAttemptsIp must be an integer between 5 and 50.");
			}
		}
		else
		{
			throw new InvalidArgumentException("maxLoginAttemptsIp is not set.");
		}
	}
	function setMaxLoginAttemptsPerEmail(): void {

		global $maxLoginAttemptsPerEmail;

		if (isset($_POST['maxLoginAttemptsEmail'])) {
			$value = filter_input(INPUT_POST, 
				'maxLoginAttemptsEmail', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 5, 'max_range' => 50]]
			);

			if ($value !== false) 
			{
				$maxLoginAttemptsPerEmail = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("maxLoginAttemptsEmail must be an integer between 5 and 50.");
			}
		}
		else
		{
			throw new InvalidArgumentException("maxLoginAttemptsEmail is not set.");
		}
	}
	function setWaitTimeLogin(): void {

		global $waitTimeLogin;

		if (isset($_POST['waitTimeLogin'])) {
			$value = filter_input(INPUT_POST, 
				'waitTimeLogin', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 5, 'max_range' => 720]]
			);

			if ($value !== false) 
			{
				$waitTimeLogin = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("waitTimeLogin must be an integer between 5 and 720.");
			}
		}
		else
		{
			throw new InvalidArgumentException("waitTimeLogin is not set.");
		}
	}
	function setKeepOldRecordsLogin(): void {

		global $keepOldRecordsLogin;

		if (isset($_POST['keepOldRecordsLogin'])) {
			$value = filter_input(INPUT_POST, 
				'keepOldRecordsLogin', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 5, 'max_range' => 731]]
			);

			if ($value !== false) 
			{
				$keepOldRecordsLogin = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("keepOldRecordsLogin must be an integer between 5 and 731.");
			}
		}
		else
		{
			throw new InvalidArgumentException("keepOldRecordsLogin is not set.");
		}
	}
	
	function setMaxResetAttemptsPerIp(): void {

		global $maxResetAttemptsPerIp;

		if (isset($_POST['maxResetAttemptsIp'])) {
			$value = filter_input(INPUT_POST, 
				'maxResetAttemptsIp', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 5, 'max_range' => 50]]
			);

			if ($value !== false) 
			{
				$maxResetAttemptsPerIp = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("maxResetAttemptsIp must be an integer between 5 and 50.");
			}
		}
		else
		{
			throw new InvalidArgumentException("maxResetAttemptsIp is not set.");
		}
	}
	function setMaxResetAttemptsPerEmail(): void {

		global $maxResetAttemptsPerEmail;

		if (isset($_POST['maxResetAttemptsEmail'])) {
			$value = filter_input(INPUT_POST, 
				'maxResetAttemptsEmail', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 5, 'max_range' => 50]]
			);

			if ($value !== false) 
			{
				$maxResetAttemptsPerEmail = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("maxResetAttemptsEmail must be an integer between 5 and 50.");
			}
		}
		else
		{
			throw new InvalidArgumentException("maxResetAttemptsEmail is not set.");
		}
	}
	function setWaitTimeReset(): void {

		global $waitTimeReset;

		if (isset($_POST['waitTimeReset'])) {
			$value = filter_input(INPUT_POST, 
				'waitTimeReset', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 16, 'max_range' => 1440]]
			);

			if ($value !== false) 
			{
				$waitTimeReset = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("waitTimeReset must be an integer between 16 and 1440.");
			}
		}
		else
		{
			throw new InvalidArgumentException("waitTimeReset is not set.");
		}
	}
	function setKeepOldRecordsReset(): void {

		global $keepOldRecordsReset;

		if (isset($_POST['keepOldRecordsReset'])) {
			$value = filter_input(INPUT_POST, 
				'keepOldRecordsReset', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 1, 'max_range' => 731]]
			);

			if ($value !== false) 
			{
				$keepOldRecordsReset = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("keepOldRecordsReset must be an integer between 1 and 731.");
			}
		}
		else
		{
			throw new InvalidArgumentException("keepOldRecordsReset is not set.");
		}
	}
	
	function setMaxRegSuccessPerIp(): void {
		
		global $maxRegisterSuccessesPerIp;

		if (isset($_POST['maxRegSuccessIp'])) {
			$value = filter_input(INPUT_POST, 
				'maxRegSuccessIp', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 1, 'max_range' => 10]]
			);

			if ($value !== false) 
			{
				$maxRegisterSuccessesPerIp = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("maxRegSuccessIp must be an integer between 1 and 10.");
			}
		}
		else
		{
			throw new InvalidArgumentException("maxRegSuccessIp is not set.");
		}
	}
	function setWaitTimeRegSuccess(): void {
		
		global $waitTimeRegister;

		if (isset($_POST['waitTimeRegSuccess'])) {
			$value = filter_input(INPUT_POST, 
				'waitTimeRegSuccess', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 1, 'max_range' => 10]]
			);

			if ($value !== false) 
			{
				$waitTimeRegister = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("waitTimeRegSuccess must be an integer between 1 and 10.");
			}
		}
		else
		{
			throw new InvalidArgumentException("waitTimeRegSuccess is not set.");
		}
	}
	function setKeepOldRecordsRegister(): void {
		
		global $keepOldRecordsRegister;

		if (isset($_POST['keepOldRecordsRegister'])) {
			$value = filter_input(INPUT_POST, 
				'keepOldRecordsRegister', 
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 1, 'max_range' => 731]]
			);

			if ($value !== false) 
			{
				$keepOldRecordsRegister = $value;
			} 
			else 
			{
				throw new InvalidArgumentException("keepOldRecordsRegister must be an integer between 1 and 731.");
			}
		}
		else
		{
			throw new InvalidArgumentException("keepOldRecordsRegister is not set.");
		}
	}
	
	function writeSettingsConfig(): void {
		
		global $allowSessionOverHttp, $isProduction, 
			$maxLoginAttemptsPerIp, $maxLoginAttemptsPerEmail, $waitTimeLogin, $keepOldRecordsLogin,
			$maxResetAttemptsPerIp, $maxResetAttemptsPerEmail, $waitTimeReset, $keepOldRecordsReset,
			$maxRegisterSuccessesPerIp, $waitTimeRegister, $keepOldRecordsRegister;
		
		//read config template
		$path = __DIR__ . '/_installer_templates/_settings_config_template.php';
		if (!is_readable($path)) throw new RuntimeException("Settings Config template not readable.");
		$template = file_get_contents($path);
		
		//fill up values
		$updated = str_replace('*ALLOW_SESSION_OVER_HTTP_VALUE*', $allowSessionOverHttp, $template);
		$updated = str_replace('*IS_PRODUCTION_VALUE*', $isProduction, $updated);
		
		$updated = str_replace('*MAX_LOGIN_ATTEMPTS_PER_IP_VALUE*', $maxLoginAttemptsPerIp, $updated);
		$updated = str_replace('*MAX_LOGIN_ATTEMPTS_PER_EMAIL_VALUE*', $maxLoginAttemptsPerEmail, $updated);
		$updated = str_replace('*WAIT_TIME_LOGIN_VALUE*', $waitTimeLogin, $updated);
		$updated = str_replace('*KEEP_OLD_RECORDS_VALUE*', $keepOldRecordsLogin, $updated);
		$updated = str_replace('*MAX_RESET_ATTEMPTS_PER_IP_VALUE*', $maxResetAttemptsPerIp, $updated);
		$updated = str_replace('*MAX_RESET_ATTEMPTS_PER_EMAIL_VALUE*', $maxResetAttemptsPerEmail, $updated);
		$updated = str_replace('*WAIT_TIME_RESET_VALUE*', $waitTimeReset, $updated);
		$updated = str_replace('*KEEP_OLD_RECORDS_RESETS_VALUE*', $keepOldRecordsReset, $updated);
		$updated = str_replace('*MAX_REGISTER_SUCCESSES_PER_IP_VALUE*', $maxRegisterSuccessesPerIp, $updated);
		$updated = str_replace('*WAIT_TIME_REGISTER_VALUE*', $waitTimeRegister, $updated);
		$updated = str_replace('*KEEP_OLD_RECORDS_REGISTER_VALUE*', $keepOldRecordsRegister, $updated);

		
		//save config
		$savepath = __DIR__ . '/_configs/_settings_config.php';
		if (file_put_contents($savepath, $updated) === false)
		{
			throw new RuntimeException("Could not save config.");
		}
	}
	function doStep3Success(): void {
		
		global 
			$step1State, $step2State,
			$compilerUrl, $wikiUrl, $sqlServerUrl, $sqlDbName, $sqlDbUser, $sqlDbPass,
			$compilerUrl_err, $wikiUrl_err, $sqlServerUrl_err, $sqlDbName_err, $sqlDbUser_err, $sqlDbPass_err;
		
		//reflect success in the UI
		$step1State = "succeeded";
		$step2State = "succeeded";
		$step3State = "succeeded";
		$step4State = "active";
	}


	

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
		if ($_POST["form_type"] === "setsettingsconfig"){

			$step3State = 'open';
			
			//validate inputs
			setIsProductionDeployment();
			setIsHttpSessionAllowed();
			
			setMaxLoginAttemptsPerIp();
			setMaxLoginAttemptsPerEmail();
			setWaitTimeLogin();
			setKeepOldRecordsLogin();
			
			setMaxResetAttemptsPerIp();
			setMaxResetAttemptsPerEmail();
			setWaitTimeReset();
			setKeepOldRecordsReset();
			
			setMaxRegSuccessPerIp();
			setWaitTimeRegSuccess();
			setKeepOldRecordsRegister();
			
			//if we havent thrown, proceed with writing
			//we can also output error to a var, and test if this var is empty
			writeSettingsConfig();
			doStep3Success();
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
								echo '<button onclick="step3_ShowModal();" class="btn btn--orange">3. Set up various settings</button>';
								echo '<br><br>'; 
							}
							
							//state 4
							if($step4State == "inactive")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>4. Upload PHP files</button><br>';
							}
							else if($step4State == "succeeded")
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
							if($step5State == "inactive")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>5. Visit your new wiki homepage</button><br>';
							}
							else if($step5State == "succeeded")
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
				</div>
				
				<!-- Action Buttons -->
				<div class="listiaryConsoleModalButtonsWrapper">
					<button class="buttonNo" type="button" onclick="step2_CloseModal();">Cancel</button>
					<button id="step2Button" class="buttonYes" type="button" name="form_dbconfig">Run</button>
				</div>
			</form>
		</div>

		<!-- Set settings dialog modal box -->
		<div <?php if($step3State != 'open') echo "style='display:none;'" ?> id="settingsConfigModal" class="listiaryYesNoModalWrapper">
			<form class="listiaryYesNoModalCorpus" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			<input type="hidden" name="form_type" value="setsettingsconfig">
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
				
				<!-- Header Bar -->
				<div class="listiaryYesNoModalHeaderBar">
					<h2>Settings Config</h2>
					<span onclick="hideListiaryModal('settingsConfigModal');">&times;</span>
				</div>
				
				<!-- The Content Area -->
				<div id="modalInnerContent" class="listiaryYesNoModalInnerContent">
					
					<div class="listiaryYesNoModalBody">
						<label class="listiaryCheckboxWrapper">
							<input type="checkbox" 
								name="isprod"
								id="isProductionCheckBox"
								value="1" checked />
							<span>Production Mode Deployment</span>
						</label>
						<label class="listiaryCheckboxWrapper">
							<input type="checkbox" 
								name="ishttpsesdisallowed"
								id="isHttpSessionAllowedCheckBox"
								value="1" checked />
							<span> Disallow Session over HTTP</span>
						</label>
						
						<!-- GAP 1: Pushes the text paragraphs away from the editor -->
						<div style="flex-grow: 1;"></div>

						<label class="listiaryUpDownLabel">
							<span>Max login attempts per IP :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="maxLoginAttemptsIp" 
								value="10" min="5" max="50" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>
						<label class="listiaryUpDownLabel">
							<span>Max login attempts per Email :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="maxLoginAttemptsEmail" 
								value="5" min="5" max="50" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>
						<label class="listiaryUpDownLabel">
							<span>Wait time - login (minutes) :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="waitTimeLogin" 
								value="15" min="5" max="720" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>
						<label class="listiaryUpDownLabel">
							<span>Keep old records (days) :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="keepOldRecordsLogin" 
								value="7" min="1" max="731" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>

						<!-- GAP 1: Pushes the text paragraphs away from the editor -->
						<div style="flex-grow: 1;"></div>

						<label class="listiaryUpDownLabel">
							<span>Max password reset attempts per IP :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="maxResetAttemptsIp" 
								value="5" min="5" max="50" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>
						<label class="listiaryUpDownLabel">
							<span>Max password reset attempts per Email :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="maxResetAttemptsEmail" 
								value="5" min="5" max="50" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>
						<label class="listiaryUpDownLabel">
							<span>Wait time - password reset (minutes) :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="waitTimeReset" 
								value="16" min="16" max="1440" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>
						<label class="listiaryUpDownLabel">
							<span>Keep old records (days) :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="keepOldRecordsReset" 
								value="7" min="1" max="731" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>
						
						<!-- GAP 1: Pushes the text paragraphs away from the editor -->
						<div style="flex-grow: 1;"></div>
						
						<label class="listiaryUpDownLabel">
							<span>Max register successes per IP :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="maxRegSuccessIp" 
								value="2" min="1" max="10" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>
						<label class="listiaryUpDownLabel">
							<span>Wait time - register account (days) :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="waitTimeRegSuccess" 
								value="1" min="1" max="10" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>
						<label class="listiaryUpDownLabel">
							<span>Keep old records (days) :</span>
							<input class="listiaryUpDownInput" 
								type="number" 
								name="keepOldRecordsRegister" 
								value="14" min="1" max="731" 
								readonly />
							<div class="listiaryUpDownControl">
								[<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepDown()">&minus;</span>
								<span>|</span>
								<span class="listiaryUpDownControlTrigger" onclick="this.parentElement.previousElementSibling.stepUp()">&plus;</span>]
							</div>
						</label>

					</div>

					<!-- GAP 1: Pushes the text paragraphs away from the editor -->
					<div style="flex-grow: 1;"></div>
				</div>

				<!-- Action Buttons -->
				<div class="listiaryYesNoModalButtonsWrapper">
					<button class="buttonNo" type="button" onclick="hideListiaryModal('settingsConfigModal');">Cancel</button>
					<button class="buttonYes" type="submit" name="form_settingsconfig">Save</button>
				</div>
			</form>
		</div>


	</body>

</html>