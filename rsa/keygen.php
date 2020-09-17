<?php

$prime_pair = get_two_random_primes(get_prime_numbers(200));

$p = $prime_pair[0];
$q = $prime_pair[1];

$n = $p * $q;
$o = ($p - 1) * ($q - 1);

$e = compute_e($o);

$d = compute_d($e, $o);

echo json_encode(array('e' => $e, 'n' => $n, 'd' => $d));

function compute_e($o) {
    $e = 1;
    while ($o % $e == 0) $e++;

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