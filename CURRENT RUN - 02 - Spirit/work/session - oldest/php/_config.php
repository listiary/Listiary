<?php
define('DB_SERVER', '[REDACTED]');
define('DB_USERNAME', '[REDACTED]');
define('DB_PASSWORD', '[REDACTED]');
define('DB_NAME', '[REDACTED]');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>