<?php
/*
Define Database credentials.
In MySQL server with default setting:
user is 'root' with no password
*/

$server_name = '69.90.66.140';
$port = 3306;
$user_name = 'jayde421_3493188';
$db_password = 'inte1070S3493188';
$db_name = 'jayde421_3493188db'; //ecommercedb

/* Connect to MySQL database */
global $link;
$link = mysqli_connect($server_name, $user_name, $db_password, $db_name, $port);

// Check database connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
