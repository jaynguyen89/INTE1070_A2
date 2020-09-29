<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /inte2/index.html");
    exit;
}

if (!$_SESSION['is_owner']) header("location: /inte2/home/home.php");

require_once 'cheque_signing.php';
require_once '../db_config.php';
global $link;

$expense_id = array_key_exists('id', $_GET) ? $_GET['id'] : null;
$user_id = $_SESSION['user_id'];

$query = 'SELECT * FROM expenses WHERE id = '.$expense_id;
$result = mysqli_query($link, $query);

$expense = mysqli_fetch_array($result, MYSQLI_ASSOC);

$sign_result = false;
if (array_key_exists('sign_expense', $_POST)) {

    if (!$expense['multisig']) {
        $private_key = file_get_contents($_FILES['signKey']['tmp_name']);
        $signature = sign_cheque_personally($expense['payee'], $expense['amount'], $private_key);
    }
    else $signature = sign_cheque_multisig($expense_id);

    if ($signature) {
        if (!$expense['multisig'])
            $query = 'INSERT INTO agreements (signed_id, expense_id, signature)
                      VALUES ('.$user_id.', '.$expense_id.', \''.$signature.'\');';
        else
            $query = 'INSERT INTO agreements (signed_id, expense_id, key_id, signature)
                      VALUES ('.$user_id.', '.$expense_id.', '.$signature['key_id'].', \''.$signature['sig'].'\');';

        if (mysqli_query($link, $query)) {
            $insert_id = mysqli_insert_id($link);

            if (!$expense['multisig']) {
                $sign_result = true;
                unlink($_FILES['signKey']['tmp_name']);
                unset($_POST['signKey']);
            }
            else {
                $query = 'UPDATE agreements SET signature = '.$signature['sig'].' WHERE expense_id = '.$expense_id.' AND id <> '.$insert_id;
                if (mysqli_query($link, $query)) $sign_result = true;
            }
        }
    }

    if (!$sign_result && array_key_exists('signKey', $_FILES) && $_FILES['signKey']['tmp_name']) unlink($_FILES['signKey']['tmp_name']);
}

$query = 'SELECT A.*, U.first_name, U.last_name, U.username FROM agreements A, users U WHERE expense_id = '.$expense_id;
$result = mysqli_query($link, $query);

date_default_timezone_set('Australia/Melbourne');

$signers = array();
$last_signed_on = strtotime("01-01-2000");
while ($signer = mysqli_fetch_assoc($result)) {
    array_push($signers, $signer);

    if (strtotime($signer['signed_on']) > $last_signed_on)
        $last_signed_on = strtotime($signer['signed_on']);
}

$is_signed = false;
$query = 'SELECT COUNT(*) as signing FROM agreements WHERE expense_id = '.$expense_id.' AND signed_id = '.$user_id;
$result = mysqli_fetch_array(mysqli_query($link, $query), MYSQLI_ASSOC);

$is_signed = $result['signing'] != 0;

$has_key = false;
$query = 'SELECT public_key from users WHERE id = '.$user_id;
$result = mysqli_fetch_array(mysqli_query($link, $query), MYSQLI_ASSOC);

$has_key = $result['public_key'] != null;

$query = !$expense['multisig'] ? 'SELECT signature FROM agreements WHERE expense_id = '.$expense_id
                               : 'SELECT signature, MAX(signed_on) FROM agreements WHERE expense_id = '.$expense_id;
$result = mysqli_query($link, $query);

$signatures = array();
while ($signature = mysqli_fetch_assoc($result)) {
    if (!$expense['multisig']) {
        $sig = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/inte2/assets/security/' . $signature['signature']);
        $sig = base64_encode($sig);

        array_push($signatures, substr($sig, rand(0, strlen($sig) - 62), 60));
        continue;
    }

    array_push($signatures, hash('sha256', $signature['signature']));
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
</head>
<body>
<div class="inte-header">
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container" style="margin-bottom: 80px">
    <?php if (array_key_exists('sign_expense', $_POST)) {
        if ($sign_result) { ?>
            <div class="alert alert-success" style="margin-top: 1rem;">You have successfully signed this cheque.</div>
        <?php } else { ?>
            <div class="alert alert-danger" style="margin-top: 1rem;">An error occurred while putting your signature on the cheque. Please try again.</div>
        <?php }
        unset($_POST['sign_expense']);
    } ?>

    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h4>View and manage expense: #<?php echo $expense_id; ?></h4>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <div class="bank-cheque">
            <div class="bank-logo"><i class="fas fa-university fa-2x"></i>&nbsp;<span>Australian Bank</span></div>
            <div class="cheque-no"><?php echo rand(1000, 9999); ?></div>
            <div class="cheque-date">Date:&nbsp;<span><?php echo date('d/m/Y', $last_signed_on); ?></span></div>
            <div class="cheque-amount"><b>$</b><span><?php echo $expense['amount'] ?></span></div>
            <div class="check-desc">
                <span>Pay to the<br/>order of:</span>
                <b><?php echo $expense['description']; ?></b>
            </div>
            <div class="in-words">
                <span><?php echo $expense['in_words']; ?></span>
                <b>Dollars</b>
            </div>
            <img src="../assets/logos/security-icon.png" class="sec-icon" />
            <div class="cheque-memo">
                <span>Memo:</span>
                <b>INTE1070 S3493188</b>
            </div>
            <div class="signatures">
                <ul>
                    <?php $colors = ['#4794ff', '#ff3526', '#d6a800'];
                        foreach ($signatures as $signature)
                            echo '<li style="color: '.$colors[array_search($signature, $signatures)].';">'.$signature.'</li>';
                    ?>
                </ul>
            </div>
            <img src="../assets/logos/cheque-notes.png" class="cheque-notes" />
        </div>

        <?php if (!$is_signed) { ?>
            <?php if ($has_key) { ?>
                <div class="col-sm-12">
                    <form method="post" action="expense_details.php?id=<?php echo $expense_id; ?>"
                          style="width: 50%; margin: auto;" enctype="multipart/form-data" novalidate>
                        <?php if (!$expense['multisig']) { ?>
                            <div class="form-group">
                                <label for="signKey">Your private key file:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-key" style="font-size: 25px; line-height: 30px"></i>
                                        </span>
                                    </div>
                                    <input name="signKey" id="signKey" required type="file" class="form-control">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-sm-12 text-center" style="margin-top: 20px;">
                            <input name="sign_expense" value="Sign this cheque" type="submit" class="btn btn-primary" />
                        </div>
                    </form>
                </div>
            <?php } else { ?>
                <div class="alert alert-warning text-center">
                    You need to sign this cheque, however, you have not had a public-private keys pair.<br/>
                    Please go back to your Admin page to generate keys.
                </div>
        <?php }} else
            echo '<div class="subtitle text-center" style="width: 50%; color: #008a22; margin: auto;">
                    '.($expense['is_approved'] ? 'This cheque has been approved by the bank.' : 'You have signed this cheque').'
                  </div>';
        ?>
    </div>

    <br /><br />
    <a href="admin.php" class="btn btn-secondary" style="margin-bottom: 2rem">
        <i class="fas fa-sign-out-alt"></i>&nbsp;Back
    </a>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
<script src="../assets/custom.js"></script>
</body>
</html>
