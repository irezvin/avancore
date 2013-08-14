<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Application extends Ac_Test_Base {
    
    function testApplication() {
        $sam = $this->getSampleApp();
        $leg = $sam->getLegacyDatabase();
        $leg->setQuery("SHOW TABLES");
        $this->assertTrue(is_array($leg->loadAssocList()));
        $db = $sam->getDb();
        $this->assertTrue(is_array($db->fetchArray('SHOW TABLES')));
    }
    
}