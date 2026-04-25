<?php
$ip   = '192.168.7.50';
$mask = '255.255.248.0';
$broadcast = long2ip(ip2long($ip) | (~ip2long($mask) & 0xFFFFFFFF));
echo 'IP       : ' . $ip       . PHP_EOL;
echo 'Mask     : ' . $mask     . PHP_EOL;
echo 'Network  : ' . long2ip(ip2long($ip) & ip2long($mask)) . PHP_EOL;
echo 'Broadcast: ' . $broadcast . PHP_EOL;
