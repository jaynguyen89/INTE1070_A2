<?php

require_once '../db_config.php';
global $link;

$cheque_id = $_GET['chequeId'];
$error = false;
$verified = true;

//Verify signature for separate signing, so read all signatures on the cheque from agreements table
$query = 'SELECT signed_id, signature FROM agreements WHERE expense_id = '.$cheque_id;
$result = mysqli_query($link, $query);

$signatures = array();
if ($result) {
    while ($sig = mysqli_fetch_assoc($result))
        $signatures[$sig['signed_id']] = $sig['signature'];
}
else $error = true;

//read data on the cheque from database to create the signing message
$query = 'SELECT payee, amount FROM expenses WHERE id = '.$cheque_id;
$result = mysqli_query($link, $query);

$message = null; //the message to be verified
if ($result) {
    $data = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $message = strtoupper($data['payee']).' '.number_format((float) $data['amount'], 2);
}
else $error = true;

if (!$error) { //message retrieved successfully, then verify it
    foreach ($signatures as $signed_id => $signature) {
        //Get the public key filename for each owner (each signature) to verify
        $query = 'SELECT public_key FROM users WHERE id = ' . $signed_id;

        $result = mysqli_query($link, $query); //read the public keys from files
        $pbk_fname = null;
        if ($result) {
            $data = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $pbk_fname = $data['public_key'];
        }
        else {
            $error = true;
            break;
        }

        //read public key and signatures from files and verify
        $pbk = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/inte2/assets/security/' . $pbk_fname);
        $sig = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/inte2/assets/security/' . $signature);

        $verified = openssl_verify($message, $sig, $pbk, "sha256WithRSAEncryption");
        if (!$verified) break;
    }

    echo !$error ? ($verified ? 'success' : 'failed') : 'error';
}
else echo 'error';
