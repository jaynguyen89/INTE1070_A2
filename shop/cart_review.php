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
$post_data = null;
if (array_key_exists('cart', $_POST)) {
    $post_data = $_POST['cart'];
    unset($_POST['cart']);
}

$cart_to_display = [];
if ($post_data) {
    $query = 'SELECT * FROM shopping_carts WHERE user_id = '.$_SESSION['user_id'].' AND isPaid = false';
    $result = mysqli_fetch_array(mysqli_query($link, $query));
    if ($result) {
        $query = 'DELETE FROM shopping_carts WHERE user_id = '.$_SESSION['user_id'].' AND isPaid = false';
        mysqli_query($link, $query);
    }

    $query = "INSERT INTO shopping_carts (user_id) VALUES (".$user_id.");";
    if (mysqli_query($link, $query)) {
        $db_cart_id = mysqli_insert_id($link);
        $order_number = strtoupper(substr(md5($db_cart_id.$user_id.time()), 0, 16));

        $query = "UPDATE shopping_carts SET order_number = '".$order_number."' WHERE id = ".$db_cart_id;
        mysqli_query($link, $query);

        $cart_items = json_decode($post_data, true);
        foreach ($cart_items as $item) {
            $query = 'INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (' . $db_cart_id . ', ' . $item['item'] . ', ' . $item['qty'] . ');';
            mysqli_query($link, $query);

            $query = 'SELECT P.id, P.product_name, P.unit_price, P.photo FROM products P WHERE id = '.$item['item'];
            $result = mysqli_query($link, $query);

            $data = mysqli_fetch_array($result,MYSQLI_ASSOC);
            $data['quantity'] = $item['qty'];
            array_push($cart_to_display, $data);
        }
    }
}
else {
    $query = 'SELECT  P.id, P.product_name, P.unit_price, P.photo, I.quantity
              FROM products P, shopping_carts C, cart_items I
              WHERE C.user_id = '.$_SESSION['user_id'].' AND payment_id IS NULL
              AND I.cart_id = C.id AND P.id = I.product_id';

    $result = mysqli_query($link, $query);
    while ($row = mysqli_fetch_assoc($result))
        array_push($cart_to_display, $row);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>INTE1070</title>

    <link href="https://fonts.googleapis.com/css2?family=Balsamiq+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/custom.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
    <script src="../assets/custom.js"></script>
</head>
<body>
<div class="inte-header">
    <h2><a class="a-header" href="../home/home.php">INTE1070</a>: Secure Electronic Commerce</h2>
</div>

<div class="container" style="margin-bottom: 80px">
    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h5>Please review the items in your cart before checkout</h5>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <div class="card">
            <h5 class="card-header"><i class="fas fa-cart-plus"></i>&nbsp;&nbsp;Your cart</h5>
            <div class="card-body">
                <?php $total = 0; if (!$cart_to_display) { ?>
                    <div class="row" id="cart-items">
                        <p style="margin: 1rem auto;" id="no-item">You have no item in your cart.</p>
                    </div>
                <?php } else { ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Item</th>
                                <th scope="col">Price</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php foreach ($cart_to_display as $item) { ?>
                        <tr>
                            <th scope="row">
                                <?php echo (array_search($item, $cart_to_display) + 1); ?>
                                <img class="img-fluid" src="../assets/images/<?php echo $item['photo']; ?>" alt="<?php echo $item['product_name']; ?>" width="100px" />
                            </th>
                            <td><?php echo $item['product_name']; ?></td>
                            <td>$<?php echo $item['unit_price']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo ($item['unit_price'] * number_format((float)$item['quantity'], 2)); ?></td>
                        </tr>
                    <?php $total += ($item['unit_price'] * number_format((float) $item['quantity'], 2)); } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-12" style="background-color: #fadedc; border-radius: 4px;">
                        <b>Total:</b>
                        <b class="float-right">$<?php echo $total; ?></b>
                    </div>
                    <div class="col-sm-12" style="margin-bottom: 1rem">
                        <p>Please select a payment method below:</p>
                        <div class="row justify-content-center" id="payment-methods">
                            <div class="col-sm-1">
                                <a class="a-button" role="button" id="visa" <?php if ($total) { ?>onclick="selectPaymentMethod('visa')"<?php } ?>>
                                    <img class="img-fluid" src="../assets/logos/visa-card.png">
                                </a>
                            </div>
                            <div class="col-sm-1">
                                <a class="a-button" role="button" id="paypal" <?php if ($total) { ?>onclick="selectPaymentMethod('paypal')"<?php } ?>>
                                    <img class="img-fluid" src="../assets/logos/paypal.png">
                                </a>
                            </div>
                            <div class="col-sm-1">
                                <a class="a-button" role="button" id="google" <?php if ($total) { ?>onclick="selectPaymentMethod('google')"<?php } ?>>
                                    <img class="img-fluid" src="../assets/logos/google-pay.png">
                                </a>
                            </div>
                            <div class="col-sm-1">
                                <a class="a-button" role="button" id="apple" <?php if ($total) { ?>onclick="selectPaymentMethod('apple')"<?php } ?>>
                                    <img class="img-fluid" src="../assets/logos/apply-pay.png">
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <a class="btn btn-primary float-right disabled" role="button" id="checkout-button"
                           onclick="checkout()">
                            <i class="fas fa-shopping-basket"></i>&nbsp;Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>