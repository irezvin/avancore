<?php

$buf = '';
for ($i = 0; $i < 10000; $i++) {
    $buf .= str_repeat(md5(rand()), rand(20, 30)).'##['.uniqid('', true).']##';
}

echo "Buf length: \n";
var_dump(strlen($buf));
$i = xdebug_time_index();
$h = md5($i);
$ms = (xdebug_time_index() - $i)*1000;
echo "Md5 time, ms: \n";
var_dump($ms);

$i = xdebug_time_index();
$s = preg_split('/(##\[[0-9a-z.]+\]##)/', $buf, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
$ms = (xdebug_time_index() - $i)*1000;
echo "Preg_split time, ms: \n";
var_dump($ms);

require('/home/i1ya/Work/Avancore3/Avancore/classes/Ac/StringObject.php');
$i = xdebug_time_index();
$ctx = Ac_StringObject::getStringObjectContext($buf, strlen($buf), true);
$ms = (xdebug_time_index() - $i)*1000;
echo "Worst-case getStringObjectContext time, ms: \n";
var_dump($ms);

$i = xdebug_time_index();
$ctx = Ac_StringObject::getStringObjectContext($buf, strlen($buf), true);
$ms = (xdebug_time_index() - $i)*1000;
echo "Worst-case getStringObjectContext time (Second call), ms: \n";
var_dump($ms);


?>


