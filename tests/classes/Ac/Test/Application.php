<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Application extends Ac_Test_Base {
    
    function testApplication() {
        $sam = $this->getSampleApp();
        $db = $sam->getDb();
        $this->assertTrue(is_array($db->fetchArray('SHOW TABLES')));
    }
    
    function testPlugins() {
        $sam = $this->getSampleApp();
        if ($this->assertTrue(in_array('otherPeople', $sam->listMappers()))) {
            $m = $sam->getMapper('otherPeople');
            $this->assertIsA($m, 'Ac_Model_Mapper');
            //foreach ($m->getAllRecords() as $rec) var_dump($rec->getDataFields());
        }
    }
    
    function testConvenientAccess() {
        $sam = $this->getSampleApp();
        $this->assertTrue($sam->c->people instanceof Sample_Person_Mapper);
        $this->assertEqual(array_values($sam->db('a', 'b', 'c')->fetchRow('SELECT ?, ?, ?')), ['a', 'b', 'c']);
        $person = $sam->createSamplePerson(['name' => 'Foo Example', 'gender' => 'M']);
        $this->assertEqual($person->name, 'Foo Example');
        $this->assertEqual($person->gender, 'M');
        $this->assertEqual(array_values($person->db('a', 'b')->fetchRow('SELECT ?, ?')), ['a', 'b']);
    }
    
}