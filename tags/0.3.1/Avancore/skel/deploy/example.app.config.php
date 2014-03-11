<?php

$config = array(
    
    'webUrl' => 'http://host/path/',
    'avancorePath' => '../../Avancore',
    
    'legacyDatabasePrototype' => array(
        'class' => 'Ac_Native_Database',
        '__construct' => array(array(
            'user' => 'dbuser',
            'password' => 'dbpassword',
            'db' => 'dbname',
            'prefix' => 'dbprefix',
        )),
    ),
    
);
