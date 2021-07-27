<?php

require_once(dirname(__DIR__).'/classes/Ac/Avancore.php');
Ac_Avancore::getInstance();
Ac_Util::addIncludePath(__DIR__);
Ac_Util::addIncludePath(__DIR__.'/classes');
Ac_Util::addIncludePath(__DIR__.'/gen/classes');

ini_set('error_reporting', E_ALL);

ini_set('display_errors', 1);
ini_set('html_errors', 1);

?>