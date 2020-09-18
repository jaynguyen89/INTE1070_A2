<?php

$e = $_GET['e'];
$o = $_GET['o'];

$d = 0.1;

$i = 1;
while ($d != (int) $d) {
    $d = ($o * $i + 1) / $e;
    $i++;
}

echo $d;