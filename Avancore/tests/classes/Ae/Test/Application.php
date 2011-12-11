<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ae_Test_Application extends Ae_Test_Base {
    
    function testApplication() {
        $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/classes/Sample/App.php';
        require_once($appClassFile);
        $sam = Sample_App::getInstance();
        $leg = $sam->getLegacyDatabase();
        $leg->setQuery("SHOW TABLES");
        $this->assertTrue(is_array($leg->loadAssocList()));
        $db = $sam->getDb();
        $this->assertTrue(is_array($db->fetchArray('SHOW TABLES')));
    }
    
}