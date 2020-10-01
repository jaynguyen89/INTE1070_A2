<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page (index.html)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /index.html");
    exit;
}

if (!$_SESSION['is_owner']) header("location: /home/home.php");

require_once 'cheque_signing.php';
require_once '../db_config.php';
global $link;

$login_message = array_key_exists('login_message', $_SESSION) ? $_SESSION['login_message'] : null;
unset($_SESSION['login_message']);

$user_id = $_SESSION['user_id'];
$create_result = false;

if (array_key_exists('create-expense', $_POST)) {
    $query = 'INSERT INTO expenses (user_id, payee, amount, in_words, description)
              VALUES ('.$user_id.', \''.$_POST['payee'].'\', '.$_POST['amount'].', \''.$_POST['in-words'].'\', \''.$_POST['description'].'\');';

    if (mysqli_query($link, $query)) {
        $expense_id = mysqli_insert_id($link);
        $multisig = array_key_exists('multisig', $_POST) && $_POST['multisig'] == 'on';

        $signature = $multisig ? sign_cheque_multisig($expense_id) : sign_cheque_separately();
        $create_result = $signature != null;

        if ($create_result) {
            if ($multisig)
                $query = 'INSERT INTO agreements (signed_id, expense_id, key_id, signature)
                          VALUES ('.$_SESSION['user_id'].', '.$expense_id.', '.$signature['key_id'].', \''.$signature['sig'].'\');';
            else
                $query = 'INSERT INTO agreements (signed_id, expense_id, signature)
                          VALUES ('.$_SESSION['user_id'].', '.$expense_id.', \''.$signature.'\');';

            if (mysqli_query($link, $query)) {
                if (array_key_exists('signKey', $_FILES) && $_FILES['signKey']['tmp_name']) {
                    unlink($_FILES['signKey']['tmp_name']);
                    unset($_FILES['signKey']);
                }
            } else {
                $query = 'DELETE FROM expenses WHERE id = '.$expense_id;
                mysqli_query($link, $query);
            }
        }
    }
    else unlink($_FILES['signKey']['tmp_name']);
}

$query = 'SELECT COUNT(*) as owner_count FROM users WHERE is_owner = true';
$result = mysqli_fetch_array(mysqli_query($link, $query), MYSQLI_ASSOC);

global $owner_count;
$owner_count = $result['owner_count'];

$query = 'SELECT E.*, U.username, U.first_name, U.last_name FROM expenses E, users U WHERE E.user_id = U.id';
$result = mysqli_query($link, $query);

$unsigned_expenses = array();
global $pending_expenses; $pending_expenses = array();
global $in_review_expenses; $in_review_expenses = array();
global $completed_expenses; $completed_expenses = array();

while ($expense = mysqli_fetch_assoc($result)) {
    if ($expense['user_id'] == $user_id) filter_expenses($expense);
    else {
        $query = 'SELECT * FROM expenses E, agreements A WHERE E.id = A.expense_id AND E.id = '.$expense['id'].' AND A.signed_id ='.$user_id;
        $res = mysqli_query($link, $query);

        if (mysqli_num_rows($res) == 0) array_push($unsigned_expenses, $expense);
        else filter_expenses($expense);
    }
}

function filter_expenses($expense) {
    global $owner_count;
    global $link;
    global $pending_expenses;
    global $completed_expenses;
    global $in_review_expenses;

    $query = 'SELECT COUNT(*) as sign_count FROM agreements WHERE expense_id = '.$expense['id'];
    $result = mysqli_fetch_array(mysqli_query($link, $query), MYSQLI_ASSOC);
    $sign_count = $result['sign_count'];

    if ($sign_count < $owner_count) array_push($pending_expenses, $expense);
    else if ($expense['is_approved']) array_push($completed_expenses, $expense);
    else array_push($in_review_expenses, $expense);
}

function sign_cheque_separately() {
    $private_key = array_key_exists('signKey', $_FILES) ? file_get_contents($_FILES['signKey']['tmp_name']) : null;
    return $private_key ? sign_cheque_personally($_POST['payee'], $_POST['amount'], $private_key) : null;
}

function display_expense($expense) {
    echo '
        <tr>
            <th scope="row">'.$expense['id'].'</th>
            <td>'.$expense['first_name'].' '.$expense['last_name'].' ('.$expense['username'].')'.'</td>
            <td>'.$expense['payee'].'</td>
            <td>$'.$expense['amount'].'</td>
            <td>
                <a class="btn btn-primary" href="expense_details.php?id='.$expense['id'].'">
                    <i class="fas fa-folder-open"></i>&nbsp;View
                </a>
            </td>
        </tr>';
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
    <?php if ($login_message) { ?>
        <div class="alert alert-success" style="margin-top: 1rem;"><?php echo $login_message; ?></div>
    <?php } ?>

    <?php if (array_key_exists('create-expense', $_POST)) {
    if ($create_result) { ?>
        <div class="alert alert-success" style="margin-top: 1rem;">The new expense has been created and your signature is applied successfully.</div>
    <?php } else { ?>
        <div class="alert alert-danger" style="margin-top: 1rem;">An error occurred while creating new expense. Please try again.</div>
    <?php }
        unset($_POST['create-expense']);
    } ?>

    <h2 style="margin-top: 2rem;">Hi, <?php echo $_SESSION["first_name"]." ".$_SESSION["last_name"]; ?></h2>
    <h4>Welcome to your E-Commerce Admin.</h4>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <table class="table table-bordered">
        <thead class="thead-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Created By</th>
                <th scope="col">Payee</th>
                <th scope="col">Amount</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
        <tr><td colspan="5" class="text-center bg-info">The expenses that you have not signed.</td></tr>
        <?php if (empty($unsigned_expenses)) { ?>
            <tr><td colspan="5"><div class="text-center">No expense of this type.</div></td></tr>
        <?php } else foreach ($unsigned_expenses as $expense) display_expense($expense); ?>

        <tr><td colspan="5" class="text-center bg-info">The expenses that are still await for signatures.</td></tr>
        <?php if (empty($pending_expenses)) { ?>
            <tr><td colspan="5"><div class="text-center">No expense of this type.</div></td></tr>
        <?php } else foreach ($pending_expenses as $expense) display_expense($expense); ?>

        <tr><td colspan="5" class="text-center bg-info">The expenses that all owners have signed and pending review.</td></tr>
        <?php if (empty($in_review_expenses)) { ?>
            <tr><td colspan="5"><div class="text-center">No expense of this type.</div></td></tr>
        <?php } else foreach ($in_review_expenses as $expense) display_expense($expense); ?>

        <tr><td colspan="5" class="text-center bg-info">The expenses that have been reviewed and completed.</td></tr>
        <?php if (empty($completed_expenses)) { ?>
            <tr><td colspan="5"><div class="text-center">No expense of this type.</div></td></tr>
        <?php } else foreach ($completed_expenses as $expense) display_expense($expense); ?>
        </tbody>
    </table>

    <br />
    <a class="btn btn-primary" onclick="showModal();" style="margin-bottom: 2rem" data-toggle="modal" data-target="#createExpense">
        <i class="fas fa-plus-circle"></i>&nbsp;Create new expense
    </a>

    <script type="text/javascript">
        function showModal() { $('#createExpense').modal(); }
    </script>

    <div class="modal fade" id="createExpense" tabindex="-1" role="dialog" aria-labelledby="createExpense" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create new expense</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">
                            <i class="fas fa-times"></i>
                        </span>
                    </button>
                </div>
                <form method="post" action="admin.php" class="form-group row" enctype="multipart/form-data" novalidate>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 col-sm-12" style="margin-bottom: 7px;">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-university" style="font-size: 25px"></i>
                                            </span>
                                        </div>
                                        <input name="payee" type="text" class="form-control" placeholder="Payee">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12" style="margin-bottom: 7px;">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-money-check-alt" style="font-size: 25px"></i>
                                            </span>
                                        </div>
                                        <input name="amount" required type="number" min="0.01" step="0.01" class="form-control" placeholder="Amount to transfer">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12" style="margin-bottom: 7px;">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-quote-left" style="font-size: 25px"></i>
                                            </span>
                                        </div>
                                        <input name="in-words" required type="text" class="form-control" placeholder="Amount in words">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12" style="margin-bottom: 7px;">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-quote-left" style="font-size: 25px"></i>
                                            </span>
                                        </div>
                                        <input name="description" required type="text" class="form-control" placeholder="Description">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 text-center" style="margin-bottom: 7px;">
                                <label class="form-check checkbox-inline">
                                    <input id="multisig" name="multisig" type="checkbox" class="form-check-input" onclick="handleMultisigCheckbox()">
                                    &nbsp;<span id="multisig-label">Sign cheque with separate signature</span>
                                </label>
                            </div>
                            <div id="signature-selector" class="col-sm-12" style="margin-bottom: 7px;">
                                <div class="form-group">
                                    <label for="signKey">Your private key file:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-key" style="font-size: 25px; line-height: 30px"></i>
                                            </span>
                                        </div>
                                        <input name="signKey" id="signKey" required type="file" class="form-control">
                                        <p class="instruction">When creating a new expense, you are the first people to sign it.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button name="create-expense" value="Save Cheque" type="submit" class="btn btn-primary">Save Cheque</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <br/><br/>
    <h4><?php echo array_key_exists('public_key', $_SESSION) && $_SESSION['public_key'] ? 'Change' : 'Generate'; ?> your public-private keys pair</h4>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <div class="col-sm-12" id="crypto-keys-col">
            <?php if (array_key_exists('public_key', $_SESSION) && $_SESSION['public_key']) { ?>
                <p class="subtitle text-success">You have generated your public-private keys pair before. Click the below button if you wish to change it.</p>
            <?php } ?>
            <div class="btn btn-primary" style="width: 20%" id="generate-keys-btn"
                 onclick="generateCryptoKeys(<?php echo array_key_exists('public_key', $_SESSION) && $_SESSION['public_key'] ? 'true' : 'false'; ?>)">
                <?php echo array_key_exists('public_key', $_SESSION) && $_SESSION['public_key'] ? 'Change' : 'Generate'; ?>
            </div>
        </div>
    </div>

    <br /><br />
    <a onclick="logout(true)" class="btn btn-danger" style="margin-bottom: 2rem">
        <i class="fas fa-sign-out-alt"></i>&nbsp;Sign Out
    </a>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
<script src="../assets/custom.js"></script>
</body>
</html>