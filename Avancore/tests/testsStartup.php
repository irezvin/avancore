<?php

ini_set('include_path', 
      '.'
    . PATH_SEPARATOR .dirname(__FILE__).'/../classes'
    . PATH_SEPARATOR .dirname(__FILE__).'/../obsolete'
	. PATH_SEPARATOR . '.' 
	. PATH_SEPARATOR . dirname(__FILE__)
	. PATH_SEPARATOR . dirname(__FILE__) . '/classes'
	. PATH_SEPARATOR . dirname(__FILE__) . '/gen/classes'
);

ini_set('error_reporting', E_ALL ^ E_DEPRECATED);

require('Ac/Util.php');
Ac_Util::registerAutoload();

ini_set('display_errors', 1);
ini_set('html_errors', 1);
ini_set('max_execution_time', 240);

?>