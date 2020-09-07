<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

require_once '../db_config.php';
global $link;

$login_message = array_key_exists('login_message', $_SESSION) ? $_SESSION['login_message'] : null;
unset($_SESSION['login_message']);

$searched_id = array_key_exists('product_id', $_POST) ? $_POST['product_id'] : null;
$search_error = '';

$data = array();
$searching = false;

$listing_error = false;
$listing_message = '';

global $query;
$query = '';

if ($searched_id && $_POST['submit'] == 'view_details') {
    $searching = true;
    $query = "SELECT P.*, U.first_name, U.last_name
              FROM products P, users U
              WHERE P.user_id = U.id AND P.id = ".$searched_id;

    $data = getProducts($query);
    if (!is_array($data)) {
        $search_error = $data;
        $data = null;
    }
}
else if ($searched_id && $_POST['submit'] == 'save-export') {
    $searching = false;
    $query = "SELECT P.*, U.first_name, U.last_name
              FROM products P, users U
              WHERE P.user_id = U.id AND P.id = ".$searched_id;

    $data = getProducts($query);
    $json_data = json_encode($data);
    $file_name = $_SESSION['first_name'].'_'.$_SESSION['last_name'].'_'.time().'.json';
    $file_to_download = fopen('./assets/'.$file_name, 'w');

    if (fwrite($file_to_download, $json_data)) {
        $file_url = './assets/'.$file_name;

        header('Content-Type: application/json');
        header('Content-Transfer-Encoding: Binary');
        header("Content-disposition: attachment; filename=\"".basename($file_url)."\"");

        flush();
        readfile($file_url);
        exit(0);
    }
    else $search_error = 'An error occurred while extracting data.';
}
else if (array_key_exists('submit', $_POST) && $_POST['submit'] == 'create-listing') {
    $searching = false;

    $promotion = $_POST['promotion'] == 1;
    $listing_quota = $_POST['listing_quota'];
    $account_type = $_POST['account_type'];

    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $unit_price = $_POST['unit_price'];

    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

    if ($listing_quota > 0) {
        $query = $promotion
            ? "INSERT INTO products (user_id, product_name, unit_price, stock, description)
                  VALUES (".$_SESSION['user_id'].", '".$item_name."', '".$unit_price."', '".$stock."', '".$description."')"
            : "INSERT INTO products (user_id, product_name, unit_price, stock, description, expire_on)
                  VALUES (".$_SESSION['user_id'].", '".$item_name."', '".$unit_price."', '".$stock."', '".$description."', '".$expiry."');";
        try {
            if (mysqli_query($link, $query)) {
                $listing_error = false;
                $listing_message = 'Your new listing is now published. And a listing fee of '.($account_type == 1 ? '$10' : '$1').' has been added to your account.';
            }
        } catch (Exception $e) {
            $listing_error = true;
            $listing_message = $e->getMessage();
        }
    }
    else {
        $listing_error = true;
        $listing_message = 'You have run out of listing quota for this month.';
    }

    $result = getUserData();
    if (!is_array($result)) $search_error = $result;
    else $data = $result;
}
else {
    $searching = false;
    $result = getUserData();
    if (!is_array($result)) $search_error = $result;
    else $data = $result;
}

function getProducts($query) {
    global $link;
    $data = array();
    try {
        $result = mysqli_query($link, $query);
        while ($row = mysqli_fetch_assoc($result))
            array_push($data, $row);
    } catch (Exception $e) {
        return $e->getMessage();
    }

    return $data;
}

function getUserData() {
    global $query;
    $user_id = $_SESSION['user_id'];
    $query = "SELECT P.*, U.first_name, U.last_name 
              FROM products P, users U
              WHERE P.user_id = U.id AND P.user_id= ".$user_id;

    return getProducts($query);
}

