<?php

use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

require '../../vendor/autoload.php';
session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

require_once '../../db_config.php';
global $link;

$paymentToken = array_key_exists('paymentToken', $_POST) ? $_POST['paymentToken'] : null;
$data = array_key_exists('checkout_data', $_SESSION) ? $_SESSION['checkout_data'] : null;

if ($data == null || $paymentToken == null) {
    $_SESSION['failure'] = 'missing_data';
    header('location: error.php');
}

$checkout_data = json_decode($data, true);

$grand_total = $checkout_data['grand_total'];
$order_number = $checkout_data['order_number'];
$items = $checkout_data['items'];

$_SESSION['order'] = $order_number;

Stripe::setApiKey('sk_test_51HQDZND2FG7NncIE67ajAx6FuANOhZQQVUrzM1j7CKk43zOzwXhmLwVXqLSu1SxvDxiQj5eLwbgJqDQC9ZAMiL8300Q2mnLvSi');

$charge = null;
try {
    $charge = Charge::create(array(
        'amount' => round($grand_total * 1.0125 + 8.95, 2)*100,
        'currency' => 'AUD',
        'description' => $order_number,
        'source' => $paymentToken,
    ));
} catch (ApiErrorException $e) {
    $_SESSION['failure'] = 'charge_failed';
    header('location: error.php');
}

if ($charge && $charge['amount_refunded'] == 0 && $charge['failure_code'] == null && empty($charge['fraud_details']) &&
    $charge['paid'] && $charge['outcome']['risk_score'] < 75 && $charge['status'] == 'succeeded'
) {
    $payment_gross = round($grand_total * 1.0125 + 8.95, 2);
    $transaction_id = $charge['balance_transaction'];
    $charge_id = $charge['id'];

    $query = 'INSERT INTO payments (payment_gross, currency_code, payment_status, transaction_id, charge_id)
              VALUES ('.$payment_gross.', \'AUD\', \'COMPLETED\', \''.$transaction_id.'\', \''.$charge_id.'\');';

    if (mysqli_query($link, $query)) {
        $payment_id = mysqli_insert_id($link);
        $query = 'UPDATE shopping_carts SET payment_id = '.$payment_id.', isPaid = true
                  WHERE user_id = '.$_SESSION['user_id'].' AND isPaid = false AND order_number = \''.$_SESSION['order'].'\';';

        if (mysqli_query($link, $query)) {
            foreach ($items as $item) {
                $query = 'SELECT stock FROM products WHERE id = '.$item['id'];
                $result = mysqli_fetch_array(mysqli_query($link, $query));

                $query = 'UPDATE products SET stock = '.($result['stock'] - $item['quantity']).' WHERE id = '.$item['id'];
                mysqli_query($link, $query);
            }

            header('location: success.php');
        }
        else {
            $_SESSION['failure'] = 'update_shopping_carts';
            header('location: error.php');
        }
    }
    else {
        $_SESSION['failure'] = 'insert_payments';
        header('location: error.php');
    }
}
else {
    $_SESSION['failure'] = 'charge_failed';
    header('location: error.php');
}