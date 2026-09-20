<?php

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

	// Get entries
	$sql = "SELECT `content`, `filename` FROM `describe_documents`;";
    $result = mysqli_query($conn, $sql);

    // Check result
	if (mysqli_num_rows($result) <= 0)
	{
        echo "No entries fetched.\n";
        return;
	}

	// Iterate through rows
	while ($row = mysqli_fetch_assoc($result))
    {
        $filename = $row['filename'];
        $content = $row['content'];

        // compile data
        echo "Compiling $filename\n";
        $compilationResult = doPostRequest('https://bcm98vlf3b.execute-api.eu-north-1.amazonaws.com/', $content, $filename);
        //logPostResponse($compilationResult);

        if ($compilationResult == null)
        {
            echo "ERROR : response is NULL\n";
            continue;
        }

        // Decode response JSON
        $jArr = json_decode($compilationResult, true);

        // Check if decoding failed
        if (!is_array($jArr)) {
            echo "ERROR: Failed to parse JSON\n";
            echo "\n\nRaw Response:\n$compilationResult\n\n";
            continue;
        }

        // Check if "Output" is missing
        if (!isset($jArr["Output"])) {
            echo "ERROR: 'Output' is missing from response\n";
            echo "\n\nRaw Response:\n$compilationResult\n\n";
            continue;
        }

        // Check if "Result" is missing
        if (!isset($jArr["Result"])) {
            echo "ERROR: 'Result' is missing from response\n";
            echo "\n\nRaw Response:\n$compilationResult\n\n";
            continue;
        }

        // Compare result, safely using double quotes around array key
        if (strtolower($jArr["Result"]) !== "success") {
            echo "ERROR: Result is " . $jArr["Result"] . "\n";
            echo "\n\nRaw Response:\n$compilationResult\n\n";
            continue;
        }

        // Decode the compiled output
        $compiled64 = $jArr["Output"];
        $compiledJson = base64_decode($compiled64);
        //echo $compiledJson;

        $entry_safe = mysqli_real_escape_string($conn, $filename);
        $content_safe = mysqli_real_escape_string($conn, $compiledJson);
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
            echo "Document saved.\n";
        }
    }

    // Close connection
	mysqli_close($conn);

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
    return $result;
}
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

    echo "Code :\n" . htmlspecialchars($article);
    echo "\n\n\n\n";
}
function logCode($code) {

    echo $code;
    echo "\n\n\n\n";
}
