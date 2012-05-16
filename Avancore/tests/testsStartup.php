<?php

ini_set('include_path', 
      '.'
    . PATH_SEPARATOR .dirname(__FILE__).'/../classes'
	. PATH_SEPARATOR . '.' 
	. PATH_SEPARATOR . dirname(__FILE__)
	. PATH_SEPARATOR . dirname(__FILE__) . '/classes'
	. PATH_SEPARATOR . dirname(__FILE__) . '/gen/classes'
);

ini_set('error_reporting', E_ALL);


function __autoload($class) {
    require(str_replace('_', '/', $class).'.php');
}

ini_set('display_errors', 1);
ini_set('html_errors', 1);

/*
Ac_Dispatcher::instantiate('avancore', false, 'english', 'Ac_Legacy_Adapter_Native', 'Ac_Dispatcher', array(
	'configPath' => dirname(__FILE__).'/app.config.php',
	'cachePath' => dirname(__FILE__).'/var'
));
*/
?>