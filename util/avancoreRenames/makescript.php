<?php

$f = file($_SERVER['argv'][1]);
$o = array();
foreach ($f as $line) {
    $line = trim($line);
    $l = preg_split("/\s*\>\s*/", $line, 2);
    $filename1 = str_replace('_', '/', $l[0]).'.php';
    $filename2 = str_replace('_', '/', $l[1]).'.php';
    $o[] = "s/\b".preg_quote($l[0])."\b/".preg_quote($l[1])."/g";
    $o[] = "s/\b".preg_quote($filename1, "/")."\b/".preg_quote($filename2, "/")."/g";
}
file_put_contents($_SERVER['argv'][2], implode("\n", $o));
