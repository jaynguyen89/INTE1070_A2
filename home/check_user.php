<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to HOME Page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: home.php");
    exit;
}

// Include database configuration file
require_once '../db_config.php';
global $link;

// Define variables and initialize with empty values
$email = "";
$password = "";
$email_err = "";
$password_err = "";

// Store Form Data
//if(isset($GET["email"]) && isset($GET["password"])){
$email = array_key_exists('email', $_POST) ? $_POST["email"] : null;
$password = array_key_exists('password', $_POST) ? $_POST["password"] : null;

// Validate credentials
// Prepare a select statement
$sql = "SELECT * FROM users WHERE email='$email'";

$result = mysqli_query($link, $sql);//mysqli_query($link, $sql);
$data = mysqli_fetch_array($result,MYSQLI_ASSOC);
$count = mysqli_num_rows($result);

if ($count > 0) {
    // Password is correct, so start a new session
    if (!isset($_SESSION)) session_start();

    // Store data in session variables
    $_SESSION["loggedin"] = true;
    $_SESSION["first_name"] = $data['first_name'];
    $_SESSION["last_name"] = $data['last_name'];
    $_SESSION["email"] = $email;
    $_SESSION['user_id'] = $data['id'];
    $_SESSION['login_message'] = 'You have logged in successfully.';

    // Redirect user to welcome page
    header("location: home.php");
} else {
    // Display an error message if password is not valid
    $email_err = "No account found with that email.";
    $password_err = "The password you entered was not valid.";

}

// Close connection
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
    <div class="login-area">
        <h4>Welcome to E-commerce website</h4>

        <div class="row">
            <div class="login-row">
                <?php if ($email_err || $password_err) { ?>
                    <div class="error">
                        <p><?php echo $email_err;?></p>
                        <p><?php echo $password_err;?></p>
                    </div>
                <?php } ?>

                <form action="check_user.php" method="post" autocomplete="off">
                    <div class="col-sm-12" style="margin-bottom: 0.5rem;">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="fas fa-at" style="font-size: 1.5rem"></i>
                    </span>
                                </div>
                                <input name="email" value="<?php echo $email; ?>" type="text" class="form-control" placeholder="Email" autocomplete="none">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12" style="margin-bottom: 0.5rem;">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-key" style="font-size: 1.5rem"></i>
                        </span>
                                </div>
                                <input name="password" value="<?php echo $password; ?>" type="password" class="form-control" placeholder="Password" autocomplete="none">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>
</body>
</html>
