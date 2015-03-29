<?php

$dbuser = 'user';
$dbpassword = 'password';
$dbprefix = 'ac_';
$dbname = 'test_avancore';

$config = array(
    'dbPrototype' => array(
        'class' => 'Ac_Sql_Db_Pdo',
        'dbPrefix' => $dbprefix,
        'pdo' => array(
            'dsn' => 'mysql:host='.$host.';dbname='.$dbname,
            'username' => $dbuser,
            'password' => $dbpassword,
            'driver_options' => array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
            ),
        ),
    ),
    'cachePath' => dirname(__FILE__).'/var',
    'absolutePath' => dirname(__FILE__),
    'liveSite' => 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']),
    'charset' => 'utf8',
    'listLimit' => 50,
    'cacheLifeTime' => 600,
);
