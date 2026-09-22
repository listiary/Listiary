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
	$sql = "SELECT `content`, `filename` FROM `compiled_documents`;";
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
        $content = $row['content'];

        // Extract IDs
        $jArr = json_decode($content, true);
        $ids = [];
        extractIds($jArr, $ids);
        $ids = array_keys($ids);

        $length = count($ids);
        echo "\"" . $filename . "\" has $length public ids.\n";
        //print_r($ids); var_dump($ids); break;

        //upload the data
        $filename_safe = mysqli_real_escape_string($conn, $filename);
        $values = [];

        foreach ($ids as $id) {
            $item_id_safe = mysqli_real_escape_string($conn, $id);
            $values[] = "('$filename_safe', '$item_id_safe')";
        }

        if (!empty($values)) {

            $query = "INSERT IGNORE INTO housekeeping_itemid_filename (filename, item_id) VALUES " . implode(", ", $values);
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

function extractIds(array $node, array &$ids) {

    if (isset($node['id']) && is_string($node['id'])) {
        if (strpos($node['id'], '@') !== 0) {
            $ids[$node['id']] = true; // Use value as key to ensure uniqueness
        }
    }

    if (isset($node['items']) && is_array($node['items'])) {
        foreach ($node['items'] as $child) {
            if (is_array($child)) {
                extractIds($child, $ids);
            }
        }
    }
}
