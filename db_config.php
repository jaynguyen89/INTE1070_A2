<?php
/*
Define Database credentials.
In MySQL server with default setting:
user is 'root' with no password
*/

$server_name = 'localhost';
$port = 3306;
$user_name = 'nlkp';
$db_password = 'nlkp1989';
$db_name = 'intedb'; //ecommercedb

/* Connect to MySQL database */
global $link;
$link = mysqli_connect($server_name, $user_name, $db_password, $db_name, $port);

// Check database connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
