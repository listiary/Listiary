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

	// Get all table names
    $tables = [];
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result))
    {
        $tables[] = $row[0];
    }

    // Drop each table
    foreach ($tables as $table)
    {
        $sql = "DROP TABLE IF EXISTS `$table`";
        if (mysqli_query($conn, $sql))
        {
            echo "Dropped table: $table\n";
        }
        else
        {
            echo "Error dropping $table: " . mysqli_error($conn) . "\n";
        }
    }

    // Close connection
	mysqli_close($conn);

