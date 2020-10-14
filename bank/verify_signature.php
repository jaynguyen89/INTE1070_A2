<?php

require_once '../db_config.php';
global $link;

$cheque_id = $_GET['chequeId'];
$error = false;
$verified = true;

$query = 'SELECT signed_id, signature FROM agreements WHERE expense_id = '.$cheque_id;
$result = mysqli_query($link, $query);

$signatures = array();
if ($result) {
    while ($sig = mysqli_fetch_assoc($result))
        $signatures[$sig['signed_id']] = $sig['signature'];
}
else $error = true;

$query = 'SELECT payee, amount FROM expenses WHERE id = '.$cheque_id;
$result = mysqli_query($link, $query);

$message = null;
if ($result) {
    $data = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $message = strtoupper($data['payee']).' '.number_format((float) $data['amount'], 2);
}
else $error = true;

if (!$error) {
    foreach ($signatures as $signed_id => $signature) {
        $query = 'SELECT public_key FROM users WHERE id = ' . $signed_id;

        $result = mysqli_query($link, $query);
        $pbk_fname = null;
        if ($result) {
            $data = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $pbk_fname = $data['public_key'];
        }
        else {
            $error = true;
            break;
        }

        $pbk = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/inte2/assets/security/' . $pbk_fname);
        $sig = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/inte2/assets/security/' . $signature);

        $verified = openssl_verify($message, $sig, $pbk, "sha256WithRSAEncryption");
        if (!$verified) break;
    }

    echo !$error ? ($verified ? 'success' : 'failed') : 'error';
}
else echo 'error';
