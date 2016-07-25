<?php

require('../tests/testsStartup.php');
require_once('simpletest/reporter.php');
require_once(dirname(__FILE__).'/classes/Ac/Test/Base.php');
require_once(dirname(__FILE__).'/app.config.php');
define('AC_TESTS_TMP_PATH', dirname(__FILE__).'/var');
Ac_Test_Base::$config = $config;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$ac = true;
$etl = false;
if (isset($_GET['etl'])) $etl = (bool)($_GET['etl']);
if (isset($_GET['ac'])) $ac = (bool) ($_GET['ac']);

$v = Ac_Avancore::version;

if (isset($_GET['class']) && strlen($class = $_GET['class'])) {
    
    $classes = explode(',', $_GET['class']);
	
    $ts = new TestSuite("Ac {$v} Test".(count($classes == 1)? '' : 's').": {$_GET['class']}");
    
    foreach ($classes as $class) {

        if (strpos($class, '_Test_') === false)
            $cn = 'Ac_Test_'.ucfirst($class);
        else
            $cn = $class;
        $test = new $cn;
        $ts->add($test);
    }
    $ts->run(new Ac_Test_Reporter('UTF-8'));
    
} else {
    $title = "Avancore Framework {$v} Tests";
    $t = new TestSuite($title);
    $classes = array();
    if ($ac) {
        foreach ($files = glob(dirname(__FILE__).'/classes/Ac/Test/*.php') as $file) {
            $class = 'Ac_Test_'.basename($file, '.php');
            if ($class !== 'Ac_Test_Base' && $class !== 'Ac_Test_Reporter') $classes[] = $class;
        }
    }
    if ($etl) {
        foreach ($files = glob(dirname(__FILE__).'/classes/Etl/Test/*.php') as $file) {
            if (is_file($file)) $classes[] = "Etl_Test_".basename($file, '.php');
        }
    }
    foreach ($classes as $class) $t->add($class);
		
	$t->run(new Ac_Test_Reporter('UTF-8'));
	
}

if (function_exists('xdebug_time_index')) {
    var_dump(xdebug_time_index(), round(memory_get_peak_usage()/1024/1024, 2).'M');
}

