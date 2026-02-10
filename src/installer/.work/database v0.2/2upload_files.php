<?php

    // Check args
    if ($argc < 2) {
        echo "Usage: php upload_files.php /path/to/directory\n";
        exit(1);
    }

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

	// Read all files in directory
	$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($rii as $file)
    {
        if ($file->isDir()) continue;
        //if (pathinfo($file, PATHINFO_EXTENSION) !== 'ds') continue;

        $fullPath = $file->getPathname();
        $relativePath = substr($fullPath, strlen($directory) + 1); // relative to root

        echo "Uploading: $relativePath\n";

        $content = file_get_contents($fullPath);
        if ($content === false)
        {
            echo "  Error reading $relativePath\n";
            continue;
        }

        $filenameNoExt = pathinfo($relativePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($relativePath, PATHINFO_FILENAME);
        if (substr($filenameNoExt, 0, 2) === './') $filenameNoExt = substr($filenameNoExt, 2);
        $filename = mysqli_real_escape_string($conn, $filenameNoExt);
        $contentEscaped = mysqli_real_escape_string($conn, $content);

        $sql = "INSERT IGNORE INTO `describe_documents` (`filename`, `content`) VALUES ('$filename', '$contentEscaped')";
        if (!mysqli_query($conn, $sql))
        {
            echo "  Error inserting $relativePath: " . mysqli_error($conn) . "\n";
        }
        else
        {
            echo "  Uploaded successfully.\n";
        }
    }

    // Close connection
	mysqli_close($conn);
