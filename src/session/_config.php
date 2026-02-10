<?php
define('DB_SERVER', 'web.jumphosting03.com');
define('DB_USERNAME', 'historic_ca7amaran82');
define('DB_PASSWORD', 'A4mh)uZ?Qq^Qx(i');
define('DB_NAME', 'historic_worldinlists_private');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>