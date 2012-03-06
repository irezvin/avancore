<?php

$config = array(
	'host' => 'localhost',
    'db' => 'avancore_test',
    'user' => 'irezvin',
    'password' => 'iiv80of3',
    'cachePath' => dirname(__FILE__).'/var',
    'absolutePath' => dirname(__FILE__),
    'liveSite' => 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']),
    'charset' => 'utf8',
    'listLimit' => 50,
    'cacheLifeTime' => 600,
    'prefix' => 'ac_',
);

?>
