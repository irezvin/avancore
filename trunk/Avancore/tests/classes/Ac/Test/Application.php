<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Application extends Ac_Test_Base {
    
    function testApplication() {
        $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/gen/classes/Sample/DomainBase.php';
        require_once($appClassFile);
        $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/classes/Sample.php';
        require_once($appClassFile);
        $sam = Sample::getInstance();
        $leg = $sam->getLegacyDatabase();
        $leg->setQuery("SHOW TABLES");
        $this->assertTrue(is_array($leg->loadAssocList()));
        $db = $sam->getDb();
        $this->assertTrue(is_array($db->fetchArray('SHOW TABLES')));
    }
    
}