<?php

require_once '../rsa/keygen.php';
require_once '../rsa/signer.php';
require_once '../db_config.php';
global $link;

function sign_cheque_personally($payee, $amount, $privateKey) {
    $message = strtoupper($payee).' '.number_format((float) $amount, 2);

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

function sign_cheque_multisig($cheque_id) {
    global $link;

    $query = 'SELECT COUNT(*) AS counting FROM agreements WHERE expense_id = '.$cheque_id;
    $result = mysqli_fetch_array(mysqli_query($link, $query), MYSQLI_ASSOC);

    if ($result['counting'] == 0) { //first signature when cheque is being created
        $keys = compute_keys_multi();

        if ($keys) {
            $query = 'SELECT amount FROM expenses WHERE id = ' . $cheque_id;

            $result = mysqli_fetch_array(mysqli_query($link, $query), MYSQLI_ASSOC);
            $amount = $result['amount'] * 100;

            $private_key = $keys['priv'][0];
            $signature = sign_cheque($amount, $private_key['key'], $keys['pub']['n']);

            if ($signature > 1) {
                $private_key['used'] = true;
                $keys['priv'][0] = $private_key;

                $query = 'INSERT INTO multisig_keys (public_keys, private_keys, is_active)
                      VALUES (\'' . json_encode($keys['pub']) . '\', \'' . json_encode($keys['priv']) . '\', true);';

                if (mysqli_query($link, $query)) {
                    $key_id = mysqli_insert_id($link);
                    $query = 'UPDATE expenses SET multisig = true WHERE id = ' . $cheque_id;

                    if (mysqli_query($link, $query)) return array('sig' => $signature, 'key_id' => $key_id);
                }
            }
        }
    }
    else {
        $query = 'SELECT key_id, signature, max(signed_on) AS last_signed FROM agreements WHERE expense_id = '.$cheque_id;
        $result = mysqli_query($link, $query);
        $agreement = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $signature = intval($agreement['signature']);

        $query = 'SELECT * FROM multisig_keys WHERE id = '.$agreement['key_id'].' ';
        $result = mysqli_query($link, $query);
        $keys = mysqli_fetch_array($result);

        $public_keys = json_decode($keys['public_keys'], true);
        $private_keys = json_decode($keys['private_keys'], true);

        $private_key = $private_keys[1]['used'] ? $private_keys[2] : $private_keys[1];
        $signature = sign_cheque($signature, $private_key['key'], $public_keys['n']);

        if ($signature > 1) {
            $key_index = array_search($private_key, $private_keys);
            $private_key['used'] = true;

            $private_keys[$key_index] = $private_key;
            $query = $key_index == 2
                ? 'UPDATE multisig_keys SET private_keys = \''.json_encode($private_keys).'\', is_active = false WHERE id ='.$keys['id']
                : 'UPDATE multisig_keys SET private_keys = \''.json_encode($private_keys).'\' WHERE id ='.$keys['id'];

            if (mysqli_query($link, $query)) return array('sig' => $signature, 'key_id' => $keys['id']);
        }
    }

    return null;
}