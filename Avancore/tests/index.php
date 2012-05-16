<?php

require('../tests/testsStartup.php');
require_once('simpletest/reporter.php');

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['class']) && strlen($class = $_GET['class'])) {
	
	if (!in_array(substr($class, 0, 8), array('Ac_Test_')))
    	$cn = 'Ac_Test_'.ucfirst($class);
    else
    	$cn = $class;
    $t = new $cn;
    $t->run(new HtmlReporter('UTF-8'));

} else {
	$t = new TestSuite('Avancore Framework v0.3 Tests');
	$t->add(new Ac_Test_CsvResume);
	$t->add(new Ac_Test_Hacks);
	$t->add(new Ac_Test_Dbi);
	$t->add(new Ac_Test_SqlSelect);
	$t->add(new Ac_Test_RefChecker);
	$t->add(new Ac_Test_ModelSql);
    $t->add(new Ac_Test_Adapter);	
		
	$t->run(new HtmlReporter('UTF-8'));
	
}

?>
