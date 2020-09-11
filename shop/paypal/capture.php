<?php

use SendGrid\Client;

session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

require_once '../../db_config.php';
global $link;

$user_id = $_SESSION['user_id'];
$paypal_order_id = array_key_exists('orderId', $_POST) ? $_POST['orderId'] : null;
$authId = array_key_exists('authId', $_POST) ? $_POST['authId'] : null;

if ($paypal_order_id && $authId) {
    //Get access token to send REST requests to Paypal API
    $oauth_url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
    $access_token_request = curl_init();

    curl_setopt($access_token_request, CURLOPT_URL, $oauth_url);
    curl_setopt($access_token_request,CURLOPT_POST, true);
    curl_setopt($access_token_request, CURLOPT_SSLVERSION , 6); //New version of Paypal API requires this explicit value, not working otherwise
    curl_setopt($access_token_request,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($access_token_request, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Accept-Language: en-US']);
    curl_setopt($access_token_request, CURLOPT_USERPWD, 'ASRvVBDpjSkg9hMfMD1h_bEAcVzMIg91FXtqhA6pRHrSluyPwuT7-rpSgoPfleFh757E0XcZ6tLCZYtG:EOqlOzjiRJa4qpL7yFVSd95s8G7cX4DDxqqr7x1gULvXAs00uE2RxIeU4KiKhgapVe-wtbmNLi_0gwpk');
    curl_setopt($access_token_request, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');

    $response = curl_exec($access_token_request);
    $json_response = json_decode($response, true);

    $access_token = $json_response['access_token'];
    curl_close($access_token_request);

    //User has authorized a payment for the order, verify the payment authorization
    $order_verification_url = 'https://api.sandbox.paypal.com/v2/checkout/orders/'.$paypal_order_id.'/';
    $order_verification_request = curl_init();

    curl_setopt($order_verification_request, CURLOPT_URL, $order_verification_url);
    curl_setopt($order_verification_request,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($order_verification_request, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer '.$access_token]);

    $response = curl_exec($order_verification_request);
    $json_response = json_decode($response, true);
    curl_close($order_verification_request);

    $payment_auth = $json_response['purchase_units'][0]['payments']['authorizations'][0];

    if ($json_response['status'] == 'COMPLETED' && $payment_auth['status'] == 'CREATED' &&
        strtotime($payment_auth['expiration_time']) > time()
    ) {
        //Verification success, now update database
        $payment_gross = $payment_auth['amount']['value'];
        $order_number = $json_response['purchase_units'][0]['reference_id'];

        $query = 'INSERT INTO payments (payment_gross, currency_code, payment_status, paypal_order_id, authorization_id)
                  VALUES ('.$payment_gross.', \'AUD\', \'AUTHORIZED\', \''.$paypal_order_id.'\', \''.$authId.'\');';

        if (mysqli_query($link, $query)) {
            $inserted_payment_id = mysqli_insert_id($link);

            //Next send a request to capture money from the authorized payment
            $capture_url = 'https://api.sandbox.paypal.com/v2/payments/authorizations/'.$authId.'/capture';
            $capture_request = curl_init();

            curl_setopt($capture_request, CURLOPT_URL, $capture_url);
            curl_setopt($capture_request,CURLOPT_POST, true);
            curl_setopt($capture_request,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($capture_request, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer '.$access_token]);

            $response = curl_exec($capture_request);
            $json_response = json_decode($response, true);

            //Capturing success, now save data
            if ($json_response['status'] == 'COMPLETED') {
                $_SESSION['order'] = $order_number;

                $query = 'UPDATE payments
                          SET paypal_payment_id = \''.$json_response['id'].'\', payment_status = \'COMPLETED\'
                          WHERE id = '.$inserted_payment_id;

                if (mysqli_query($link, $query)) {
                    $purchased_items = array();

                    $query = 'SELECT I.product_id, I.quantity FROM shopping_carts C, cart_items I
                              WHERE C.id = I.cart_id AND C.user_id = '.$user_id.' AND C.isPaid = false;';

                    $data = mysqli_query($link, $query);
                    while ($item = mysqli_fetch_assoc($data))
                        array_push($purchased_items, $item);

                    $query = 'UPDATE shopping_carts
                              SET payment_id = '.$inserted_payment_id.', isPaid = true
                              WHERE user_id = '.$user_id.' AND isPaid = false;';

                    if (mysqli_query($link, $query)) {
                        $_SESSION['purchased_items'] = json_encode($purchased_items);
                        header('location: success.php');
                    }
                    else {
                        $_SESSION['failure'] = 'update_shopping_carts';
                        header('location: error.php');
                    }
                }
                else {
                    $_SESSION['failure'] = 'update_payments';
                    header('location: error.php');
                }
            }
            else {
                $_SESSION['failure'] = 'payment_capture';
                header('location: error.php');
            }
        }
        else {
            $_SESSION['failure'] = 'insert_payments';
            header('location: error.php');
        }
    }
    else {
        $_SESSION['failure'] = 'verify_authorization';
        //header('location: error.php');
    }
}
else header('location: error.php');
