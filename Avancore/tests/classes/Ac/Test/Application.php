<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Application extends Ac_Test_Base {
    
    function testApplication() {
        $sam = $this->getSampleApp();
        $this->expectError('mysql_connect(): The mysql extension is deprecated and will be removed in the future: use mysqli or PDO instead');
        $leg = $sam->getLegacyDatabase();
        $leg->setQuery("SHOW TABLES");
        $this->assertTrue(is_array($leg->loadAssocList()));
        $db = $sam->getDb();
        $this->assertTrue(is_array($db->fetchArray('SHOW TABLES')));
    }
    
    function testPlugins() {
        $sam = $this->getSampleApp();
        if ($this->assertTrue(in_array('otherPeople', $sam->listMappers()))) {
            $m = $sam->getMapper('otherPeople');
            $this->assertIsA($m, 'Ac_Model_Mapper');
        }
    }
    
}