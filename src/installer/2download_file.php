<?php

    //download a file from the DB
    //php download_file.php "name.in.database" "/download/directory"

    // Check args
    $name = "";
    if ($argc < 3) {
        echo "Usage: php download_file.php name.in.database /path/to/directory\n";
        exit(1);
    }
    $name = $argv[1];
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $name))
    {
        echo "Invalid name. Only letters, digits, '.', '-', '_' are allowed.\n";
        exit(1);
    }

    // Check if valid file
    $directory = rtrim($argv[2], "/");
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

	// Fetch file
    $nameEscaped = mysqli_real_escape_string($conn, $name);
	$sql = "SELECT `filename`, `content` FROM `describe_documents` WHERE `filename` = '$nameEscaped';";
	$result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error fetching file: " . mysqli_error($conn));
    }

    if ($row = mysqli_fetch_assoc($result))
    {
        $filename = $row['filename'];
        $content  = $row['content'];

        $filepath = $directory . "/" . $filename . ".ds";

        if (file_put_contents($filepath, $content) === false)
        {
            echo "Error: could not write file to $filepath\n";
            exit(1);
        }

        echo "File saved to: $filepath\n";
    }
    else
    {
        echo "File '$name' not found in database.\n";
    }

    // Close connection
	mysqli_close($conn);
