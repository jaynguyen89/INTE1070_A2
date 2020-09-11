<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

require_once '../db_config.php';
global $link;

$user_id = $_SESSION['user_id'];
$payment_method = array_key_exists('payment_method', $_POST) ? $_POST['payment_method'] : null;

$grand_total = 0;
$order_number = '';
$items = [];
if ($payment_method) {
    $query = 'UPDATE shopping_carts SET payment_method = \''.strtoupper($payment_method).'\' WHERE user_id = '.$user_id.' AND isPaid = false;';

    if (mysqli_query($link, $query)) {
        $query = 'SELECT order_number FROM shopping_carts WHERE user_id = '.$user_id.' AND isPaid = false;';
        $result = mysqli_fetch_array(mysqli_query($link, $query));
        $order_number = $result['order_number'];

        $query = 'SELECT P.product_name, P.unit_price, I.quantity FROM shopping_carts C, cart_items I, products P
                  WHERE C.id = I.cart_id AND I.product_id = P.id AND C.user_id = '.$user_id.' AND C.isPaid = false;';
        $result = mysqli_query($link, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $grand_total += ($row['unit_price'] * $row['quantity']);
            array_push($items, $row);
        }
    }
}
else {
    $query = "SELECT * FROM shopping_carts WHERE user_id = ".$user_id." AND isPaid = false;";
    $result = mysqli_query($link, $query);

    $cart = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $order_number = $cart['order_number'];

    $query = "SELECT I.quantity, P.id, P.product_name, P.unit_price FROM cart_items I, products P WHERE cart_id = ".$cart['id']." AND P.id = I.product_id";
    $data = mysqli_query($link, $query);

    while ($item = mysqli_fetch_assoc($data)) {
        $grand_total += ($item['unit_price'] * $item['quantity']);
        array_push($items, $item);
    }

    $payment_method = $cart['payment_method'];
}

$_SESSION['checkout_data'] = json_encode(array('grand_total' => $grand_total, 'order_number' => $order_number, 'items' => $items));
header('location: '.$payment_method.'/checkout.php');