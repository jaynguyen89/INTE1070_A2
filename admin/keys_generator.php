<?php
session_start();

require_once '../db_config.php';
global $link;

$response = array('status' => 'failed');

$query = 'SELECT public_key FROM users WHERE id = '.$_SESSION['user_id'];
$data = mysqli_fetch_array(mysqli_query($link, $query));

$current_pkfile = $data['public_key'];
if ($current_pkfile)
    unlink($_SERVER['DOCUMENT_ROOT'].'/inte2/assets/security/'.$current_pkfile);

$key_pair = openssl_pkey_new([
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
]);

$privateKey = '';
openssl_pkey_export($key_pair, $privateKey);

$details = openssl_pkey_get_details($key_pair);
$publicKey = $details['key'];

$response = array('status' => 'success');
$file_name = md5(time()).'.pem';

$file_path = $_SERVER['DOCUMENT_ROOT'].'/inte2/assets/security/'.$file_name;
$file = fopen($file_path, 'w');
if ($file) {
    fwrite($file, $publicKey);
    fclose($file);

    $query = 'UPDATE users SET public_key = \''.$file_name.'\' WHERE id = '.$_SESSION['user_id'];
    if (!mysqli_query($link, $query))
        $response = array('status' => 'failed');
}
else $response = array('status' => 'failed');

if ($response['status'] == 'success') $response['privateKey'] = $privateKey;
echo json_encode($response);