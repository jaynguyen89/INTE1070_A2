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
$post_data = array_key_exists('cart', $_POST) ? $_POST['cart'] : null;
$payment_method = array_key_exists('payment_method', $_POST) ? $_POST['payment_method'] : null;

$grand_total = 0;
$order_number = '';
$items = [];
if ($post_data != null && $payment_method != null) {
    $query = "INSERT INTO shopping_carts (user_id, payment_method) VALUES (".$user_id.", '".$payment_method."');";

    if (mysqli_query($link, $query)) {
        $db_cart_id = mysqli_insert_id($link);
        $order_number = strtoupper(substr(md5($db_cart_id.$user_id.time()), 0, 16));

        $query = "UPDATE shopping_carts SET order_number = '".$order_number."' WHERE id = ".$db_cart_id;
        mysqli_query($link, $query);

        $cart_items = json_decode($post_data, true);
        foreach ($cart_items as $item) {
            $query = 'INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (' . $db_cart_id . ', ' . $item['item'] . ', ' . $item['qty'] . ');';
            mysqli_query($link, $query);

            $query = "SELECT id, product_name, unit_price FROM products WHERE id = ".$item['item'];
            $data = mysqli_fetch_array(mysqli_query($link, $query), MYSQLI_ASSOC);
            $grand_total += ($data['unit_price'] * $item['qty']);

            $data['quantity'] = $item['qty'];
            array_push($items, $data);
        }
    }
}
else {
    $query = "SELECT * FROM shopping_carts WHERE user_id = ".$user_id." AND payment_id IS NULL;";
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

    <script src="https://www.paypal.com/sdk/js?client-id=ASRvVBDpjSkg9hMfMD1h_bEAcVzMIg91FXtqhA6pRHrSluyPwuT7-rpSgoPfleFh757E0XcZ6tLCZYtG"></script>
</head>
<body>
<div class="inte-header">
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container" style="margin-bottom: 80px">
    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h5><?php echo $payment_method ? 'Complete your purchase' : 'It seems like you should not be here...'; ?></h5>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <div class="col-sm-12 text-center">
            <?php if ($grand_total == 0 || strlen($order_number) == 0 || !$payment_method) { ?>
                <p>We find no item in your cart nor any payment information.</p>
                <p>If you have come here somehow by mistake, please click <a href="../home/home.php">here</a> to go back.</p>
            <?php } else { ?>
                <div class="payment-proceed">
                    <h2>Pay with Paypal</h2>
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td>Order Number:</td>
                                <td><b><?php echo $order_number; ?></b></td>
                            </tr>
                            <tr>
                                <td>Ship To:</td>
                                <td><b>123 Place St., Somewhere, VIC 3210</b></td>
                            </tr>
                            <tr>
                                <td>Total Payable:</td>
                                <td><b>$<?php echo $grand_total; ?></b></td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="width: 60%; margin: auto;" id="paypal-button"></div>
                </div>

                <script>
                    paypal.Buttons({
                        createOrder: function (data, actions) {
                            return actions.order.create({
                                purchase_units : [{
                                    reference_id : '<?php echo $order_number; ?>',
                                    amount : {
                                        currency_code : 'AUD',
                                        amount : '<?php echo $grand_total; ?>'
                                    },
                                    payee : {
                                        
                                    }
                                }]
                            });
                        }
                    }).render('#paypal-button');
                </script>
            <?php } ?>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>