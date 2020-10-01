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
    $checkout_data = json_decode($data, true);

    $grand_total = $checkout_data['grand_total'];
    $order_number = $checkout_data['order_number'];
    $items = $checkout_data['items'];
}
else
    $error = true;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>INTE1070</title>

    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" />
    <link rel="stylesheet" href="../../assets/custom.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
    <script src="../../assets/custom.js"></script>

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
                <div class="center-text" style="width: 70%; margin: auto">
                    <div class="alert alert-info">
                        <h2>Pay with Alipay</h2>
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
                        <div class="btn btn-primary" id="pay-button"
                             onclick="<?php echo array_key_exists('payment_authorized', $_GET) ? 'processCharge();' : 'aliCheckout();'; ?>">
                            <i class="fab fa-alipay"></i>
                            <?php if (array_key_exists('payment_authorized', $_GET)) { ?>
                                Pay $<?php echo ($grand_total + 8.95); ?>
                            <?php } else echo 'Checkout Now'; ?>
                        </div>
                        <?php if (!array_key_exists('payment_authorized', $_GET)) { ?>
                            <p class="instruction">You will be redirected to Alipay to authorize the payment.</p>
                        <?php } else { ?>
                            <p class="instruction">You have authorized the payment. Now just click to proceed with your order.</p>
                        <?php } ?>
                    </div>
                </div>

                <script>
                    function aliCheckout() {
                        let orderItems = [
                            <?php foreach ($items as $item) {
                                echo '{
                                    name : \'' . $item['product_name'] . '\',
                                    unit_amount : {
                                        currency_code : \'AUD\',
                                        value : \'' . round($item['unit_price'] / 1.1, 2) . '\'
                                    },
                                    quantity : ' . $item['quantity'] . ',
                                    category : \'PHYSICAL_GOODS\'
                                }' . (array_search($item, $items) != count($items) - 1 ? ',' : '');
                            } ?>
                        ];

                        $('#pay-button').addClass('disabled');
                        let stripe = Stripe('pk_test_51HQDZND2FG7NncIEj68F5ie7Yc6VKR7y5r0aMkoaf3OD5CUIcqHBCYq3Wb2biu3D1jie5wjUKdsfwh3kdWG6flgJ00KdGXIjMp');

                        stripe.createSource({
                            type: 'alipay',
                            amount: <?php echo ($grand_total + 8.95) * 100; ?>,
                            currency: 'AUD',
                            redirect: {
                                return_url: 'http://localhost:81/shop/alipay/checkout.php?payment_authorized=true',
                            }
                        }).then(function (result) {
                            if (result.error) {
                                $('#pay-button').removeClass('disabled');
                                alert('Error: we were unable to process your Alipay payment. Please try again.');
                            }
                            else {
                                sessionStorage.setItem('ALI_SOURCE_ID', result.source.id);
                                let aliCheckoutPage = window.open(result.source.redirect.url, '_blank');
                                aliCheckoutPage.focus();
                            }
                        });
                    }

                    function processCharge() {
                        let form = document.createElement('form');
                        form.method = 'post';
                        form.action = 'capture.php';

                        let sourceInput = document.createElement('input');
                        sourceInput.name = 'aliToken';
                        sourceInput.value = sessionStorage.getItem('ALI_SOURCE_ID');
                        sourceInput.type = 'hidden';

                        form.appendChild(sourceInput);
                        document.body.appendChild(form);

                        sessionStorage.removeItem('ALI_SOURCE_ID');
                        form.submit();
                    }
                </script>
            <?php } ?>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>