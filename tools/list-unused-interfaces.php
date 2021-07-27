#!/bin/php
<?php

// Lists unused interfaces

$root = dirname(__DIR__);
$cmd = "find '$root/classes' '$root/obsolete' -type f -name '*.php' -print0 | xargs -0 grep -oP 'interface [A-Z]\\w+'";
exec($cmd, $output, $return);
$interfaces = [];
foreach ($output as $row) {
    $interfaces[] = preg_replace('/^.*interface\\s+/', '', trim($row));
}
$interfaces = array_unique($interfaces);
sort($interfaces);
$interfaces = array_reverse($interfaces);
$rx = '('.implode("|", $interfaces).')';
$cmd2 = "find '$root/classes' '$root/obsolete' -type f -name '*.php' -print0 | xargs -0 grep -oP '.*".$rx."'";
//echo $cmd2;
//die();
$used = [];
exec($cmd2, $output, $return);
foreach ($output as $row) {
    $row = preg_replace("/interface\\s+{$rx}/", "", $row);
    preg_match_all("/{$rx}/", trim($row), $matches);
    $used = array_unique(array_merge($used, $matches[0]));
}
$unused = array_diff($interfaces, $used);
echo implode("\n", $unused);

