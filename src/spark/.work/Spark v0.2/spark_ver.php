<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/utils/commonlib.php";
	set_exception_handler('catchEx');

	
	//print ASCII art banner
	printBanner();
	
	echo "Running Spark framework v 0.1" . NEW_LINE;
	echo "Spark is an administrative CLI interface for administering the Listiary database layer." . NEW_LINE;
	echo "Spark is very powerful and unforgiving - be careful which script you are invoking and read the documentation." . NEW_LINE;
	echo NEW_LINE;


	// Exit with a proper status code
	exit(0);