mysqli_close($link);
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
</head>
<body>
<div class="inte-header">
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container">
    <?php if ($login_message) { ?>
        <div class="alert alert-success" style="margin-top: 1rem;"><?php echo $login_message; ?></div>
    <?php } ?>

    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h4>Welcome to our E-Commerce Site.</h4>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />



    <br />
    <div>Here are your listings:</div>

    <div class="row" style="margin: 2.5rem auto;">
        <div class="row">
            <form method="post" action="home.php" class="form-group row">
                <label>View your listing details or Export listing:</label>
                <div class="col-sm-8" style="margin-bottom: 7px;">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-search" style="font-size: 25px"></i>
                                </span>
                            </div>
                            <input name="product_id" type="text" class="form-control" placeholder="Enter a Product ID to view details">
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <button id="search-product" type="submit" name="submit" value="view_details" class="btn btn-primary float-right">
                        <i class="fas fa-eye"></i> View
                    </button>
                </div>
                <div class="col-sm-2">
                    <button id="search-product" type="submit" name="submit" value="save-export" class="btn btn-primary">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </form>
        </div>


        <?php if ($search_error) { ?>
            <div class="error">
                <p><?php echo $search_error; ?></p>
            </div>
        <?php } ?>

        <?php if (!$search_error && $searched_id) { ?>
            <div class="product-details">
                <p>Product ID: <b><?php echo $data[0]['id']; ?></b></p>
                <p>Seller: <b><?php echo $data[0]['first_name'].' '.$data[0]['last_name']; ?></b></p>
                <p>Product Name: <b><?php echo $data[0]['product_name']; ?></b></p>
                <p>Unit Price: <b><?php echo $data[0]['unit_price']; ?></b></p>
                <p>Stock: <b><?php echo $data[0]['stock']; ?></b></p>
                <p>Description: <b style="line-height: 1.5rem;"><?php echo $data[0]['description']; ?></b></p>

                <button class="btn btn-sm btn-primary float-right"
                        onClick="closeProductDetails()">
                    Close
                </button>
            </div>

            <script type="application/javascript">
                const closeProductDetails = function() { document.getElementById('search-product').click(); }
            </script>
        <?php } ?>


        <?php if (!$searching) { ?>
            <table class="table" style="margin-top: 3rem;">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">Listing ID</th>
                    <th scope="col">Seller</th>
                    <th scope="col">Item Name</th>
                    <th scope="col">Unit Price</th>
                    <th scope="col">Stock</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $product) { ?>
                    <tr>
                        <th scope="row"><?php echo $product['id']; ?></th>
                        <td><?php echo $product['first_name']." ".$product['last_name']; ?></td>
                        <td><?php echo $product['product_name']; ?></td>
                        <td><?php echo $product['unit_price']; ?></td>
                        <td><?php echo $product['stock']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>

    <br />
    <div>List more items:</div>

    <div class="error" style="line-height: 1.5rem">
        Attention! You have outstanding debt in your account so that you will be unable to create more listings.<br />
        Please go to your account and make payments to clear your debt first.
    </div>

    <?php if ($listing_message != '') { ?>
        <div class="<?php echo $listing_error ? 'error' : 'success' ?>">
            <?php echo $listing_message; ?>
        </div>
    <?php } ?>

    <div class="row" style="margin-bottom: 5rem">
        <form method="post" action="home.php" class="form-group row">
            <!-- Listing never expires -->
            <input name="promotion" type="number" value="0" hidden />
            <!-- Listings per month -->
            <input name="listing_quota" type="number" value="0" hidden />
            <!-- For discounted listing -->
            <input name="account_type" type="number" value="1" hidden />

            <div class="col-sm-4" style="margin-bottom: 7px;">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-pencil-alt" style="font-size: 25px"></i>
                            </span>
                        </div>
                        <input name="item_name" type="text" class="form-control" placeholder="Item Name" disabled>
                    </div>
                </div>
            </div>
            <div class="col-sm-8" style="margin-bottom: 7px;">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-pencil-alt" style="font-size: 25px"></i>
                            </span>
                        </div>
                        <input name="description" type="text" class="form-control" placeholder="Description" disabled>
                    </div>
                </div>
            </div>
            <div class="col-sm-6" style="margin-bottom: 7px;">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-pencil-alt" style="font-size: 25px"></i>
                            </span>
                        </div>
                        <input name="stock" type="number" class="form-control" placeholder="Stock" disabled>
                    </div>
                </div>
            </div>
            <div class="col-sm-6" style="margin-bottom: 7px;">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-pencil-alt" style="font-size: 25px"></i>
                            </span>
                        </div>
                        <input name="unit_price" type="number" class="form-control" placeholder="Unit Price" disabled>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <button id="create-listing" type="submit" name="submit" value="create-listing" class="btn btn-primary disabled">
                    <i class="fas fa-plus-circle"></i> Create
                </button>
            </div>
        </form>
    </div>

    <a href="logout.php" class="btn btn-danger" style="margin-bottom: 2rem">
        <i class="fas fa-sign-out-alt"></i> Sign Out
    </a>
</div>

<!--<div class="footer">S3493188 Le Kim Phuc Nguyen</div>-->
</body>
</html>