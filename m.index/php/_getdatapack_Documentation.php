<?php

	// Set credentials
	require_once "_config.php";
    $servername = DB_SERVER_DOCUMENTATION;
    $username = DB_USERNAME_DOCUMENTATION;
    $password = DB_PASSWORD_DOCUMENTATION;
    $dbname = DB_NAME_DOCUMENTATION;

	// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	
	// Check connection
	if (!$conn) 
	{
		die("Connection failed: " . mysqli_connect_error());
	}

	// Run query
	$sql = "SELECT * FROM artifacts ORDER BY timestamp DESC LIMIT 1;";
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
