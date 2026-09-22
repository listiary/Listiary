<?php

    // Check args
    if ($argc < 2) {
        echo "Usage: php download_files.php /path/to/directory\n";
        exit(1);
    }

    $maxFiles = 0; //Set to >0 if you need to limit the number of files fetched
    $directory = rtrim($argv[1], "/");

    // Check if valid directory
    if (!is_dir($directory)) {
        echo "Error: '$directory' is not a directory.\n";
        exit(1);
    }

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
    $sql = "SELECT `filename`, `content` FROM `describe_documents`";
    if ($maxFiles > 0) {
        $sql .= " LIMIT $maxFiles";
    }

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error fetching files: " . mysqli_error($conn));
    }

    while ($row = mysqli_fetch_assoc($result)) {

        $filename = $row['filename'];
        $content = $row['content'];

        $filePath = $directory . DIRECTORY_SEPARATOR . $filename . ".ds";

        // Write the file
        if (file_put_contents($filePath, $content) === false)
        {
            echo "  Error writing $filename\n";
        }
        else
        {
            echo "  Downloaded $filename\n";
        }
    }

    echo "Task completed!\n";

    // Close connection
	mysqli_close($conn);
