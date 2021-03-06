<?php

$signers = array_key_exists('signers', $_GET) ? $_GET['signers'] : 0;

if ($signers > 1) echo json_encode(compute_keys_multi($signers));
If ($signers == 1) echo json_encode(compute_keys_single());

//compute the keys for 3 owners to sign cheque using combined signature
//for 3 owners, we need 4 keys, one of which will be the public key for the bank verifier
function compute_keys_multi($signers = 3) {
    if ($signers < 3 || $signers > 5) return null;
    $rsa_keys = array('pub' => array(), 'priv' => array()); //the response

    while (true) {
        $keys = array();
        $prime_pair = get_two_random_primes(get_prime_numbers(500));

        //first and second keys are selected randomly, given that they are different
        $first_key = 1;
        $second_key = 1;
        while (in_array($first_key, $prime_pair) || in_array($second_key, $prime_pair) || $first_key == $second_key) {
            $initial_keys = get_two_random_primes(get_prime_numbers(100));
            $first_key = $initial_keys[0];
            $second_key = $initial_keys[1];
        }

        array_push($keys, $first_key);
        array_push($keys, $second_key);

        //Now compute the 2 remaining keys, so I compute a number d that have key1 * key2 * d = 1 mod(o)
        //then I get the divisors of d for each remaining key given that all product of remaining keys equals d
        $n = $prime_pair[0] * $prime_pair[1];
        $o = ($prime_pair[0] - 1) * ($prime_pair[1] - 1);

        $d = compute_d($first_key * $second_key, $o);

        $d_divisors = get_divisors_of($d);
        $d_divisors = array_diff($d_divisors, array(1, $d)); //remove 1 and d in divisors

        if (count($d_divisors) < $signers - 1) //d is a prime number, so have to redo the computation
            continue;

        $third_key = $d_divisors[rand(1, count($d_divisors) - 2)]; //select random divisor to be third key
        $fourth_key = $d / $third_key; //calculate the remaining key

        array_push($keys, $third_key);
        array_push($keys, $fourth_key);

        $pubkey_index = rand(0, 3); //select a random key to be public key
        $rsa_keys['pub']['key'] = $keys[$pubkey_index];

        foreach ($keys as $v) {
            if (array_search($v, $keys) == $pubkey_index) continue;
            array_push($rsa_keys['priv'], array('key' => $v, 'used' => false));
        }

        $rsa_keys['pub']['n'] = $n;
        break;
    }

    return $rsa_keys;
}

//Generate the keys for signing cheque separately
function compute_keys_single() {
    $e = 0;
    $p = 0;
    $q = 0;
    $n = 0;

    while ($e == $p || $e == $q || $e == $n) {
        $prime_pair = get_two_random_primes(get_prime_numbers(200));

        $p = $prime_pair[0];
        $q = $prime_pair[1];

        $n = $p * $q;
        $o = ($p - 1) * ($q - 1);

        $e = compute_e($o);
        $d = compute_d($e, $o);
    }

    return array('d' => $d, 'n' => $n, 'e' => $e); //the final keys
}

function compute_e($o) { //co-prime to o
    $o_divisors = get_distinct_divisors_of($o);

    $e_divisors = array();
    for ($i = 1; $i < $o_divisors[count($o_divisors) - 1]; $i++) {
        if (count($e_divisors) == 2) break;

        if (is_prime($i) && !in_array($i, $o_divisors))
            array_push($e_divisors, $i);
    }

    $e = 1;
    foreach ($e_divisors as $v) $e *= $v;

    return $e;
}

function compute_d($e, $o) { //de=1mod(o)
    $d = 0.1;

    $i = 1;
    while ($d != (int) $d) {
        $d = ($o * $i + 1) / $e;
        $i++;
    }

    return $d;
}

function compute_product($ar) {
    $product = 1;
    foreach ($ar as $v) $product *= $v;

    return $product;
}

function get_prime_numbers($lim = 1000) {
    $primes = array();

    $prime = 2;
    array_push($primes, $prime);

    while ($prime < $lim) {
        $prime++;

        $is_prime = true;
        for ($i = 2; $i < $prime; $i++)
            if ($prime % $i === 0) {
                $is_prime = false;
                break;
            }

        if ($is_prime) array_push($primes, $prime);
    }

    return $primes;
}

