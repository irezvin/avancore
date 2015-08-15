<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Mapper extends Ac_Test_Base {

    var $peopleCols = array(
        'personId', 'name', 'gender', 'isSingle', 'birthDate', 'lastUpdatedDatetime', 'createdTs', 'religionId',  'portraitId'
    );
    
    var $bootSampleApp = true;
    
    function testMapper() {
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
        
        $tmp = $m->useRecordsCollection;
        $m->useRecordsCollection = false;
        $guy2 = $m->loadRecord($guy->getPrimaryKey());
        $m->useRecordsCollection = $tmp;
        
        $this->assertTrue($guy2->name == $guy->name);

        /*var_dump($guy->getDataFields());
        var_dump($guy2->getDataFields());*/
        
        $id = $this->resetAi('#__people');
        $guy3 = $sam->createSamplePerson();
        $guy3->bind(array('personId' => $id, 'name' => 'Guy 3', 'gender' => 'M', 'birthDate' => '0000-00-00'));
        if ($this->assertTrue($guy3->store())) {
            $this->assertTrue($guy3->isPersistent());
            $row = $sam->getDb()->args($id)->fetchRow('SELECT * FROM #__people WHERE personId = ?');
            $this->assertTrue(is_array($row), 'New record with provided ID must be properly saved');
        }
        
        
        $guy->delete();
        $guy3->delete();
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
    
    function testRestrRelations() {
        $sam = Sample::getInstance();
        $m = $sam->getSamplePersonMapper();
        $p = $m->loadByPersonId(3);
        $port = $p->getPortraitPersonPhoto();
        $this->assertTrue($port);
        $p->religionId = '';
        $p->portraitId = '';
        $p->intResetReferences();
        $port = $p->getPortraitPersonPhoto();
        $this->assertTrue(!$port, 'Ensure association object is cleared after '
            . 'I have changed the FK field value (multi-field FK)');
        $this->assertEqual($p->personId, 3, 'Field that participates in PK or other FK must not be touched'
            . ' and only part of FK must be cleared');
        $this->assertIdentical($p->portraitId, NULL, 'Unrestricted part of FK must be cleared');
        $this->assertIdentical($p->religionId, NULL, 'Unrestricted single-field FK\' field must be cleared');
        //var_dump($m->getFkFieldsData());
        
        $pm = $sam->getSamplePersonPostMapper();
        $post = $pm->loadById(1);
        $this->assertIsA($post->getPersonPhoto(), 'Sample_Person_Photo');
        $post->photoId = '';
        $post->intResetReferences();
        $this->assertNull($post->photoId);
        $this->assertNotNull($post->personId);

        $post = $pm->loadById(1);
        $post->personId = '';
        $post->intResetReferences();
        $this->assertNull($post->photoId);
        $this->assertNull($post->personId);
    }
    
    
    
}