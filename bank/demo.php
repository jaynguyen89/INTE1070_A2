<?php
require_once '../admin/cheque_signing.php';
require_once '../db_config.php';
global $link;

$cheques = array();
$query = 'SELECT E.*, U.username, U.first_name, U.last_name
          FROM expenses E, agreements A, users U
          WHERE E.id = A.expense_id AND E.user_id = U.id AND E.is_approved = false
          GROUP BY E.id HAVING COUNT(*) = (
            SELECT COUNT(*) FROM users U WHERE U.is_owner = true
          );';

$result = mysqli_query($link, $query);
while ($cheque = mysqli_fetch_assoc($result))
    array_push($cheques, $cheque);

if (array_key_exists('id', $_GET)) {
    $query = 'SELECT * FROM expenses WHERE id = '.$_GET['id'];

    $result = mysqli_query($link, $query);
    $selected_cheque = mysqli_fetch_array($result, MYSQLI_ASSOC);

    $query = 'SELECT signature, signed_on FROM agreements WHERE expense_id = '.$_GET['id'];
    $result = mysqli_query($link, $query);

    $last_signed_on = strtotime("01-01-2000");
    $signatures = array();
    while ($signature = mysqli_fetch_assoc($result)) {
        $sig = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/inte2/assets/security/'.$signature['signature']);
        $sig = base64_encode($sig);

        array_push($signatures, substr($sig, rand(0, strlen($sig) - 62), 60));

        if (strtotime($signature['signed_on']) > $last_signed_on)
            $last_signed_on = strtotime($signature['signed_on']);
    }
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
    <h2 style="margin-top: 2rem;">Bank Cheque Approval Demo</h2>
    <h4>The below table displays the cheques that have been signed by all owners.</h4>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
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
                <?php foreach ($cheques as $cheque) { ?>
                    <tr>
                        <td><?php echo $cheque['id']; ?></td>
                        <td><?php echo $cheque['first_name'].' '.$cheque['last_name'].' ('.$cheque['username'].')'; ?></td>
                        <td><?php echo $cheque['payee']; ?></td>
                        <td><?php echo $cheque['amount']; ?></td>
                        <td>
                            <a class="btn btn-primary" href="demo.php?id=<?php echo $cheque['id']; ?>">
                                <i class="fas fa-folder-open"></i>&nbsp;View
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if (array_key_exists('id', $_GET)) { ?>
            <div class="bank-cheque">
                <div class="bank-logo"><i class="fas fa-university fa-2x"></i>&nbsp;<span>Australian Bank</span></div>
                <div class="cheque-no"><?php echo rand(1000, 9999); ?></div>
                <div class="cheque-date">Date:&nbsp;<span><?php echo date('d/m/Y', $last_signed_on); ?></span></div>
                <div class="cheque-amount"><b>$</b><span><?php echo $selected_cheque['amount'] ?></span></div>
                <div class="check-desc">
                    <span>Pay to the<br/>order of:</span>
                    <b><?php echo $selected_cheque['description']; ?></b>
                </div>
                <div class="in-words">
                    <span><?php echo $selected_cheque['in_words']; ?></span>
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

            <div class="col-sm-12" style="width: 70%; margin: auto;">
                <div class="alert text-center" id="cheque-alert"></div>

                <div class="btn btn-primary" id="verify-btn" onclick="verifyCheque(<?php echo $selected_cheque['id']; ?>);">Verify</div>
                <div class="btn btn-primary disabled" id="approve-btn" onclick="approveCheque(<?php echo $selected_cheque['id']; ?>);">Approve</div>

                <a href="demo.php" class="btn btn-warning float-right">Close</a>
            </div>
        <?php } ?>


        <br/><br/><br/>
        <div class="btn btn-primary" onclick="run();">Test</div>
        <script type="text/javascript">
            function run() {
                $.ajax({
                    url: "http://localhost:81/inte2/rsa/keygen.php",
                    method: 'GET',
                    success: function(response) {
                        console.log(response);
                    }
                });
            }
        </script>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
<script src="../assets/custom.js"></script>
</body>
</html>
