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

	// Get entries
	$sql = "SELECT DISTINCT `filename` FROM `housekeeping_itemid_filename`;";
    $result = mysqli_query($conn, $sql);

    // Check result
	if (mysqli_num_rows($result) <= 0)
	{
        echo "No entries fetched.\n";
        return;
	}

	// Iterate through rows
	while ($row = mysqli_fetch_assoc($result))
    {
        $filename = $row['filename'];
        $filename_escaped = mysqli_real_escape_string($conn, $filename);
        echo "Working on \"" . $filename . "\".\n";

        // Get the ids in this file
        $ids = [];
        $sql = "SELECT `item_id` FROM `housekeeping_itemid_filename` WHERE `filename` = '$filename_escaped';";
        $result2 = mysqli_query($conn, $sql);
        while ($row2 = mysqli_fetch_assoc($result2))
        {
            $itemId = $row2['item_id'];
            $ids[] = $itemId;
        }

        // Check there are Ids
        if (empty($ids))
        {
            echo "No item IDs for \"$filename\".\n";
            continue;
        }

        // Get the files for those ids
        $relatedFilenames = [];
        $ids_quoted = array_map(fn($id) => "'$id'", $ids);
        $sql = "SELECT DISTINCT `filename` FROM `housekeeping_itemid_filename` WHERE `item_id` IN (" . implode(',', $ids_quoted) . ");";
        $result3 = mysqli_query($conn, $sql);
        if ($result3)
        {
            while ($row3 = mysqli_fetch_assoc($result3))
            {
                $relFile = $row3['filename'];
                $relatedFilenames[$relFile] = true;
            }
        }
        $relatedFilenames = array_keys($relatedFilenames);

        // Upload the data
        $values = [];
        foreach ($relatedFilenames as $fn)
        {
            $fn_escaped = mysqli_real_escape_string($conn, $fn);
            $values[] = "('$filename_escaped', '$fn_escaped')";
        }
        if (!empty($values)) {

            $query = "INSERT IGNORE INTO housekeeping_filename_related (filename, related_filename) VALUES " . implode(", ", $values);
            if (!mysqli_query($conn, $query))
            {
                echo "Error: " . mysqli_error($conn);
            }
            else
            {
                echo "Inserted " . mysqli_affected_rows($conn) . " rows.\n";
            }
        }
    }

    // Close connection
	mysqli_close($conn);
