<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

$failure = array_key_exists('failure', $_SESSION) ? $_SESSION['failure'] : null;
$data = json_decode($_SESSION['checkout_data'], true);
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
</head>
<body>
<div class="inte-header">
    <h2><a class="a-header" href="../../home/home.php">INTE1070</a>: Secure Electronic Commerce</h2>
</div>

<div class="container" style="margin-bottom: 80px">
    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h5>Something went wrong...</h5>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <div class="alert alert-warning" role="alert">
            <h4>What happened...</h4>
            <?php if ($failure == null) { ?>
                <p>An error occurred while we were processing your payment for the order.</p>
                <p>No charge has made on your Paypal account. Please go back to <a href="../cart_review.php">your cart</a> and try again.</p>
            <?php } else switch ($failure) {
                case 'verify_authorization': ?>
                    <p>An error occurred while we verify your payment for the order.</p>
                    <p>Money have not been charged from your Paypal account. Please contact support and quote the following information to get help:</p>
                    <?php break;
                case 'insert_payments': ?>
                    <p>An error occurred while we update your payment details into our system.</p>
                    <p>Money have not been charged from your Paypal account. Please contact support and quote the following information to get help:</p>
                    <?php break;
                case 'payment_capture': ?>
                    <p>An error occurred while we attempt to charge your Paypal account for the payment.</p>
                    <p>Please do not go back to previous page. We're sorry for the inconvenience. Please contact support and quote the following information to get help:</p>
                    <?php break;
                case 'update_payments': ?>
                    <p>An error occurred while we were update your payment details into our system.</p>
                    <p>Money have been charged from your Paypal account and we know that. Please contact support and quote the following information to get help:</p>
                    <?php break;
                case 'charge_failed': ?>
                    <p>An error occurred while we were processing your payment.</p>
                    <p>Money have not been charged from your card. Please contact support and quote the following information to get help:</p>
                    <?php break;
                default: //update_shopping_carts ?>
                    <p>An error occurred while we were update your payment details into our system.</p>
                    <p>Money have been charged from your Paypal account and we know that. Please contact support and quote the following information to get help:</p>
                    <?php break;
                } ?>

            <br/>
            <p>Error name: <b><?php echo $failure ? $failure : 'PAYPAL_DATA_MISMATCHED'; ?></b></p>
            <p>Order Number: <b><?php echo $failure ? $_SESSION['order'] : $data['order_number']; ?></b></p>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>