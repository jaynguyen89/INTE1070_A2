<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

require_once '../../db_config.php';
global $link;

$user_id = $_SESSION['user_id'];
$payment_method = array_key_exists('payment_method', $_POST) ? $_POST['payment_method'] : null;

$grand_total = 0;
$order_number = '';
$items = [];
if ($payment_method) {
    $query = 'UPDATE shopping_carts SET payment_method = \''.$payment_method.'\' WHERE user_id = '.$user_id.' AND isPaid = false;';

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
    <link rel="stylesheet" href="../../assets/custom.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
    <script src="../../assets/custom.js"></script>

    <script src="https://www.paypal.com/sdk/js?client-id=ASRvVBDpjSkg9hMfMD1h_bEAcVzMIg91FXtqhA6pRHrSluyPwuT7-rpSgoPfleFh757E0XcZ6tLCZYtG&intent=authorize&currency=AUD"></script>
</head>
<body>
<div class="inte-header">
    <h2><a class="a-header" href="../../home/home.php">INTE1070</a>: Secure Electronic Commerce</h2>
</div>

<div class="container" style="margin-bottom: 80px">
    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h5><?php echo $payment_method ? 'Complete your purchase' : 'It seems like you should not be here...'; ?></h5>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <div class="col-sm-12 text-center">
            <?php if ($grand_total == 0 || strlen($order_number) == 0 || !$payment_method) { ?>
                <p>We find no item in your cart nor any payment information.</p>
                <p>If you have come here somehow by mistake, please click <a href="../../home/home.php">here</a> to go back.</p>
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
                                <td>Order Total:</td>
                                <td><b>$<?php echo $grand_total; ?></b></td>
                            </tr>
                            <tr>
                                <td>Shipping Fee:</td>
                                <td><b>$8.95 (Australia Post Standard Flat Rate)</b></td>
                            </tr>
                            <tr>
                                <td>Total Payable:</td>
                                <td><b>$<?php echo ($grand_total + 8.95); ?> (including $<?php echo round(round($grand_total / 1.1, 2) * 0.1, 2); ?> GST)</b></td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="width: 60%; margin: auto;" id="paypal-button"></div>
                </div>

                <script>
                    let orderItems = [
                        <?php foreach ($items as $item) {
                            echo '{
                                name : \''.$item['product_name'].'\',
                                unit_amount : {
                                    currency_code : \'AUD\',
                                    value : \''.round($item['unit_price'] / 1.1, 2).'\'
                                },
                                quantity : '.$item['quantity'].',
                                category : \'PHYSICAL_GOODS\'
                            }'.(array_search($item, $items) != count($items) - 1 ? ',' : '');
                        } ?>
                    ];

                    paypal.Buttons({
                        createOrder : function (data, actions) {
                            return actions.order.create({
                                intent : 'AUTHORIZE',
                                purchase_units : [{
                                    reference_id : '<?php echo $order_number; ?>',
                                    amount : {
                                        currency_code : 'AUD',
                                        value : '<?php echo $grand_total + 8.95; ?>',
                                        breakdown : {
                                            item_total : {
                                                currency_code: 'AUD',
                                                value: '<?php echo round($grand_total / 1.1, 2); ?>'
                                            },
                                            shipping : {
                                                currency_code: 'AUD',
                                                value: '8.95'
                                            },
                                            tax_total : {
                                                currency_code: 'AUD',
                                                value: '<?php echo round(round($grand_total / 1.1, 2) * 0.1, 2); ?>'
                                            }
                                        }
                                    },
                                    items : orderItems,
                                }]
                            });
                        },
                        onError : function (error) {
                            window.location.href = '/shop/paypal/error.php';
                        },
                        onCancel : function (response) {
                            alert('You have cancelled your payment. No charge was made. Please feel free to checkout at anytime.');
                        },
                        onApprove : function (data, actions) {
                            actions.order.authorize().then(function (auth) {
                                if (auth.status === 'COMPLETED') {
                                    let authId = auth.purchase_units[0].payments.authorizations[0].id;
                                    invokeCaptureRequestForOrder(data.orderID, authId);
                                }
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