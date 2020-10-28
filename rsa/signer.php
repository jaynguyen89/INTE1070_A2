<?php

require_once '../db_config.php';
global $link;

$cheque_id = array_key_exists('chequeId', $_GET) ? $_GET['chequeId'] : 0;
if ($cheque_id) echo verify_cheque($cheque_id);

function sign_cheque($plain, $key, $n) { //sign cheque by combined signature method
    return bcpowmod($plain, $key, $n);
}

//verify cheque for combined signature
function verify_cheque($cheque_id) {
    global $link;
    $query = 'SELECT DISTINCT(A.signature), A.key_id, E.amount FROM agreements A, expenses E
              WHERE expense_id = '.$cheque_id.' AND A.expense_id = E.id;';
    $result = mysqli_query($link, $query); //real cheque data to get the message and the signature

    if ($result) {
        $data = mysqli_fetch_array($result, MYSQLI_ASSOC);

        if ($data) {
            $signature = intval($data['signature']); //signature to verify
            $amount = $data['amount']; //message to compare

            $query = 'SELECT public_keys FROM multisig_keys WHERE id = '.$data['key_id']; //get keys from TTP
            $result = mysqli_query($link, $query);

            if ($result) {
                $data = mysqli_fetch_array($result, MYSQLI_ASSOC);

                if ($data) {
                    $public_keys = json_decode($data['public_keys'], true);

                    $plain = bcpowmod($signature, $public_keys['key'], $public_keys['n']); //verification
                    return $plain == $amount * 100 ? 'success' : 'failed';
                }
            }
        }
    }

    return 'error';
}

// PHP program to compute
// factorial of big numbers

// Maximum number of
// digits in output

// This function multiplies
// x with the number represented
// by res[]. res_size is size of
// res[] or number of digits in
// the number represented by res[].
// This function uses simple school
// mathematics for multiplication.
// This function may value of
// res_size and returns the new
// value of res_size
function multiply($x, $res)
{

// Initialize carry
    $carry = 0;
    $res_size = count($res);

// One by one multiply
// n with individual
// digits of res[]
    for ($i = 0; $i < $res_size; $i++) {
        $prod = $res[$i] * $x + $carry;

        // Store last digit of
        // 'prod' in res[]
        $res[$i] = $prod % 10;

        // Put rest in carry
        $carry = (int)($prod / 10);
    }

// Put carry in res and
// increase result size
    while ($carry) {
        if($carry % 10) $res[$res_size++] = $carry % 10;
        $carry = (int)($carry / 10);
    }

    return $res;
}

// This function finds
// power of a number x
function power($x, $n)
{
    //printing value "1" for power = 0
    if($n == 0 ) return 1;

    $res_size = 0;
    $res = array();
    $temp = $x;

// Initialize result
    while ($temp != 0) {
        $res[$res_size++] = $temp % 10;
        $temp = $temp / 10;
    }

// Multiply x n times
// (x^n = x*x*x....n times)
    for ($i = 2; $i <= $n; $i++)
        $res = multiply($x, $res);

    $O = 0;
    for ($i = count($res) - 1; $i >= 0; $i--, $O++)
        if($res[$i]) break;

    for ($i = count($res) - $O - 1; $i >= 0; $i--)
        return $res[$i];
}

// Driver Code
//$exponent = 100;
//$base = 2;
//power($base, $exponent);

// This code is contributed
// by mits