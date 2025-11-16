<?php

    //delete a file from the DB, after a confirmation
    //php delete_file.php "name.in.database"

    // Check args
    $name = "";
    if ($argc < 2) {
        echo "Usage: php delete_file.php name.in.database\n";
        exit(1);
    }
    $name = $argv[1];
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $name))
    {
        echo "Invalid name. Only letters, digits, '.', '-', '_' are allowed.\n";
        exit(1);
    }

    // Confirm the dangerous operation we are about to perform
    echo "!!! WARNING !!!\n";
    echo "This will permanently delete file '$name' from the database.\n";
    echo "Do you want to proceed (y/n): ";
    $confirmation = trim(readline());
    if (strtolower($confirmation) !== "y") {
        echo "Aborted. Nothing was deleted.\n";
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
	$sql = "DELETE FROM `describe_documents` WHERE `filename` = '$nameEscaped';";
	$result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error deleting file: " . mysqli_error($conn));
    }

    // Report back
    $deletedRows = mysqli_affected_rows($conn);
    if ($deletedRows === 0)
    {
        echo "No file named '$name' was found in the database.\n";
    }
    else
    {
        echo "Deleted $deletedRows row(s) from the database.\n";
    }
    echo "Task completed!\n";


    // Close connection
	mysqli_close($conn);
