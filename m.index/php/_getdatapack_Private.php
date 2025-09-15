<?php

	if(isset($_GET['usercode']) == false) die("'usercode' parameter of the url is not set");
	$u = $_GET['usercode'];
		//echo "Got usercode parameter - '" . $u . "'<br />";


	// Set credentials
	require_once "_config.php";
    $servername = DB_SERVER_PRIVATE;
    $username = DB_USERNAME_PRIVATE;
    $password = DB_PASSWORD_PRIVATE;
    $dbname = DB_NAME_PRIVATE;

	// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	
	// Check connection
	if (!$conn) 
	{
		die("Connection failed: " . mysqli_connect_error());
	}

	// Run query
	$sql = "SELECT * FROM artifacts WHERE name='" . $u . "' ORDER BY timestamp DESC LIMIT 1;";
	$result = mysqli_query($conn, $sql);

	// Output result
	if (mysqli_num_rows($result) > 0) 
	{
		while($row = mysqli_fetch_assoc($result)) 
		{
			echo $row["content"];
			break;
		}
	} 
	else 
	{
		echo "{}";
	}

	mysqli_close($conn);
?> 
