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

//Retrieve cart data
$query = 'SELECT I.product_id, I.quantity, P.product_name, P.unit_price
          FROM shopping_carts C, cart_items I, products P
          WHERE C.id = I.cart_id AND C.isPaid = false AND C.user_id = '.$user_id.' AND P.id = I.product_id;';
$data = mysqli_query($link, $query);

$cart_items = array();
$cart_total = 0;
while ($item = mysqli_fetch_assoc($data)) {
    array_push($cart_items, $item);
    $cart_total += ($item['unit_price'] * $item['quantity']);
}

//Retrieve all listings
$query = 'SELECT U.username, P.* FROM products P, users U
          WHERE P.user_id = U.id AND P.stock > 0 AND P.user_id <> '.$user_id;
$data = mysqli_query($link, $query);

$all_listings = array();
while ($listing = mysqli_fetch_assoc($data)) {
    foreach ($cart_items as $item)
        if ($listing['id'] == $item['product_id']) {
            $listing['stock'] -= $item['quantity'];
            break;
        }

    array_push($all_listings, $listing);
}

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
    <h2><a class="a-header" href="../home/home.php">INTE1070</a>: Secure Electronic Commerce</h2>
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
                        <?php if (count($cart_items) == 0) { ?>
                            <p style="margin: 1rem auto;" id="no-item">You have no item in your cart.</p>
                        <?php } else {?>
                            <script type="text/javascript">
                                document.ready(function () {
                                    let cart = localStorage.hasOwnProperty('SHOPPING_CART') ? JSON.parse(localStorage.getItem('SHOPPING_CART')) : [];
                                    <?php foreach ($cart_items as $item) { ?>
                                        cart.push({ item : <?php $item['product_id'] ?>, qty : <?php $item['quantity'] ?> });
                                    <?php } ?>
                                });
                            </script>
                            <?php foreach ($cart_items as $item) { ?>
                            <div class="col-sm-12" id="item<?php echo $item['product_id'] ?>">
                                <p style="display: none;" id="item-id"><?php echo $item['product_id'] ?></p>
                                <a class="text-danger" role="button" onclick="removeItemFromCart(<?php echo $item['product_id'] ?>)">
                                    <i class="fas fa-times"></i>
                                </a>&nbsp;
                                <b><?php echo $item['product_name']; ?></b>
                                <span class="float-right" id="item<?php echo $item['product_id'] ?>-subtotal">
                                    $<?php echo $item['unit_price'] ?> x <?php echo $item['quantity'] ?>
                                </span>
                            </div>
                        <?php }} ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <b>Total:</b>
                            <b class="float-right" id="total">$<?php echo $cart_total; ?></b>
                        </div>
                        <div class="col-sm-12" style="margin-bottom: 1rem">
                            <p class="subtitle"> We accept the following payment methods:</p>
                            <div class="row">
                                <div class="col-sm-3"><img class="img-fluid" src="../assets/logos/visa-card.png"></div>
                                <div class="col-sm-3"><img class="img-fluid" src="../assets/logos/paypal.png"></div>
                                <div class="col-sm-3"><img class="img-fluid" src="../assets/logos/google-pay.png"></div>
                                <div class="col-sm-3"><img class="img-fluid" src="../assets/logos/alipay.png"></div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <a class="btn btn-primary float-right <?php echo count($cart_items) == 0 ? 'disabled' : '' ?>"
                               role="button" id="checkout-button" onclick="gotoCart()">
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
                                        <span class="float-right" id="stock<?php echo $listing['id'] ?>">
                                            <?php echo $listing['stock'] == 0 ? 'Out of stock' : $listing['stock'].' left'; ?>
                                        </span>
                                    </div>
                                    <div class="col-sm-12">
                                        <b>Price:</b>
                                        <span class="float-right" id="price<?php echo $listing['id'] ?>">$<?php echo $listing['unit_price']; ?></span>
                                    </div>
                                </div>
                                <a class="btn btn-primary float-right <?php echo $listing['stock'] == 0 ? 'disabled' : ''; ?>"
                                   role="button" id="btn<?php echo $listing['id'] ?>" onclick="addItemToCart(<?php echo $listing['id'] ?>)">
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