<?php

		//set for debugging
		//$_GET['domain'] = "personal";
		//$_GET['username'] = "DemonOfReason";

	//get parameters	
	if(isset($_GET['domain']) == false) die("'domain' parameter of the url is not set");
	$domain = $_GET['domain'];
		//echo "Got domain parameter - '" . $domain . "'<br />";

	if($_GET['domain'] == "personal" && isset($_GET['username']) == false)
	die("'domain' is 'personal' and 'username' parameter of the url is not set");
		//echo "Got username parameter - '" . $_GET['username'] . "'<br />";

	//include config
	require_once "_config.php";
	$servername = "";
	$username = "";
	$password = "";
	$dbname = "";

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
		//echo "DB name is '" . $dbname . "'<br />";

	// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	if(!$conn) die("Connection failed: " . mysqli_connect_error());
		//echo "Created connection to '" . $servername . "'<br />";

	
	//get all the articles
	$code = "";
	$sql = "SELECT name, content, timestamp FROM files";
		//echo "Executing query - '" . $sql . "'<br>";
	$result = mysqli_query($conn, $sql);
	if($result == false || mysqli_num_rows($result) < 1) die("Query result is false");
	while ($row = mysqli_fetch_assoc($result))
	{
		$code .= $row['name'] . "~~N~~" . $row['content'] . "~~~FILE~~~";
	}

	// test POST
	$base64 = base64_encode($code);
	$base64Name = base64_encode("MULTISTRING");
	$result = doPostRequest('https://2zce0k22xi.execute-api.eu-north-1.amazonaws.com/', $base64, $base64Name);
		//var_dump($result);
	//logPostResponse($result);
		//die("<br>End Testing POST.");
	
	//update
	$jArr = json_decode($result, true);
	$responseJSON = $jArr["Json"];
	if($responseJSON == null) die("Something went wrong. JSON is null.");

	if($domain == "public")
	{
		$escapedJSON = mysqli_real_escape_string($conn, $responseJSON);
		$timestamp = date("Y/m/d H:i:s");
		$sqlcommand = "UPDATE artifacts SET content='" . $escapedJSON . "', " . "timestamp='" . $timestamp . "' WHERE name='root';";
		$updateresult = mysqli_query($conn, $sqlcommand);
		if($updateresult == false) die("Update DB failed. Query result is false");
		else echo "success";
	}
	else if($domain == "personal")
	{
		$escapedJSON = mysqli_real_escape_string($conn, $responseJSON);
		$timestamp = date("Y/m/d H:i:s");
		$sqlcommand = "UPDATE artifacts SET content='" . $escapedJSON . "', " . "timestamp='" . $timestamp . "' WHERE name='" . $_GET['username'] . "';";
		$updateresult = mysqli_query($conn, $sqlcommand);
		if($updateresult == false) die("Update DB failed. Query result is false");
		else echo "success";

		//TODO: accomodate for non-existing username
		//right now we get success and change nothing in the DB
	}
	else if($domain == "private")
	{
		die("Not implemented yet for domain - 'private'");
	}
	else if($domain == "normative")
	{
		$escapedJSON = mysqli_real_escape_string($conn, $responseJSON);
		$timestamp = date("Y/m/d H:i:s"); //https://www.php.net/manual/en/datetime.format.php
		$sqlcommand = "UPDATE artifacts SET content='" . $escapedJSON . "', " . "timestamp='" . $timestamp . "' WHERE name='root';";
		$updateresult = mysqli_query($conn, $sqlcommand);
		if($updateresult == false) die("Update DB failed. Query result is false");
		else echo "success";
	}
	else
	{
		die("Connection failed: Unknown value for url parameter domain - '" . $domain . "'");
	}


	function logArticle($article) {
		
		echo "Code :<br /><span style='color:red'>" . htmlspecialchars($article) . "</span>";
		echo "<br /><br /><br /><br />";
	}
	function logCode($code) {

		echo "<span style='color:blue'>" . $code . "</span>";
		echo "<br /><br /><br /><br />";		
	}
	function doPostRequest($url, $code, $filename) {

		//https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
		$data = [
			'command' => 'multi-parse', 
			'verbosity' => 'low',
			'translator' => 'JSON',
			'filename' => $filename,
			'code' => $code
		];

		// use key 'http' even if you send the request to https://...
		$options = [
			'http' => [
				'header' => "Content-type: application/x-www-form-urlencoded\r\n",
				'method' => 'POST',
				'content' => http_build_query($data),
			],
		];

		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return $result;
	}
	function logPostResponse($response) {

		//https://2zce0k22xi.execute-api.eu-north-1.amazonaws.com/?command=parse&verbosity=low&translator=JSON&code=%22directives%20-%3E%3E%20delimiter-mode%20%3C%3Cbi%3E%3E;;%20fabrics%20-%3E%3E%20wool%20fabrics,,%20cotton%20fabrics,,%20silk%20fabrics,,%20synthetic%20fabrics;;%22

		if ($response == null) 
		{
			echo "ERROR : <span style='color:blue'>response is NULL</span><br />";
			return;
		}

		//echo $response;
		//var_dump($jArr);
		$jArr = json_decode($response, true);

		//json.Result
		if ($jArr["Result"] == null) echo "Result : <span style='color:blue'>NULL</span><br />";
		else echo "Result : <span style='color:blue'>" . $jArr["Result"] . "</span><br />";
		//echo "<br />";

		//json.Command
		if ($jArr["Command"] == null) echo "Command : <span style='color:blue'>NULL</span><br />";
		else echo "Command : <span style='color:blue'>" . $jArr["Command"] . "</span><br />";
		echo "<br /><br />";

		//json.Logs
		if ($jArr["Logs"] == null) echo "Logs : <span style='color:blue'>NULL</span><br />";
		else echo "Logs :<br /><span style='color:green'>" . $jArr["Logs"] . "</span><br /><br />";
		echo "<br />";
		
		//json.Json
		if ($jArr["Json"] == null) echo "Json : <span style='color:blue'>NULL</span><br />";
		else echo "Json :<br /><span style='color:blue'>" . $jArr["Json"] . "</span><br />";
		echo "<br />";
	}
?>