function get_two_random_primes($primes) {
    $first = $primes[rand(0, count($primes) - 1)];
    $second = $first;

    while ($second == $first) $second = $primes[rand(0, count($primes) - 1)];
    return array($first, $second);
}

//Distinct divisor means no divisor can be divisible by another divisor
function get_distinct_divisors_of($any) {
    $divisors = get_divisors_of($any);

    $distinct_divisors = array();
    foreach ($divisors as $divisor)
        if (is_prime($divisor))
            array_push($distinct_divisors, $divisor);

    return $distinct_divisors;
}

function get_divisors_of($any) {
    $divisors = array();

    for ($i = 1; $i <= $any; $i++)
        if ($any % $i == 0)
            array_push($divisors, $i);

    return $divisors;
}

function is_prime($any) {
    if ($any == 1) return false;

    for ($i = 2; $i <= $any / 2; $i++)
        if ($any % $i == 0) return false;

    return true;
}


//Support any number of signers greater than 1 people by increasing the limit of get_prime_number
//The limit should be double for each 1 addition of signers
//function compute_keys_multi($signers = 3) {
//    if ($signers < 2 || $signers > 5) return null;
//    $rsa_keys = array('pub' => array(), 'priv' => array());
//
//    while (true) {
//        $keys = array();
//        $prime_pair = get_two_random_primes(get_prime_numbers(500));
//
//        $first_key = 1;
//        $second_key = 1;
//        while (
//            in_array($first_key, $prime_pair) ||
//            in_array($second_key, $prime_pair) ||
//            $first_key == $second_key)
//        {
//            $initial_keys = get_two_random_primes(get_prime_numbers(100));
//            $first_key = $initial_keys[0];
//            $second_key = $initial_keys[1];
//        }
//
//        array_push($keys, $first_key);
//        array_push($keys, $second_key);
//
//        $n = $prime_pair[0] * $prime_pair[1];
//        $o = ($prime_pair[0] - 1) * ($prime_pair[1] - 1);
//
//        $d = compute_d($first_key * $second_key, $o);
//
//        if ($signers == 2) array_push($keys, $d);
//        else { //number of signers > 2
//            $other_keys = get_other_keys($d, $signers - 1);
//            if (!$other_keys) continue;
//
//            $keys = array_merge($keys, $other_keys);
//        }
//
//        $pubkey_index = rand(0, count($keys) - 1);
//        $rsa_keys['pub']['key'] = $keys[$pubkey_index];
//
//        foreach ($keys as $v) {
//            if (array_search($v, $keys) == $pubkey_index) continue;
//            array_push($rsa_keys['priv'], array('key' => $v, 'used' => false));
//        }
//
//        $rsa_keys['pub']['n'] = $n;
//        break;
//    }
//
//    return $rsa_keys;
//}

function get_other_keys($d, $qty)
{
    $d_divisors = get_distinct_divisors_of($d);
    if (count($d_divisors) < $qty) return false;

    $powers = get_powers_for($d_divisors, $d);

    $keys = array();
    //Since number of divisors can be much greater than $qty, so
    //Get the keys up to $qty - 1, then get the last key
    for ($i = 0; $i < $qty - 1; $i++) {
        $divisor = $d_divisors[$i];
        array_push($keys, $divisor ** $powers[$divisor]);
    }

    //The last key
    array_push($keys, $d / powers_product($powers));
    return $keys;
}

function get_powers_for($divisors, $d)
{
    $powers = array(); //save [$divisor => $power]
    $n = count($divisors) - 1;

    $power = 0;
    $quotient = $d;
    //Algorithm: divide $d to the (next) greatest divisor until quotient = 1
    while ($quotient >= 1) {
        $quotient = $quotient / $divisors[$n];
        if ($quotient == intval($quotient)) $power++;
        else {
            $powers[$divisors[$n]] = $power;

            $quotient = $quotient == 1 ? 0 : $d / powers_product($powers);
            $power = 0;
            $n--;
        }
    }

    return $powers;
}

function powers_product($powers)
{
    $product = 1;
    foreach ($powers as $k => $v)
        $product *= ($k ** $v);

    return $product;
}
