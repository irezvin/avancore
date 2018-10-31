<?php

require('../tests/testsStartup.php');
require_once('simpletest/reporter.php');
require_once(dirname(__FILE__).'/classes/Ac/Test/Base.php');
require_once(dirname(__FILE__).'/app.config.php');
define('AC_TESTS_TMP_PATH', dirname(__FILE__).'/var');
Ac_Test_Base::$config = $config;

if (isset($_GET['class']) && strlen($class = $_GET['class'])) {
    
    $classes = explode(',', $_GET['class']);
	
    $ts = new TestSuite(implode(', ', $classes));
    
    foreach ($classes as $class) {

        if (!in_array(substr($class, 0, 8), array('Ac_Test_')))
            $cn = 'Ac_Test_'.ucfirst($class);
        else
            $cn = $class;
        $test = new $cn;
        $ts->add($test);
    }
    $ts->run(new Ac_Test_Reporter('UTF-8'));
    
} else {
    $classes = array();
    foreach ($files = glob(dirname(__FILE__).'/classes/Ac/Test/*.php') as $file) {
        $class = 'Ac_Test_'.basename($file, '.php');
        if ($class !== 'Ac_Test_Base' && $class !== 'Ac_Test_Reporter') $classes[] = $class;
    }
    
    
    $time = microtime(true);
    foreach ($classes as $class) {
        
        // TODO: Ac_Cg_Domain::listModels() runs in infinite loop
        if ($class === 'Ac_Test_CgImport') continue;
        
        $t = new TestSuite($class);
        $t->add($class);
        $t->run(new Ac_Test_Reporter('UTF-8'));
        var_dump(microtime(true) - $time);
        $time = microtime(true);
    }
    
	
}

if (function_exists('xdebug_time_index')) var_dump(xdebug_time_index(), memory_get_peak_usage()/1024/1024);