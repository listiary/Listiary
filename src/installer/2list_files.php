<?php

    //list all the files in the DB
    //list_files.php

    $maxFiles = 0; //Set to >0 if you need to limit the number of files fetched

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


	// Fetch files from database
    $sql = "SELECT `filename` FROM `describe_documents`";
    if ($maxFiles > 0) {
        $sql .= " LIMIT $maxFiles";
    }
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error fetching filenames: " . mysqli_error($conn));
    }

    // Show results
    echo "File listing:\n\n";
    while ($row = mysqli_fetch_assoc($result)) {

        $filename = $row['filename'];
        echo "$filename\n";
    }
    echo "\nTask completed!\n";

    // Close connection
	mysqli_close($conn);
