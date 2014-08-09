<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Mapper extends Ac_Test_Base {

    var $peopleCols = array(
        'personId', 'name', 'gender', 'isSingle', 'birthDate', 'lastUpdatedDatetime', 'createdTs', 'sexualOrientationId'
    );
    
    function testMapper() {
        $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/gen/classes/Sample/DomainBase.php';
        require_once($appClassFile);
        $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/classes/Sample.php';
        require_once($appClassFile);
        $sam = Sample::getInstance();
        
        $sam->addMapper(Ac_Prototyped::factory(array('id' => 'people', 'tableName' => '#__people'), 'Ac_Model_Mapper'));
        $m = $sam->getMapper('people');
        $this->assertEqual($m->pk, 'personId', 'Auto-detection of primary key by Ac_Model_Mapper');
        $this->assertTrue(!array_diff($m->getColumnNames(), $this->peopleCols), 'Ac_Model_Mapper::getColumnNames()');
        $this->assertSame($m, Ac_Model_Mapper::getMapper('people'));
        $rec = $m->loadRecord(3);
        $this->assertEqual(Ac_Accessor::getObjectProperty($rec, 'personId'), 3, 'Ensure Ac_Accessor retrieves values from barebones Ac_Model_Object');
        $this->assertEqual($m->getAutoincFieldName(), 'personId', 'Ac_Model_Mapper::getAutoincFieldName()');
        
        $pm = $sam->getSamplePersonMapper();
        $pp = $pm->loadRecordsByCriteria('', array('gender', 'personId'));
        $this->assertArraysMatch(array(
            'M' => array('3' => array('__class' => 'Sample_Person', 'name' => 'Илья', 'gender' => 'M', 'personId' => 3)),
            'F' => array('4' => array('__class' => 'Sample_Person', 'name' => 'Таня', 'gender' => 'F', 'personId' => 4)) 
        ), $pp);
        $pp = $pm->loadRecordsByCriteria('', array('gender'));
        $this->assertArraysMatch(array(
            'M' => array(array('__class' => 'Sample_Person', 'name' => 'Илья', 'gender' => 'M', 'personId' => 3)),
            'F' => array(array('__class' => 'Sample_Person', 'name' => 'Таня', 'gender' => 'F', 'personId' => 4)) 
        ), $pp);
        
        
        $pm = $m;
        $pp = $pm->loadRecordsByCriteria('', array('gender', 'personId'));
        $this->assertArraysMatch(array(
            'M' => array('3' => array('__class' => 'Ac_Model_Record', 'name' => 'Илья', 'gender' => 'M', 'personId' => 3)),
            'F' => array('4' => array('__class' => 'Ac_Model_Record', 'name' => 'Таня', 'gender' => 'F', 'personId' => 4)) 
        ), $pp);
        $pp = $pm->loadRecordsByCriteria('', array('gender'));
        $this->assertArraysMatch(array(
            'M' => array(array('__class' => 'Ac_Model_Record', 'name' => 'Илья', 'gender' => 'M', 'personId' => 3)),
            'F' => array(array('__class' => 'Ac_Model_Record', 'name' => 'Таня', 'gender' => 'F', 'personId' => 4)) 
        ), $pp);
        
        
    }
    
    function testPersistence() {
        // TODO: people => person
        $sam = Sample::getInstance();
        $m = $sam->getSamplePersonMapper();
        $guy = $sam->createSamplePerson();
        $guy->name = 'Guy';
        $guy->birthDate = '0000-00-00';
        $guy->store();
        
        $this->assertTrue($guy->isPersistent());
        $guy2 = $m->loadRecord($guy->getPrimaryKey());

        /*var_dump($guy->getDataFields());
        var_dump($guy2->getDataFields());*/
        
        $guy->delete();
    }
    
    function testLoadFromRows() {
        $sam = Sample::getInstance();
        $m = $sam->getSamplePersonMapper();
        $m->useRecordsCollection = true;
        $first = $m->loadByPersonId(3);
        $rows = $this->getAeDb()->fetchArray("SELECT * FROM ".$m->tableName);
        $rows = array_merge($rows, $rows);
        $records = $m->loadFromRows($rows);
        $byPk = array();
        foreach ($records as $rec) {
            $pk = $rec->getPrimaryKey();
            if (isset($byPk[$pk])) $this->assertSame($rec, $byPk[$pk]);
            if ($pk === $first->getPrimaryKey())
                $this->assertSame($rec, $first);
        }
        $m->useRecordsCollection = false;
        $m->clearCollection();
        $records = $m->loadFromRows($rows);
        foreach ($records as $rec) {
            $pk = $rec->getPrimaryKey();
            if (isset($byPk[$pk])) $this->assertSame($rec, $byPk[$pk]);
            if ($pk === $first->getPrimaryKey())
                $this->assertTrue ($rec !== $first);
        }
    }
    
}