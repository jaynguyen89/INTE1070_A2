<?php

$quantity = $_GET['quantity'];

if ($quantity != 1) echo json_encode(compute_keys($quantity));
else echo json_encode(compute_keys_single());



function compute_keys($quantity = 1) {
    $keys = array();

    $first_keys = compute_keys_single();echo json_encode($first_keys);
    array_push($keys, $first_keys['n']);
    array_push($keys, $first_keys['d']);

    $turn = 1;
    $e = $first_keys['e'];
    $o = $first_keys['o'];

    while ($turn < $quantity) {
        $e = compute_e($o, $e + 1);
        $d = compute_d($e, $o);

        if ($e == $first_keys['n']) continue;

        array_push($keys, $d);
        $turn++;
    }

    $extra_e = 1;
    foreach ($keys as $key) $extra_e *= $key;

    $extra_d = compute_d($extra_e, $o);
    array_push($keys, $extra_d);

    return $keys;
}

function compute_keys_single() {
    $e = 0;
    $p = 0;
    $q = 0;
    $n = 0;

    while ($e == $p || $e == $q || $e == $n) {
        $prime_pair = get_two_random_primes(get_prime_numbers(100));

        $p = $prime_pair[0];
        $q = $prime_pair[1];

        $n = $p * $q;
        $o = ($p - 1) * ($q - 1);

        $e = compute_e($o);
        $d = compute_d($e, $o);
    }

    return array('e' => $e, 'n' => $n, 'd' => $d, 'o' => $o);
}

function compute_e($o, $min = 1) {
    $e = $min;
    while ($o % $e == 0 && $e < $o) $e++;

    return $e;
}

function compute_d($e, $o) {
    $d = 0.1;

    $i = 1;
    while ($d != (int) $d) {
        $d = ($o * $i + 1) / $e;
        $i++;
    }

    return $d;
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

function get_divisors_of($any) {
    $divisors = array();

    for ($i = 1; $i <= $any; $i++)
        if ($any % $i == 0)
            array_push($divisors, $i);
}