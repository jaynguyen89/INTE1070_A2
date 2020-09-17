<?php

require_once '../db_config.php';
global $link;

$cheque_id = $_GET['chequeId'];

$query = 'UPDATE expenses SET is_approved = true WHERE id = '.$cheque_id;
if (mysqli_query($link, $query)) echo 'success';
else echo 'error';