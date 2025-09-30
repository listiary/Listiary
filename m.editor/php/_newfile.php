<?php

	$conn = null;
	try
	{
		//get URL parameters
		$article = ""; 
		$domain = "";
		getUrlParameters();

		//include config
		require_once "_config.php";

		//read config
		$compilerurl = "";
		$servername = ""; 
		$username = ""; 
		$password = ""; 
		$dbname = "";
		setDbConstants($domain);

		//create connection
		$conn = createConnection();

		//make sure we are not overwriting
		verifyFetchOldArticleFails($conn, $article);

		//get new article from POST param
		$newArticle = getNewArticle();

		//compile data
		$result = doPostRequest($compilerurl, $newArticle, $article);
		
		//decode response JSON
		$jArr = json_decode($result, true);

		//validate JSON
		validateCompiledJson($jArr, $result);

		//get the compiled JSON
		$compiledJson = decodeOutput($jArr);

		//update the Describe document
		insertDescribeDocument($conn, $article, $newArticle);

		//update Json
		updateCompiledJson($conn, $article, $compiledJson);

		//delete ids in DB
		deleteOldIds($conn, $article);
		
		//delete file references in DB
		deleteOldFileReferences($conn, $article);
		
		//insert ids
		doIds($conn, $article, $compiledJson);
		
		//insert file references
		doFilenameReferences($conn, $article, $compiledJson);

		//close connection
		mysqli_close($conn);
		echo "Done. Connection closed.";
	}
	catch (Exception $e)
	{
		echo "Script failed: " . $e->getMessage();
		mysqli_close($conn);
		return;
	}




	// Read URL parameters into our global variables
	function getUrlParameters() {

		global $article, $domain;

		//get parameters
		if(isset($_GET['article']) == false) die("'article' parameter of the url is not set");
		$article = $_GET['article'];
			//echo "Got article parameter - '" . $article . "'<br />";
		
		if(isset($_GET['domain']) == false) die("'domain' parameter of the url is not set");
		$domain = $_GET['domain'];
			//echo "Got domain parameter - '" . $domain . "'<br />";
	}
	// Read Database connection constants from the config into our global variables.
	function setDbConstants($domain) {

		global $servername, $username, $password, $dbname, $compilerurl;

		$compilerurl = COMPILER_URL;
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
		echo "Created connection to '" . $servername . "'<br />";

		return $connection;
	}
	// Try to fetch old article - should fail, so we know there is no article with that name
	function verifyFetchOldArticleFails($conn, $article) {

		//get old article
		$sql = "SELECT `content` FROM `describe_documents` WHERE filename='" . $article . "'";
		echo "Executing query - '" . $sql . "'<br />";
		$result = mysqli_query($conn, $sql);
		if($result == false) die("Query result is false");
		if(mysqli_num_rows($result) > 0) die("Article with this name already exists");
	}
	// Get new article text from the HTTP POST data. Exit if it is the same as the old one.
	function getNewArticle() {

		//get new variant
		$newArticle = $_POST['content'];
			//echo "Got the new article <br />";
			//echo $newArticle;
		if(strlen(trim($newArticle)) == 0) exit("New article is empty.");
		$newArticle = rtrim($newArticle); //hack to be removed when we figure out why we get extra spaces at the end
		echo "Got the new article <br />";

		return $newArticle;
	}
	// Do Compilation request
	function doPostRequest($url, $code, $filename) {

		// Base64 encode the source code
		$code_base64 = base64_encode($code);

		//https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
		$requestPayload = [
			'command' => 'parse-file',
			'verbosity' => 'low',
			'translator' => 'JSON',
			'filename' => $filename,
			'code' => $code_base64
		];

		// Convert JSON to string
		$jsonString = json_encode($requestPayload);

		// Base64-encode the JSON string (this is what Lambda expects in request.Body)
		$encodedRequest = base64_encode($jsonString);

		// use key 'http' even if you send the request to https://...
		$options = [
			'http' => [
				'header' => "Content-type: text/plain\r\n",
				'method' => 'POST',
				'content' => $encodedRequest,
			],
		];

		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		//logPostResponse($result);

		if ($result == null)
		{
			echo "ERROR : response is NULL\n";
			return;
		}

		return $result;
	}
	// Check if decoding of JSON failed
	function validateCompiledJson($jArr, $result) {

		// Check if decoding failed
		if (!is_array($jArr)) {
			echo "ERROR: Failed to parse JSON\n";
			echo "\n\nRaw Response:\n$result\n\n";
			exit;
		}

		// Check if "Output" is missing
		if (!isset($jArr["Output"])) {
			echo "ERROR: 'Output' is missing from response\n";
			echo "\n\nRaw Response:\n$result\n\n";
			exit;
		}

		// Check if "Result" is missing
		if (!isset($jArr["Result"])) {
			echo "ERROR: 'Result' is missing from response\n";
			echo "\n\nRaw Response:\n$result\n\n";
			exit;
		}

		// Compare result, safely using double quotes around array key
		if (strtolower($jArr["Result"]) !== "success") {
			echo "ERROR: Result is " . $jArr["Result"] . "\n";
			echo "\n\nRaw Response:\n$result\n\n";
			exit;
		}
	}
	// Decode the compiled output
	function decodeOutput($jArr) {

		$compiled64 = $jArr["Output"];
		$compiledJson = base64_decode($compiled64);
		//echo $compiledJson;
		return $compiledJson;
	}
	// Insert Describe document in the DB
	function insertDescribeDocument($conn, $articleName, $articleText) {

		//insert describe Document
		$entry_safe = mysqli_real_escape_string($conn, $articleName);
		$content_safe = mysqli_real_escape_string($conn, $articleText);
		$sql = "INSERT INTO `describe_documents` (filename, content, submitted_at)
			VALUES ('$entry_safe', '$content_safe', NOW())
			ON DUPLICATE KEY UPDATE
			content = VALUES(content),
			submitted_at = NOW();";

		// Upsert document
		$result = mysqli_query($conn, $sql);
		if ($result == false)
		{
			Die("Database error: " . mysqli_error($conn));
		}
		else
		{
			echo "SUCCEEDED! Inserted new article - '" . $articleName . "'<br />";
		}
	}
	// Update compiled JSON in the DB
	function updateCompiledJson($conn, $articleName, $compiledJson) {

		//update entry
		$content_safe = mysqli_real_escape_string($conn, $compiledJson);
		$entry_safe = mysqli_real_escape_string($conn, $articleName);
		$sql = "INSERT INTO `compiled_documents` (filename, content, submitted_at)
			VALUES ('$entry_safe', '$content_safe', NOW())
			ON DUPLICATE KEY UPDATE
			content = VALUES(content),
			submitted_at = NOW();";

		// Upsert document
		if (!mysqli_query($conn, $sql))
		{
			echo "Database error: " . mysqli_error($conn);
		}
		else
		{
			echo "Compiled document saved.<br />";
		}
	}
	// Insert ids for the new article in the DB
	function doIds($conn, $filename, $content) {

		echo "<br />Doin Ids.";
		$jArr = json_decode($content, true);
		if ($jArr === null)
		{
			echo "Failed to parse JSON: " . json_last_error_msg();
			return;
		}

        $ids = [];
        extractIds($jArr, $ids);
        $ids = array_keys($ids);

        $length = count($ids);
        echo "\"" . $filename . "\" has $length public ids.\n";
        //print_r($ids); var_dump($ids); break;

        //upload the data
        $filename_safe = mysqli_real_escape_string($conn, $filename);
        $values = [];

        foreach ($ids as $id) {
            $item_id_safe = mysqli_real_escape_string($conn, $id);
            $values[] = "('$filename_safe', '$item_id_safe')";
        }

        if (!empty($values)) {

            $query = "INSERT IGNORE INTO housekeeping_itemid_filename (filename, item_id) VALUES " . implode(", ", $values);
            if (!mysqli_query($conn, $query))
            {
                echo "Error: " . mysqli_error($conn);
            }
            else
            {
                echo "Inserted " . mysqli_affected_rows($conn) . " rows.\n";
            }
        }
	}
	function extractIds(array $node, array &$ids) {

		if (isset($node['id']) && is_string($node['id'])) {
			if (strpos($node['id'], '@') !== 0) {
				$ids[$node['id']] = true; // Use value as key to ensure uniqueness
			}
		}

		if (isset($node['items']) && is_array($node['items'])) {
			foreach ($node['items'] as $child) {
				if (is_array($child)) {
					extractIds($child, $ids);
				}
			}
		}
	}
	// Insert filename references for the new article in the DB
	function doFilenameReferences($conn, $filename, $content) {

		$filename_escaped = mysqli_real_escape_string($conn, $filename);

		// Get the ids in this file
        $ids = [];
        $sql = "SELECT `item_id` FROM `housekeeping_itemid_filename` WHERE `filename` = '$filename_escaped';";
        $result2 = mysqli_query($conn, $sql);
        while ($row2 = mysqli_fetch_assoc($result2))
        {
            $itemId = $row2['item_id'];
            $ids[] = $itemId;
        }

        // Check there are Ids
        if (empty($ids))
        {
            echo "No item IDs for \"$filename\".\n";
            return;
        }

        // Get the files for those ids
        $relatedFilenames = [];
        $ids_quoted = array_map(fn($id) => "'$id'", $ids);
        $sql = "SELECT DISTINCT `filename` FROM `housekeeping_itemid_filename` WHERE `item_id` IN (" . implode(',', $ids_quoted) . ");";
        $result3 = mysqli_query($conn, $sql);
        if ($result3)
        {
            while ($row3 = mysqli_fetch_assoc($result3))
            {
                $relFile = $row3['filename'];
                $relatedFilenames[$relFile] = true;
            }
        }
        $relatedFilenames = array_keys($relatedFilenames);

        // Upload the data
        $values = [];
        foreach ($relatedFilenames as $fn)
        {
            $fn_escaped = mysqli_real_escape_string($conn, $fn);
            $values[] = "('$filename_escaped', '$fn_escaped')";
        }
        if (!empty($values)) {

            $query = "INSERT IGNORE INTO housekeeping_filename_related (filename, related_filename) VALUES " . implode(", ", $values);
            if (!mysqli_query($conn, $query))
            {
                echo "Error: " . mysqli_error($conn);
            }
            else
            {
                echo "Inserted " . mysqli_affected_rows($conn) . " rows.\n";
            }
        }
	}
	// Delete old ids in the DB
	function deleteOldIds($conn,$filename) {

		$filename_safe = mysqli_real_escape_string($conn, $filename);
		$query = "DELETE FROM `housekeeping_itemid_filename` WHERE `filename` = '$filename_safe'";

		if (mysqli_query($conn, $query))
		{
			echo "Deleted " . mysqli_affected_rows($conn) . " id rows from the DB.";
			echo "SQL: " . $query ." :SQL";
		}
		else
		{
			echo "Error: " . mysqli_error($conn);
		}
	}
	// Delete old filename references in the DB
	function deleteOldFileReferences($conn, $filename) {

		$filename_safe = mysqli_real_escape_string($conn, $filename);
		$query = "DELETE FROM `housekeeping_filename_related` WHERE `filename` = '$filename_safe'";

		if (mysqli_query($conn, $query))
		{
			echo "Deleted " . mysqli_affected_rows($conn) . " file relation rows from the DB.";
		}
		else
		{
			echo "Error: " . mysqli_error($conn);
		}
	}

	//logging
	function logPostResponse($response) {

		if ($response == null)
		{
			echo "ERROR : response is NULL\n";
			return;
		}
		echo "\n\nRaw Response:\n$response\n\n";

		$jArr = json_decode($response, true);
		//var_dump($jArr);

		//json.Result
		if (!isset($jArr["Result"])) echo "Result : NULL\n";
		else echo "Result : " . $jArr["Result"] . "\n";
		//echo "\n";

		//json.Command
		if (!isset($jArr["Command"])) echo "Command : NULL\n";
		else echo "Command : " . $jArr["Command"] . "\n";
		echo "\n\n";

		//json.Logs
		if (!isset($jArr["Logs"])) echo "Logs : NULL\n";
		else echo "Logs :\n" . $jArr["Logs"] . "\n\n";
		echo "\n";

		//json.Output
		if (!isset($jArr["Output"])) echo "Output : NULL\n";
		else echo "Output :\n" . $jArr["Output"] . "\n";
		echo "\n";
	}
	function logArticle($article) {
		
		echo "Code :<br /><span style='color:red'>" . htmlspecialchars($article) . "</span>";
		echo "<br /><br /><br /><br />";
	}
	function logCode($code) {

		echo "<span style='color:blue'>" . $code . "</span>";
		echo "<br /><br /><br /><br />";		
	}
?> 