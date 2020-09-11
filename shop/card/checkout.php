<?php

session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

require '../../vendor/autoload.php';

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

            <?php } ?>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>