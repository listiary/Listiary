<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	// Set credentials
	require_once "_config.php";
    $servername = DB_SERVER_PUBLIC;
    $username = DB_USERNAME_PUBLIC;
    $password = DB_PASSWORD_PUBLIC;
    $dbname = DB_NAME_PUBLIC;

	// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	
	// Check connection
	if (!$conn) 
	{
		die("Connection failed: " . mysqli_connect_error());
	}
//echo "Connection established.\n";

	// Get raw POST data
	$json = file_get_contents('php://input');
//echo "Raw POST data: $json.\n";

	// Decode JSON to PHP array/object
	$data = json_decode($json, true);

	// Get 'filename' value
	if(!isset($data['article']) && !isset($data['filename']))
	{
		echo "Error : Not valid 'article' or 'filename' argument.";
		exit;
	}

	//Article-filename
	$filename = "";
	if(isset($data['filename']))
	{
		$filename = $data['filename'];
//echo "Filename parameter red - '$filename'.\n";
	}
	else
	{
		$article = $data['article'];
//echo "Article parameter red - '$article'.\n";
		$article_safe = mysqli_real_escape_string($conn, $article);
		$sql = "SELECT `filename` FROM `housekeeping_itemid_filename` WHERE `item_id` = '$article_safe';";
		$result = mysqli_query($conn, $sql);
		if (!$result)
		{
			http_response_code(500);
			echo "Database query failed: " . mysqli_error($conn);
			exit;
		}
		while ($row = mysqli_fetch_assoc($result))
		{
			$fn = $row["filename"];
			if(strlen($filename) < 1) $filename = $fn;
			else if(strpos($filename, ".") === 0 && strpos($fn, ".") !== 0) $filename = $fn;
			else if(strlen($fn) > strlen($filename)) $filename = $fn;  //fix that
//echo "Got filename - '$filename'.\n";
		}
	}
//echo "Got here.\n$filename\n";


	// Now sanitize it for your query
	$filename_safe = mysqli_real_escape_string($conn, $filename);
//echo "Got here.\n$filename_safe\n";

	// Run query
	$sql = "SELECT `related_filename` FROM `housekeeping_filename_related` WHERE `filename` = '$filename_safe';";
//echo "SQL for related files.\n$sql\n";
	$result = mysqli_query($conn, $sql);
	if (!$result)
	{
		http_response_code(500);
		echo "Database query failed: " . mysqli_error($conn);
		exit;
	}
//echo "Got related files.\n";

	// Get related filenames from the query
	$files[$filename_safe] = true;
	while ($row = mysqli_fetch_assoc($result))
	{
		$fn = $row["related_filename"];
		$fn = mysqli_real_escape_string($conn, $fn);
		$files[$fn] = true;
//echo "Related filename red - '$fn'.\n";
	}
	$files = array_keys($files);

	// Create comma-separated quoted list of file names
	$escaped_files = array_map(function($f) use ($conn) {
		return "'" . $f . "'";
	}, $files);
	$file_list = implode(",", $escaped_files);

	// Build and run query
	$sql = "SELECT `filename`, `content` FROM `compiled_documents` WHERE `filename` IN ($file_list);";
	$result = mysqli_query($conn, $sql);

	// Check for error
	if (!$result) {
		http_response_code(500);
		echo "Query failed: " . mysqli_error($conn);
		exit;
	}

	// Fetch results
	$json = "";
	while ($row = mysqli_fetch_assoc($result))
	{
		$content = $row['content'];
		if(strlen($json) > 0) $json .= ",";
		$json .= $content;
	}
	echo "{\"name\":\"files\",\"items\":[" . $json . "]}";

	// Close connection
	mysqli_close($conn);
