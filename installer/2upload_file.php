<?php

    //use 2 0r 3 arguments - if 3, the 3rd is the desired filename in the DB
    //php upload_file.php "/path/to/file" "name.in.database"

    // Check args
    $name = "";
    if ($argc < 2) {
        echo "Usage: php upload_file.php /path/to/file\n";
        echo "Usage: php upload_file.php /path/to/file name.in.database\n";
        exit(1);
    }
    if ($argc == 3) {

        $name = $argv[2];
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $name))
        {
            echo "Invalid name. Only letters, digits, '.', '-', '_' are allowed.\n";
            exit(1);
        }
    }

    $file = rtrim($argv[1], "/");

    // Check if valid file
    if (!is_file($file)) {
        echo "Error: '$file' is not a file.\n";
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

	// Insert file
	$fullPath = realpath($file);
    echo "Uploading file: $fullPath\n";

    $content = file_get_contents($fullPath);
    if ($content === false)
    {
        die("Error reading $fullPath\n");
    }

    if($name == "") $name = pathinfo($fullPath, PATHINFO_FILENAME);
    if (substr($name, 0, 2) === './') $name = substr($name, 2);
    $filename = mysqli_real_escape_string($conn, $name);
    $contentEscaped = mysqli_real_escape_string($conn, $content);

    $sql = "INSERT IGNORE INTO `describe_documents` (`filename`, `content`) VALUES ('$filename', '$contentEscaped')";
    if (!mysqli_query($conn, $sql))
    {
        echo "Error inserting $fullPath: " . mysqli_error($conn) . "\n";
    }
    else
    {
        echo "Uploaded successfully.\n";
    }

    // Close connection
	mysqli_close($conn);
