<?php

	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_commonlib.php";
	require_once __DIR__ . "/php/_wikiinstaller.php";
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
	$installerMessage = "You are about to install Listiary v0.X.<br />Proceed with the steps below to install Listiary on your hosting by going through the steps.<br /><br />";
	$step0State = $step1State = $step2State = $step3State = $step4State = "inactive";
	$step0State = "active";			//inactive, active, open, succeeded
	$step1State = "inactive";		//inactive, active, open, succeeded
	$step2State = "inactive";		//inactive, active, open, succeeded
	$step3State = "inactive";		//inactive, active, open, succeeded
	$step4State = "inactive";		//inactive, active, open, succeeded

	//step 0 vars
	$wikiLogo = "";
	$wikiTitle = "";
	$wikiDescription = "";
	$wikiLogo_err = "";
	$wikiTitle_err = "";
	$wikiDescription_err = "";

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
	
	//step 2 vars

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
	
	//step 4 vars
	$installationStatus = "";
	$wikiUrl = "";
	$installation_err = "";


	//funcs
	function cleanStep0Vars(): void {
		
		global $wikiTitle, $wikiLogo, $wikiDescription;

		$wikiTitle = "";
		$wikiLogo = "";
		$wikiDescription = "";
	}
	function cleanStep1Vars(): void {
		
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
	}


	// Processing form data when form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Verify CSRF token
		if(isCsrfTokenValid() == false) {

			http_response_code(403);
			throw new RuntimeException('Invalid CSRF token.' . ' : ' . $_POST['csrf_token']);
		}
			
			
		//handle logo / title setup
		if ($_POST["form_type"] === "setlogo"){
			
			$step0State = 'open';
			
			//get inputs
			$wikiTitle = getPostParam_InputText("wikititle");
			$wikiDescription = getPostParam_InputText("wikidescr");

			//get wiki logo
			$file = $_FILES['logoInputName'];
			$fileName = $file['name'];
			$fileTmpPath = $file['tmp_name'];
			$fileSize = $file['size'];
			$fileError = $file['error'];
			
			//check fields
			$wikiTitle_err = validateWikiTitle($wikiTitle);
			$wikiDescription_err = validateWikiDescription($wikiDescription);
			
			//check for errors
			$wikiLogo_err = validateUploadErrorCode($_FILES['logoInputName']['error']);
			if($wikiLogo_err === null) $wikiLogo_err = validateWikiLogoFileSize($_FILES['logoInputName']['size']);
			if($wikiLogo_err === null) $wikiLogo_err = validateWikiLogoFileTypeAndDimensions($_FILES['logoInputName']['tmp_name']);

			// Check input errors before proceeding
			if(empty($wikiTitle_err) && empty($wikiDescription_err) && empty($wikiLogo_err))
			{
				//Most wikis use a PHP library like GD or ImageMagick to automatically 
				//resize the logo to a standard size (e.g., max 400px wide) during the upload process
				if(!writeLogo($_FILES['logoInputName']))
				{
					throw new InvalidArgumentException("could not upload file");
				}
				writeBasicConfig($wikiTitle, $wikiDescription);
				
				cleanStep0Vars();
				$step0State = "succeeded";
				$step1State = "active";
			}
		}
			
		//handle DB config creation
		else if ($_POST["form_type"] === "setdbconfig"){

			$step1State = 'open';
			
			//get inputs
			$compilerUrl = getPostParam_InputText("compilerurl");
			$wikiUrl = getPostParam_InputText("wikiurl");
			$sqlServerUrl = getPostParam_InputText("dburl");
			$sqlDbName = getPostParam_InputText("dbname");
			$sqlDbUser = getPostParam_InputText("dbuser");
			$sqlDbPass = getPostParam_InputText("dbpass");
			
			//validate inputs
			$compilerUrl_err = validateUrl($compilerUrl);
			$wikiUrl_err = validateUrl($wikiUrl);
			$sqlServerUrl_err = validateHostName($sqlServerUrl);
			$sqlDbName_err = validateSqlDatabaseName($sqlDbName);
			$sqlDbUser_err = validateSqlDatabaseUserName($sqlDbUser);
			$sqlDbPass_err = validateSqlDatabaseUserPassword($sqlDbPass);
			
			// Check input errors before proceeding
			if(empty($compilerUrl_err) && empty($wikiUrl_err) && empty($sqlServerUrl_err) 
				&& empty($sqlDbName_err) && empty($sqlDbUser_err) && empty($sqlDbPass_err))
			{
				writeDatabaseConfig($compilerUrl, $wikiUrl, $sqlServerUrl, $sqlDbName, $sqlDbUser, $sqlDbPass);
				cleanStep1Vars();
				$step1State = "succeeded";
				$step2State = "active";
			}
		}
		
		//handle settings config creation
		else if ($_POST["form_type"] === "setsettingsconfig"){

			$step3State = 'open';

			
			//fetch inputs and validate
			$isProduction = getPostParam_InputCheckbox('isprod');
			if($allowSessionOverHttp == null)
			{
				throw new InvalidArgumentException("isprod checkbox is not set");
			}
			$allowSessionOverHttp = getPostParam_InputCheckbox_Reverse('ishttpsesdisallowed');
			if($allowSessionOverHttp == null)
			{
				throw new InvalidArgumentException("ishttpsesdisallowed checkbox is not set");
			}
			$maxLoginAttemptsPerIp = getPostParam_InputNumber('maxLoginAttemptsIp');
			if(validateIntegerString($maxLoginAttemptsPerIp, 5, 50) == false)
			{
				throw new InvalidArgumentException("Invalid maxLoginAttemptsIp - must be an integer between 5 and 50.");
			}
			$maxLoginAttemptsPerEmail = getPostParam_InputNumber('maxLoginAttemptsEmail');
			if(validateIntegerString($maxLoginAttemptsPerEmail, 5, 50) == false)
			{
				throw new InvalidArgumentException("Invalid maxLoginAttemptsEmail - must be an integer between 5 and 50.");
			}
			$waitTimeLogin = getPostParam_InputNumber('waitTimeLogin');
			if(validateIntegerString($waitTimeLogin, 5, 720) == false)
			{
				throw new InvalidArgumentException("Invalid waitTimeLogin - must be an integer between 5 and 720.");
			}
			$keepOldRecordsLogin = getPostParam_InputNumber('keepOldRecordsLogin');
			if(validateIntegerString($keepOldRecordsLogin, 5, 731) == false)
			{
				throw new InvalidArgumentException("Invalid keepOldRecordsLogin - must be an integer between 5 and 731.");
			}
			$maxResetAttemptsPerIp = getPostParam_InputNumber('maxResetAttemptsIp');
			if(validateIntegerString($maxResetAttemptsPerIp, 5, 50) == false)
			{
				throw new InvalidArgumentException("Invalid maxResetAttemptsIp - must be an integer between 5 and 50.");
			}
			$maxResetAttemptsPerEmail = getPostParam_InputNumber('maxResetAttemptsEmail');
			if(validateIntegerString($maxResetAttemptsPerEmail, 5, 50) == false)
			{
				throw new InvalidArgumentException("Invalid maxResetAttemptsEmail - must be an integer between 5 and 50.");
			}
			$waitTimeReset = getPostParam_InputNumber('waitTimeReset');
			if(validateIntegerString($waitTimeReset, 16, 1440) == false)
			{
				throw new InvalidArgumentException("Invalid waitTimeReset - must be an integer between 16 and 1440.");
			}
			$keepOldRecordsReset = getPostParam_InputNumber('keepOldRecordsReset');
			if(validateIntegerString($keepOldRecordsReset, 1, 731) == false)
			{
				throw new InvalidArgumentException("Invalid keepOldRecordsReset - must be an integer between 1 and 731.");
			}
			$keepOldRecordsReset = getPostParam_InputNumber('keepOldRecordsReset');
			if(validateIntegerString($keepOldRecordsReset, 1, 731) == false)
			{
				throw new InvalidArgumentException("Invalid keepOldRecordsReset - must be an integer between 1 and 731.");
			}
			$maxRegisterSuccessesPerIp = getPostParam_InputNumber('maxRegSuccessIp');
			if(validateIntegerString($maxRegisterSuccessesPerIp, 1, 10) == false)
			{
				throw new InvalidArgumentException("Invalid maxRegSuccessIp - must be an integer between 1 and 10.");
			}
			$waitTimeRegister = getPostParam_InputNumber('waitTimeRegSuccess');
			if(validateIntegerString($waitTimeRegister, 1, 10) == false)
			{
				throw new InvalidArgumentException("Invalid waitTimeRegSuccess - must be an integer between 1 and 10.");
			}
			$keepOldRecordsRegister = getPostParam_InputNumber('keepOldRecordsRegister');
			if(validateIntegerString($keepOldRecordsRegister, 1, 731) == false)
			{
				throw new InvalidArgumentException("Invalid keepOldRecordsRegister - must be an integer between 1 and 731.");
			}
			
			//if we havent thrown yet - everything is good
			writeSettingsConfig(
				$allowSessionOverHttp, $isProduction, $maxLoginAttemptsPerIp, $maxLoginAttemptsPerEmail, 
				$waitTimeLogin, $keepOldRecordsLogin, $maxResetAttemptsPerIp, $maxResetAttemptsPerEmail, 
				$waitTimeReset, $keepOldRecordsReset, $maxRegisterSuccessesPerIp, $waitTimeRegister, 
				$keepOldRecordsRegister);

				$step1State = "succeeded";
				$step2State = "succeeded";
				$step3State = "succeeded";
				$step4State = "active";
		}
		
		//handle view your new wiki form
		else if ($_POST["form_type"] === "visitwiki") {
			
			$step4State = 'open';
			$step0State = "succeeded";
			$step1State = "succeeded";
			$step2State = "succeeded";
			$step3State = "succeeded";
			$step4State = "succeeded";
			$installerMessage = "Thank you for choosing Listiary.<br />Your wiki is now installed and accessible at <a target='_blank' href='https://development.listiary.org'>https://development.listiary.org</a>.<br />You can visit your home page and close that page.<br /><br />";
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
									<a class="breadcrumbmain" href="javascript:void(0);" style="display: inline;">Listiary Wiki Installer</a>
								</div>
								<hr>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="10" id="LargeContainer">
						<h1 style="margin-top: 0px;">Listiary Installer</h1>
						<p style="max-width: 700px;"><?php echo $installerMessage; ?></p><br>
						
						<!-- steps UI -->
						<?php 
							//state 0
							if($step0State == "inactive") 
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>0. Set logo/title</button><br>';
							}
							else if($step0State == "succeeded")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>✔ Set logo/title</button><br>';
							}
							else
							{
								echo '<br>';
								echo '<button onclick="showListiaryModal(\'logoModal\');" class="btn btn--orange">0. Set logo/title</button>';
								echo '<br><br>'; 
							}
							//state 1
							if($step1State == "inactive") 
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>1. Set main config</button><br>';
							}
							else if($step1State == "succeeded")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>✔ Set main config</button><br>';
							}
							else
							{
								echo '<br>';
								echo '<button onclick="showListiaryModal(\'mainConfigModal\');" class="btn btn--orange">1. Set main config</button>';
								echo '<br><br>'; 
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
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>4. Visit your new wiki homepage</button><br>';
							}
							else if($step4State == "succeeded")
							{
								echo '<button onclick="javascript:void(0);" class="btn btn--orange" disabled>✔ Visit your new wiki homepage</button><br>';
							}
							else
							{
								echo '<br>';
								echo '<button onclick="javascript:showListiaryModal(\'visitNewWikiModal\');" class="btn btn--orange">4. Visit your new wiki homepage</button>';
								echo '<br><br>'; 
							}
						?>
					</td>
				</tr>
			</tbody>
		</table>

		<!-- Set logo dialog modal box -->
		<div <?php if($step0State != 'open') echo "style='display:none;'" ?> id="logoModal" class="listiaryYesNoModalWrapper">
			<form class="listiaryYesNoModalCorpus" method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			<input type="hidden" name="form_type" value="setlogo">
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
				
				<!-- Header Bar -->
				<div class="listiaryYesNoModalHeaderBar">
					<h2>Set Logo / Title</h2>
					<span onclick="hideListiaryModal('logoModal');">&times;</span>
				</div>
				
				<!-- The Content Area -->
				<div id="modalInnerContent" class="listiaryYesNoModalInnerContent">
					<div class="listiaryYesNoModalBody">
						<label for="wikiLogoPathInput">Wiki Logo (2MB max) <span class="invalid-feedback"><?php echo $wikiLogo_err; ?></span></label>
						<div style="display: flex; gap: 5px;">
							<input 
								type="text" name="wikilogo" 
								id="wikiLogoPathInput" 
								class="<?php echo (!empty($wikiLogo_err)) ? 'is-invalid' : ''; ?>" 
								value="<?php echo $wikiLogo; ?>" 
								placeholder="upload a photo ..." 
								style="flex-grow: 1;" />
							
							<button type="button" class="buttonDots" onclick="triggerFileBrowser();">•••</button>
							<input name="logoInputName" type="file" id="hiddenFileInput" accept=".svg, .png, .webp, .jpg, .jpeg, .gif" style="display:none;" onchange="updateTextInput('wikiLogoPathInput')">
						</div>
						<br />
						<label for="wikiTitleInput">Wiki Title <span class="invalid-feedback"><?php echo $wikiTitle_err; ?></span></label>
						<input 
							type="text" name="wikititle"
							id="wikiTitleInput" 
							class="<?php echo (!empty($wikiTitle_err)) ? 'is-invalid' : ''; ?>" 
							value="<?php echo $wikiTitle; ?>"
							placeholder="AWS microservice url here" />
						<br />
						<label for="wikiDescriptionInput">Wiki Description <span class="invalid-feedback"><?php echo $wikiDescription_err; ?></span></label>
						<textarea
							name="wikidescr"
							id="wikiDescriptionInput"
							class="<?php echo (!empty($wikiDescription_err)) ? 'is-invalid' : ''; ?>"
							placeholder="Enter a wiki description here"
							rows="15"><?php echo $wikiDescription; ?></textarea>
					</div>
				</div>

				<!-- Action Buttons -->
				<div class="listiaryYesNoModalButtonsWrapper">
					<button class="buttonNo" type="button" onclick="hideListiaryModal('logoModal');">Cancel</button>
					<button class="buttonYes" type="submit" name="form_dbconfig">&nbsp;&nbsp;Save&nbsp;&nbsp;</button>
				</div>
			</form>
		</div>
		
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

		<!-- Visit your new wiki dialog modal box -->
		<div <?php if($step4State != 'open') echo "style='display:none;'" ?> id="visitNewWikiModal" class="listiaryYesNoModalWrapper">
			<form style="margin: 155px auto; height: 52vh;" class="listiaryYesNoModalCorpus" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			<input type="hidden" name="form_type" value="visitwiki">
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
				
				<!-- Header Bar -->
				<div class="listiaryYesNoModalHeaderBar">
					<h2>Visit your wiki</h2>
					<span onclick="hideListiaryModal('visitNewWikiModal');">&times;</span>
				</div>
				
				<!-- The Content Area -->
				<div id="modalInnerContent" class="listiaryYesNoModalInnerContent">
					
					<div class="listiaryYesNoModalBody">
						<br />
						<div>
							Thank you for using Listiary.<br />
							Your wiki instalation has been complete.<br />
							For more information, see the documentation <a target="_blank" href="https://documentation.listiary.org/">here</a>.
							<br />
							Please visit your home page URL below to confirm everything is working fine, and click Finish.
						</div>
						<br /><br />
						<div style="text-align: center;">
							<a target="_blank" style="display:block; overflow-wrap: anywhere;" href="https://development.listiary.org">
								https://development.listiary.org
							</a>
						</div>
					</div>
				</div>

				<!-- Action Buttons -->
				<div class="listiaryYesNoModalButtonsWrapper">
					<button class="buttonNo" type="button" onclick="hideListiaryModal('visitNewWikiModal');">Cancel</button>
					<button class="buttonYes" type="submit" name="visitwiki">Finish</button>
				</div>
			</form>
		</div>

	</body>
</html>