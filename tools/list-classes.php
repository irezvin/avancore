#!/bin/php
<?php

$files = explode("\n", `find classes -name '*.php' -type f`);
foreach ($files as $i => $file) {
    $file = str_replace("classes/", "", $file);
    $file = str_replace(".php", "", $file);
    $file = str_replace("/", "_", $file);
    $files[$i] = $file;
}
usort($files, function($a, $b) {
    if ($a == $b) return 0;
    if (strlen($a) < strlen($b) && !strncmp($a, $b, strlen($a))) return -1;
    if (strlen($b) < strlen($a) && !strncmp($b, $a, strlen($b))) return 1;
    return strcmp($a, $b);
});
foreach ($files as $file) {
    $ns = "\\".str_replace("_", "\\", $file);
    echo $file."\t".$ns."\n";
}
//echo implode("\n", $files);