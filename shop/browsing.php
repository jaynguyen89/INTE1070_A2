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

$query = 'SELECT U.username, P.* FROM products P, users U
          WHERE P.user_id = U.id AND P.stock > 0 AND P.user_id <> '.$user_id;

$data = mysqli_query($link, $query);

$all_listings = [];
while ($listing = mysqli_fetch_assoc($data))
    array_push($all_listings, $listing);

function to_friendly_time($any) {
    $days = floor($any);
    $hours = floor(($any - $days)*24);
    return $days.' days '.$hours.' hours';
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
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container" style="margin-bottom: 80px">
    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h5>Find the items you you want to buy.</h5>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <div class="col-lg-4 col-sm-12" style="margin-top: 1rem">
            <div class="card">
                <h5 class="card-header"><i class="fas fa-cart-plus"></i>&nbsp;&nbsp;Your cart</h5>
                <div class="card-body">
                    <div class="row" id="cart-items">
                        <p style="margin: 1rem auto;" id="no-item">You have no item in your cart.</p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <b>Total:</b>
                            <b class="float-right" id="total">$0</b>
                        </div>
                        <div class="col-sm-12" style="margin-bottom: 1rem">
                            <p class="subtitle"> We accept the following payment methods:</p>
                            <div class="row">
                                <div class="col-sm-3"><img class="img-fluid" src="../assets/logos/visa-card.png"></div>
                                <div class="col-sm-3"><img class="img-fluid" src="../assets/logos/paypal.png"></div>
                                <div class="col-sm-3"><img class="img-fluid" src="../assets/logos/google-pay.png"></div>
                                <div class="col-sm-3"><img class="img-fluid" src="../assets/logos/apply-pay.png"></div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <a class="btn btn-primary float-right disabled" role="button" id="checkout-button"
                                onclick="gotoCart()">
                                <i class="fas fa-shopping-basket"></i>&nbsp;View Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-sm-12" style="margin-top: 1rem">
            <div class="row">
                <?php foreach ($all_listings as $listing) { ?>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card" style="width: 18rem;">
                            <img class="card-img-top" src="../assets/images/<?php echo $listing['photo']; ?>" alt="<?php echo $listing['product_name'] ?>">
                            <div class="card-body">
                                <h4 class="card-title" id="product<?php echo $listing['id'] ?>"><?php echo $listing['product_name']; ?></h4>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <b>Sold by:</b>
                                        <span class="float-right"><?php echo $listing['username']; ?></span>
                                    </div>
                                    <div class="col-sm-12">
                                        <b>Time left:</b>
                                        <span class="float-right"><?php echo to_friendly_time((strtotime($listing['expire_on']) - time())/86400); ?></span>
                                    </div>
                                    <div class="col-sm-12">
                                        <b>Stock:</b>
                                        <span class="float-right" id="stock<?php echo $listing['id'] ?>"><?php echo $listing['stock']; ?> left</span>
                                    </div>
                                    <div class="col-sm-12">
                                        <b>Price:</b>
                                        <span class="float-right" id="price<?php echo $listing['id'] ?>">$<?php echo $listing['unit_price']; ?></span>
                                    </div>
                                </div>
                                <a class="btn btn-primary float-right" role="button" id="btn<?php echo $listing['id'] ?>"
                                   onclick="addItemToCart(<?php echo $listing['id'] ?>)">
                                    Add to cart&nbsp;<i class="fas fa-plus-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>