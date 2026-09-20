<?php
//get values
function getPostParam_InputText($parameterName): ?string {
	
	if(isset($_POST[$parameterName]) == false)
	{
		return null;
	}
	$param = trim($_POST[$parameterName]);
	return $param;
}
function getPostParam_InputNumber($parameterName): ?string {
	
	if(isset($_POST[$parameterName]) == false)
	{
		return null;
	}
	$param = $_POST[$parameterName];
	return $param;
}
function getPostParam_InputCheckbox($parameterName): ?string {

	if(isset($_POST[$parameterName]) == false)
	{
		return null;
	}
	else if($_POST[$parameterName] === '1')
	{
		return 'true';
	}
	return 'false';
}
function getPostParam_InputCheckbox_Reverse($parameterName): ?string {

	if(isset($_POST[$parameterName]) == false_)
	{
		return null;
	}
	else if($_POST[$parameterName] === '1')
	{
		return 'false';
	}
	return 'true';
}

//validate DB config
function validateUrl($url): ?string {

	$error = null;
	
	if(empty($url))
	{
		$error = " (empty!)";
	}
	elseif (!filter_var($compiler, FILTER_VALIDATE_URL))
	{
		$error = " (invalid!)";
	}
	
	return $error;
}
function validateHostName($host): ?string {

	$error = null;
	
	if(empty($host))
	{
		$error = " (empty!)";
	}
	elseif (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))
	{
		$error = " (invalid!)";
	}
	
	return $error;
}
function validateSqlDatabaseName($dbname): ?string {

	$error = null;
	
	if(empty($dbname))
	{
		$error = " (empty!)";
	}
	elseif (!preg_match('/^[A-Za-z0-9_-]{1,128}$/', $dbname))
	{
		$error = " (invalid!)";
	}
	
	return $error;
}
function validateSqlDatabaseUserName($username): ?string {

	$error = null;
	
	if(empty($username))
	{
		$error = " (empty!)";
	}
	elseif (!preg_match('/^[A-Za-z0-9._@\\\\-]{1,128}$/', $username))
	{
		$error = " (invalid!)";
	}
	
	return $error;
}
function validateSqlDatabaseUserPassword($password): ?string {

	$error = null;
	
	if(empty($password))
	{
		$error = " (empty!)";
	}
	elseif (strlen($password) < 8)
	{
		$error = " (must 8 chars or more!)";
	}
	
	return $error;
}
function validateIntegerString($value, int $min, int $max): bool {

    if (!is_string($value)) 
	{
        return false;
    }

    // must be only digits (no spaces, signs, decimals)
    if (!ctype_digit($value))
	{
        return false;
    }

    $intValue = (int)$value;
    return $intValue >= $min && $intValue <= $max;
}

//validate logo modal
function validateWikiTitle($title): ?string {

	$error = null;
	
	if(empty($title))
	{
		$error = " (empty!)";
	}
	elseif (strlen($title) < 3)
	{
		$error = " (must 3 chars or more!)";
	}
	elseif (strlen($title) > 100)
	{
		$error = " (must 100 chars or less!)";
	}
	elseif (!preg_match('/^[a-zA-Z0-9 _-]+$/', $title))
	{
		$error = " (contains invalid characters!)";
	}
	
	return $error;
}
function validateWikiDescription($description): ?string {

	$error = null;
	
	if(empty($description))
	{
		$error = " (empty!)";
	}
	elseif (mb_strlen($description) < 3)
	{
		$error = " (must 3 chars or more!)";
	}
	elseif (mb_strlen($description) > 1000)
	{
		$error = " (must 1000 chars or less!)";
	}
	elseif (!preg_match('/^[a-zA-Z0-9 _.,!?\'"-]+$/', $description))
	{
		$error = " (Description contains invalid characters!)";
	}
	
	return $error;
}
function validateUploadErrorCode($fileCode): ?string {

	$error = null;
	
	if ($fileCode !== UPLOAD_ERR_OK)
	{
		$error = " (file invalid!)";
		//die("Upload failed with error code " . $_FILES['logoInputName']['error']);
	}
	elseif($fileCode === null)
	{
		$error = " (something went wrong!)";
	}
	
	return $error;
}
function validateWikiLogoFileSize($fileSize): ?string {

	$error = null;
	$maxSize = 2 * 1024 * 1024; //max 2MB
	
	if($fileSize == null)
	{
		$error = " (something went wrong!)";
	}
	elseif($fileSize > $maxSize)
	{
		$error = " (file too large!)";
	}
	
	return $error;
}
function validateWikiLogoFileTypeAndDimensions($tmp): ?string {

	$error = null;
	$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
	
	if($tmp == null)
	{
		$error = " (something went wrong!)";
	}

	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mimeType = $finfo->file($tmp);
	if (!in_array($mimeType, $allowedMimes))
	{
		$error = " (invalid file type!)";
		//die("Invalid file type. Only JPG, PNG, and WEBP are allowed.");
	}
	
	$imageDetails = getimagesize($tmp);
	if ($imageDetails === false && $mimeType !== 'image/svg+xml') 
	{
		$error = " (invalid image demensions!)";
		//die("The file is not a valid image.");
	}
	
	return $error;
}


function writeLogo($file): bool {
	
	//writeDatabaseConfig($compilerUrl, $wikiUrl, $sqlServerUrl, $sqlDbName, $sqlDbUser, $sqlDbPass);
	$uploadDir = '_configs/';
	$tempFile = $file['tmp_name'];

	// 1. Get the extension safely
	$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
	$extension = strtolower($extension);

	// 2. Generate a NEW unique name (prevents overwriting and directory traversal)
	$newFileName = 'logo' . '.' . $extension;
	$targetPath = $uploadDir . $newFileName;
	
	if (move_uploaded_file($tempFile, $targetPath)) 
	{
		return true;
	}
	
	return false;
}

function writeBasicConfig($wikiTitle, $wikiDescription): void {
	
	//read config template
	$path = __DIR__ . '/../_installer_templates/_basic_config_template.php';
	if (!is_readable($path)) throw new RuntimeException("Config template not readable.");
	$template = file_get_contents($path);
	
	//fill up values
	$updated = str_replace('*WIKI_TITLE_VALUE*', $wikiTitle, $template);
	$updated = str_replace('*WIKI_DESCRIPTION_VALUE*', $wikiDescription, $updated);
	
	//save config
	$savepath = __DIR__ . '/../_configs/_basic_config.php';
	if (file_put_contents($savepath, $updated) === false)
	{
		throw new RuntimeException("Could not save config.");
	}
}

function writeSettingsConfig(
		$allowSessionOverHttp, $isProduction, $maxLoginAttemptsPerIp, $maxLoginAttemptsPerEmail, 
		$waitTimeLogin, $keepOldRecordsLogin, $maxResetAttemptsPerIp, $maxResetAttemptsPerEmail, 
		$waitTimeReset, $keepOldRecordsReset, $maxRegisterSuccessesPerIp, $waitTimeRegister, 
		$keepOldRecordsRegister): void {


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
	
function writeDatabaseConfig(
		$compilerUrl, $wikiUrl, $sqlServerUrl, $sqlDbName, $sqlDbUser, $sqlDbPass): void {
	
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

	