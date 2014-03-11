<?php

ini_set('include_path', 
      '.'
    . PATH_SEPARATOR .dirname(__FILE__).'/../classes'
	. PATH_SEPARATOR . '.' 
	. PATH_SEPARATOR . dirname(__FILE__) . '/classes'
);

ini_set('error_reporting', E_ALL);


ini_set('display_errors', 1);
ini_set('html_errors', 1);

require_once('Ac/Avancore.php');
Ac_Avancore::getInstance();

?>
