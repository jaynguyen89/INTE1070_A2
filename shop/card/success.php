<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

require_once '../../db_config.php';
global $link;

$purchased_items = $_SESSION['purchased_items'];
$items = json_decode($purchased_items, true);

foreach ($items as $item) {
    $query = 'SELECT stock FROM products WHERE id = '.$item['product_id'];
    $result = mysqli_fetch_array(mysqli_query($link, $query));

    $query = 'UPDATE products SET stock = '.($result['stock'] - $item['quantity']).' WHERE id = '.$item['product_id'];
    mysqli_query($link, $query);
}

$data = array_key_exists('checkout_data', $_SESSION) ? $_SESSION['checkout_data'] : null;
$checkout_data = json_decode($data, true);

$grand_total = $checkout_data['grand_total'];
$order_number = $checkout_data['order_number'];

$query = 'INSERT INTO payments (payment_gross, currency_code, payment_status)
          VALUES ('.$grand_total.', \'AUD\', \'COMPLETED\');';
mysqli_query($link, $query);

$payment_id = mysqli_insert_id($link);
$query = 'UPDATE shopping_carts SET payment_id = '.$payment_id.', isPaid = true
          WHERE user_id = '.$_SESSION['user_id'].' AND isPaid = false AND order_number = \''.$order_number.'\';';
mysqli_query($link, $query);
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
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container" style="margin-bottom: 80px">
    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h5>Congratulation on your order!</h5>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <div class="alert alert-success" role="alert">
            <p>Your order number is <b><?php echo $_SESSION['order'] ?></b></p>
            <p>Your transaction has went through and we are processing your order.</p>
            <p>Please allow 1-2 business days handling time before we ship the items to you.</p>
            <p>Should you have any question, please contact our support for more information.</p>
            <p>Thank you!</p>

            <br/><br/>
            <p>Click <a href="../browsing.php">here</a> continue shopping.</p>
            <p>Click <a href="../../home/home.php">here</a> to go back to your homepage.</p>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>