<?php

require('../tests/testsStartup.php');
require_once('simpletest/reporter.php');
require_once(dirname(__FILE__).'/classes/Ac/Test/Base.php');
require_once(dirname(__FILE__).'/app.config.php');
define('AC_TESTS_TMP_PATH', dirname(__FILE__).'/var');
Ac_Test_Base::$config = $config;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['class']) && strlen($class = $_GET['class'])) {
    
    $classes = explode(',', $_GET['class']);
	
    $ts = new TestSuite('Avancore Framework v0.3 Tests');
    
    foreach ($classes as $class) {

        if (!in_array(substr($class, 0, 8), array('Ac_Test_')))
            $cn = 'Ac_Test_'.ucfirst($class);
        else
            $cn = $class;
        $test = new $cn;
        $ts->add($test);
    }
    $ts->run(new HtmlReporter('UTF-8'));
    
} else {
	$t = new TestSuite('Avancore Framework v0.3 Tests');
    $classes = array();
    foreach ($files = glob(dirname(__FILE__).'/classes/Ac/Test/*.php') as $file) {
        $class = 'Ac_Test_'.basename($file, '.php');
        if ($class !== 'Ac_Test_Base') $classes[] = $class;
    }
    foreach ($classes as $class) $t->add($class);
		
	$t->run(new HtmlReporter('UTF-8'));
	
}

if (function_exists('xdebug_time_index')) var_dump(xdebug_time_index());

