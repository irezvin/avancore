<?php

if (is_dir('/home/nivzer')) {
    $u = 'irezvin';
    $p = 'Zo6zi3H';
    $d = 'ac3_test_new';
} elseif (is_dir('/home/i1ya')) {
    $u = 'i1ya';
    $p = 'iiv80of3';
    $d = 'avancore_test';
} else {
    throw new Exception ("Unknown machine");
}

if (isset($_SERVER) && isset ($_SERVER['REQUEST_SCHEME'])) {
    $protocol = strtolower($_SERVER['REQUEST_SCHEME']);
} else {
    $protocol = 'https';
}

if (is_dir('/home/nivzer')) {

    $config = array(
        'webUrl' => $protocol.'://crimson/ac3/',
        'useFirePHP' => true,
    );

} elseif (is_dir('/home/i1ya/')) {

    $config = array(
        'webUrl' => $protocol.'://akita/nextAvancore/',
        'useFirePHP' => true,
    );

} else {

    throw new Exception ("Unknown server!");

}

$config['assetPlaceholders']['{TOOLBAR}'] = '{AC}/vendor/images/';

$config['dbPrototype'] = array(
    'class' => 'Ac_Sql_Db_Pdo',
    'dbPrefix' => 'ac_',
    'pdo' => array(
        'dsn' => "mysql:dbname=".$d.";socket=/var/run/mysql/mysqld.sock",
        'username' => $u,
        'password' => $p,
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8;',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        )
    ),
);
