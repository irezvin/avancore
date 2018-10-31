<?php

require('../tests/testsStartup.php');
require_once('simpletest/reporter.php');
require_once(dirname(__FILE__).'/classes/Ac/Test/Base.php');
require_once(dirname(__FILE__).'/app.config.php');
define('AC_TESTS_TMP_PATH', dirname(__FILE__).'/var');
Ac_Test_Base::$config = $config;

?>
<html>
    <head>
        <style type="text/css">
        .fail { background-color: inherit; color: red; }
        .pass { background-color: inherit; color: green; }
        pre { background-color: lightgray; color: inherit; }
            
        </style>
<?php

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
        echo "<title>Avancore 0.3 tests</title>";
        $head = "<a href='?class={$class}'>{$class}</a>";
        $t = new TestSuite($head);
        $t->add($class);
        $t->run(new Ac_Test_Reporter('UTF-8'));
        ini_set('html_errors', 1);
        var_dump(round(microtime(true) - $time, 3));
        $time = microtime(true);
    }
    
	
}

if (function_exists('xdebug_time_index')) var_dump(xdebug_time_index(), memory_get_peak_usage()/1024/1024);