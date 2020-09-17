<?php

require '../vendor/autoload.php';
require_once '../db_config.php';
global $link;

function sign_cheque_personally($payee, $amount, $privateKey) {
    $message = $payee . ' ' .number_format((float) $amount, 2);

    $signature = '';
    openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $sig_fname = 'sig_'.md5(time()).'.dat';

    $file_path = $_SERVER['DOCUMENT_ROOT'].'/inte2/assets/security/'.$sig_fname;
    $file = fopen($file_path, 'w');

    if ($file) {
        fwrite($file, $signature);
        fclose($file);
    }
    else return null;

    return $sig_fname;
}

function generate_combined_signature_for_cheque() {

}