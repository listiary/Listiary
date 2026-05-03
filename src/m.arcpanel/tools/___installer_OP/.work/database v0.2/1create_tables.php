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

    //create tables
    // SQL statements to create tables and indexes
    $sqlStatements = [

        // compiled_documents table
        "CREATE TABLE IF NOT EXISTS compiled_documents (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(333) NOT NULL UNIQUE,
            content LONGTEXT NOT NULL,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // describe_documents table
        "CREATE TABLE IF NOT EXISTS describe_documents (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(333) NOT NULL UNIQUE,
            content LONGTEXT NOT NULL,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // Indexes for filename lookups
        "CREATE INDEX IF NOT EXISTS idx_filename_cd USING BTREE ON compiled_documents (filename(333))",
        "CREATE INDEX IF NOT EXISTS idx_filename_dd USING BTREE ON describe_documents (filename(333))",

        // housekeeping_itemid_filename table
        "CREATE TABLE IF NOT EXISTS housekeeping_itemid_filename (
            filename VARCHAR(333) NOT NULL,
            item_id VARCHAR(333) NOT NULL,
            PRIMARY KEY (filename(150), item_id(150))
        )",

        // Index to speed up lookups by item_id
        "CREATE INDEX IF NOT EXISTS idx_item_id ON housekeeping_itemid_filename (item_id)",

        // housekeeping_filename_related table
        "CREATE TABLE IF NOT EXISTS housekeeping_filename_related (
            filename VARCHAR(333) NOT NULL,
            related_filename VARCHAR(333) NOT NULL,
            PRIMARY KEY (filename(150), related_filename(150))
        )",

        // Index for related_filename lookups
        "CREATE INDEX IF NOT EXISTS idx_related_filename ON housekeeping_filename_related (related_filename)",

        // accounts table
        "CREATE TABLE IF NOT EXISTS accounts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(30) NOT NULL UNIQUE,
            usercode VARCHAR(8) NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            is_bot BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    // Execute each SQL statement
    foreach ($sqlStatements as $sql) {
        if (mysqli_query($conn, $sql)) {
            echo "Executed: " . strtok($sql, "(") . "\n";
        } else {
            echo "Error: " . mysqli_error($conn) . "\n";
        }
    }


    // Close connection
	mysqli_close($conn);

