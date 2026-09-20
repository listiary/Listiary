<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/_commonlib.php";
	require_once __DIR__ . "/ListFiles.php";
	set_exception_handler('catchEx');
	
	
	$output = ListFiles("/_config.php", -1);
	echo $output;