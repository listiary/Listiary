<?php

    //delete all files from the DB, after a confirmation
    //php delete_files.php

    // Confirm the dangerous operation we are about to perform
    echo "!!! WARNING !!!\n";
    echo "This will permanently delete ALL files from the database.\n";
    echo "Type 'delete all files' to confirm: ";
    $confirmation = trim(readline());
    if ($confirmation !== "delete all files") {
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

	// Delete files from database
    $sql = "DELETE FROM `describe_documents`";
    $result = mysqli_query($conn, $sql);
    if (!$result)
    {
        die("Error deleting files from the DB: " . mysqli_error($conn));
    }

    // Report back
    $deleted = mysqli_affected_rows($conn);
    echo "Deleted $deleted file(s) from the database.\n";
    echo "Task completed!\n";


    // Close connection
	mysqli_close($conn);
