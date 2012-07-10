#!/usr/local/bin/php
<?php

function binaryTime($ts) {

    $hex = hexdec($ts[14].$ts[15]);
    $pow = bcpow(2,56);
    $p0  = bcmul($hex, $pow);   
 
    $hex = hexdec($ts[12].$ts[13]);
    $pow = bcpow(2,48);
    $p1  = bcmul($hex, $pow);

    $hex = hexdec($ts[10].$ts[11]);
    $pow = bcpow(2,40);
    $p2  = bcmul($hex, $pow);

    $hex = hexdec($ts[8].$ts[9]);
    $pow = bcpow(2,32);
    $p3  = bcmul($hex, $pow);

    $hex = hexdec($ts[6].$ts[7]);
    $pow = bcpow(2,24);
    $p4 =  bcmul($hex, $pow);

    $hex = hexdec($ts[4].$ts[5]);
    $pow = bcpow(2,16);
    $p5  = bcmul($hex, $pow);

    $hex = hexdec($ts[2].$ts[3]);
    $pow = bcpow(2,8);
    $p6  = bcmul($hex, $pow);

    $p7 = hexdec($ts[0].$ts[1]);

    $term = bcadd($p0,$p1);
    $term = bcadd($term,$p2);
    $term = bcadd($term,$p3);
    $term = bcadd($term,$p4);
    $term = bcadd($term,$p5);
    $term = bcadd($term,$p6);
    $term = bcadd($term,$p7);

    $ppp = bcmul('10000000', '86400');
    $days = bcdiv($term, $ppp);

    $date = date("Y-m-d H:i:s", strtotime("1601-01-01 +$days days"));

    return $date;
  
}


$ts = binaryTime('00b2f63495e6cc01');

echo $ts;

?>
