<?php

    // Ask user for values
    $servername = readline("Server name: ");
    $username = readline("Username: ");
    $password = readline("Password: ");
    $dbname = readline("Database name: ");

    // Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);

	// Check connection
	if (!$conn)
	{
        echo "Ok - values are NOT correct - we were not able to establish a connection to the server with the values you provided!\n";
		die("Connection failed: " . mysqli_connect_error());
	}
    else
    {
        // Close connection
        mysqli_close($conn);
        echo "Ok - values are correct - we were able to establish a connection to the server with the values you provided!\n";
    }

    // Get config template
    $filename = "_config_template.php";
    if (!file_exists($filename))
    {
        die("Config template file not found: $filename\n");
    }

    // Replace values
    $contents = file_get_contents($filename);
    $updated = str_replace("*SERVER_VALUE*", $servername, $contents);
    $updated = str_replace("*USERNAME_VALUE*", $username, $updated);
    $updated = str_replace("*PASSWORD_VALUE*", $password, $updated);
    $updated = str_replace("*DATABASE_NAME_VALUE*", $dbname, $updated);

    // Save new config file
    $now = date("Ymd-His");
    $newName = "_config(" . $now . ").php";
    file_put_contents($newName, $updated);
    echo "Done. Config created: '$newName'\n";
    echo "Rename to '_config.php' to use.\n";
