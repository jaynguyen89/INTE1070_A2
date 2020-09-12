<?php

session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

$data = array_key_exists('checkout_data', $_SESSION) ? $_SESSION['checkout_data'] : null;

$grand_total = 0;
$order_number = '';
$items = [];

$error = false;
if ($data != null) {
    $payment_method = 'card';

    $checkout_data = json_decode($data, true);

    $grand_total = $checkout_data['grand_total'];
    $order_number = $checkout_data['order_number'];
    $items = $checkout_data['items'];
}
else $error = true;
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

    <script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
</head>
<body>
<div class="inte-header">
    <h2><a class="a-header" href="../../home/home.php">INTE1070</a>: Secure Electronic Commerce</h2>
</div>

<div class="container" style="margin-bottom: 80px">
    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h5><?php echo !$error ? 'Complete your purchase' : 'It seems like you should not be here...'; ?></h5>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <div class="col-sm-12 text-center">
            <?php if ($error) { ?>
                <p>We find no item in your cart nor any payment information.</p>
                <p>If you have come here somehow by mistake, please click <a href="../../home/home.php">here</a> to go back.</p>
            <?php } else { ?>
                <div style="width: 60%; margin: auto;">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="alert alert-info">
                                <h2>Pay with Bank Cards</h2>
                                <table class="table table-borderless" style="margin-top: 14px">
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
                                        <td>Card Surcharge:</td>
                                        <td><b>$<?php echo round($grand_total * 0.0125, 2); ?> (1.25% of Order Total)</b></td>
                                    </tr>
                                    <tr>
                                        <td>Total Payable:</td>
                                        <td><b>$<?php echo round($grand_total*1.0125 + 8.95, 2); ?> (including $<?php echo round(round($grand_total / 1.1, 2) * 0.1, 2); ?> GST)</b></td>
                                    </tr>
                                    </tbody>
                                </table>

                                <div class="btn btn-primary" id="pay-button" onclick="submitCard()">
                                    <i class="fas fa-money-check-alt"></i>&nbsp;
                                    Pay $<?php echo round($grand_total*1.0125 + 8.95, 2); ?>
                                </div>
                            </div>
                        </div>

                        <script type="text/javascript">
                            function submitCard() {
                                $('#pay-button').addClass('disabled');
                                let stripe = Stripe('pk_test_51HQDZND2FG7NncIEj68F5ie7Yc6VKR7y5r0aMkoaf3OD5CUIcqHBCYq3Wb2biu3D1jie5wjUKdsfwh3kdWG6flgJ00KdGXIjMp');

                                $.ajax({
                                    url:'http://localhost:81/inte2/shop/card/capture.php',
                                    type:'POST',
                                    success: function(response) {
                                        let session = JSON.parse(response);
                                        return stripe.redirectToCheckout({ sessionId : session.id });
                                    },
                                    error: function () {
                                        alert('Error occurred: We were unable to verify your card. Please try again.');
                                        $('#pay-button').removeClass('disabled');
                                    }
                                });
                            }
                        </script>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>