<?php
header('Access-Control-Allow-Origin: *');
require '../../vendor/autoload.php';

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

require_once '../../db_config.php';
global $link;

$data = array_key_exists('checkout_data', $_SESSION) ? $_SESSION['checkout_data'] : null;
if ($data == null) {
    $_SESSION['failure'] = 'missing_data';
    header('location: error.php');
}

$checkout_data = json_decode($data, true);

$grand_total = $checkout_data['grand_total'];
$order_number = $checkout_data['order_number'];
$items = $checkout_data['items'];

$query = 'SELECT order_number FROM shopping_carts WHERE user_id = '.$_SESSION['user_id'].' AND isPaid = false;';
$result = mysqli_fetch_array(mysqli_query($link, $query));

$_SESSION['order'] = $result['order_number'];

$purchased_items = array();

$query = 'SELECT I.product_id, I.quantity FROM shopping_carts C, cart_items I
          WHERE C.id = I.cart_id AND C.user_id = '.$_SESSION['user_id'].' AND C.isPaid = false;';

$data = mysqli_query($link, $query);
while ($item = mysqli_fetch_assoc($data))
    array_push($purchased_items, $item);

$_SESSION['purchased_items'] = json_encode($purchased_items);

Stripe::setApiKey('sk_test_51HQDZND2FG7NncIE67ajAx6FuANOhZQQVUrzM1j7CKk43zOzwXhmLwVXqLSu1SxvDxiQj5eLwbgJqDQC9ZAMiL8300Q2mnLvSi');

$product_data = '';
$countAll = 0;
foreach ($items as $item) {
    $product_data .= ($item['product_name'].',');
    $countAll += $item['quantity'];
}

$checkout_session = null;
try {
    $checkout_session = Session::create([ //create a checkout session for user, in which they need to authorize payment
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'AUD',
                'product_data' => [
                    'name' => $product_data
                ],
                'unit_amount' => round(($grand_total * 1.0125 + 8.95)/$countAll, 2) * 100
            ],
            'quantity' => $countAll,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost:81/inte2/shop/card/success.php', //endpoints for us to continue handling the payment
        'cancel_url' => 'http://localhost:81/inte2/shop/card/checkout.php', //after customer have authorized it
    ]);
} catch (ApiErrorException $e) {
    $_SESSION['failure'] = 'charge_failed';
    header('location: error.php');
}

echo json_encode(['id' => $checkout_session->id]);