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
        $m->useRecordsCollection = true;
        $rec = $m->loadRecord(3);
        $this->assertEqual(Ac_Accessor::getObjectProperty($rec, 'personId'), 3, 'Ensure Ac_Accessor retrieves values from barebones Ac_Model_Object');
        $this->assertTrue(in_array('personId', $m->listGeneratedFields()), 'Ac_Model_Mapper::listGeneratedFields()');
        
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
        $db = $this->getAeDb();
        
        $this->resetAi('#__people');
        
        $guy = $sam->createSamplePerson();
        $guy->name = 'Guy';
        $guy->birthDate = '0000-00-00';
        
        $this->assertTrue($guy->store(), 'Record is successfully stored');
        $this->assertTrue($guy->isPersistent(), 'Record is reported as persistent after save');
        $this->assertTrue($guy->personId > 0, 'PK assigned to the record after save');
        $name = $db->args($guy->personId)->fetchValue('SELECT name FROM #__people WHERE personId = ?');
        $this->assertTrue($name == $guy->name, 'Record data is in the database');
        
        $guy->name = 'Guy with changed name';
        $this->assertTrue($guy->store(), 'Record is successfully changed');
        $name2 = $db->args($guy->personId)->fetchValue('SELECT name FROM #__people WHERE personId = ?');
        $this->assertTrue($name2 == $guy->name, 'Changed record data is in the database');
        
        // test load
        $guy2 = $sam->createSamplePerson();
        $this->assertFalse($guy2->load(-10), 'After loading object with non-existent id load() must return false');
        $this->assertTrue(!$guy2->isPersistent(), 'After failed load object->isPersistent() must return false');
        $this->assertTrue($guy2->load($guy->personId), 'load() of existent record returns true');
        $this->assertTrue($guy2->isPersistent(), 'After successful load object->isPersistent() must return true');
        
        // test delete
        $this->assertTrue($guy->delete(), 'Record is correctly deleted');
        $n = $this->getAeDb()->args($guy->personId)->fetchValue('SELECT COUNT(*) FROM #__people WHERE personId = ?');
        $this->assertEqual($n, 0, 'No record data in DB after deletion');
        
        // test PK tracking
        $db->query("DELETE FROM #__tags WHERE title = 'TestTag'");
        $maxId = $this->resetAi('#__tags') - 1;
        $id = $maxId + 6;
        $tag = $sam->createSampleTag();
        
        $tag->bind(array('title' => 'TestTag', 'titleM' => 'TestTagM', 'titleF' => 'TestTagF'));
        
        $tag->tagId = $maxId;
        $this->assertFalse($tag->check(true), 'Conflicting match when trying to assign ID of existing object');
        $err = $tag->getErrors();
        $this->assertTrue(isset($err['tagId']['index']), 'found conflict by PK index when trying to assign ID of existing object');
        
        $tag->tagId = $id;
        $tag->check(true);
        
        $this->assertFalse($tag->isPersistent(), 'Record is NOT existent when it is created with pre-provided ID');
        if ($this->assertTrue($tag->store(), 'Record with pre-provided ID is correctly saved')) {
            $this->assertTrue($tag->isPersistent());
            $row = $db->args($id)->fetchRow('SELECT * FROM #__tags WHERE tagId = ?');
            $this->assertTrue(is_array($row) && $row['title'] == 'TestTag', 
                'New record with provided ID must be properly saved and row in the DB exists');
            
            $newId = $id + 2;
            $tag->tagId = $newId;
            $this->assertTrue($tag->store(), 'Record is saved after ID is changed');
            
            $this->assertTrue($tag->check(true));
            
            $row = $db->args($newId)->fetchRow('SELECT * FROM #__tags WHERE tagId = ?');
            $this->assertTrue(is_array($row) && $row['title'] == 'TestTag', 
                'Record with changed ID exists in DB');
            $this->assertTrue($db->args($id)->fetchValue('SELECT COUNT(*) FROM #__tags WHERE tagId = ?') == 0, 
                'Record with old ID is no more in DB');
            $this->assertEqual($tag->getIdentifier(), $newId, 'The object has new identifier assigned after it is saved with different PK');
                        
            $tag3 = $tag->copy();
            $tag3->tagId = $tag->tagId;
            $this->assertFalse($tag3->check(), 'Conflicting matches should be found');
            $err = $tag3->getErrors();
            $this->assertTrue(isset($err['tagId']['index']), 'found conflict by PK index');
            $this->assertTrue(isset($err['title']['index']), 'found conflict by title index');
            
            $newId2 = $id + 4;
            $tag->tagId = $newId2;
            $this->assertTrue($tag->delete(), 'Record is deleted after ID is changed, but before it is saved');
            $this->assertTrue($db->args($newId)->fetchValue('SELECT COUNT(*) FROM #__tags WHERE tagId = ?') == 0, 'Record is correctly deleted using old ID');
            $this->assertFalse($tag->isPersistent(), "The record is not persistent after it had been deleted");
        }
        
        if ($tag->isPersistent()) $tag->delete();
        
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
    
    function testPresence() {
        $app = Sample::getInstance();
        $m = $app->getSamplePersonMapper();
        $m->reset();
        $stor = $m->getStorage();
        $rec = $m->createRecord();
        $rec->name = 'Илья';
        $rec->birthDate = '1981-12-23';
        $rec->isSingle = 0;
        $allRec = $m->getAllRecords();
        $pres = $stor->checkRecordPresence($rec, $idx = array(
            'idxName' => 'name', 
            'idxBirth' => 'birthDate', 
            'idxSingle' => 'isSingle',
            'idxNameSingle' => array('name', 'isSingle')
        ));
        shuffle($allRec);
        $pres2 = $m->findByIndicesInArray($rec, $allRec, $idx, false);
        $isEqual = true;
        foreach ($pres as $k => $item) {
            if (!isset($pres2[$k]) || array_diff($item, $pres2[$k]) || array_diff($pres2[$k], $item)) {
                $isEqual = false;
            }
        }
        $k1 = array_keys($pres);
        $k2 = array_keys($pres2);
        sort($k1);
        sort($k2);
        $this->assertEqual($k1, $k2);
        $this->assertTrue($k1 == $k2 && $isEqual, 'same results should be returned from Ac_Model_Storage::checkRecordPresence, Ac_Model_Mapper::findIndicesInArray');
        $id = $m->getIdentifierOfObject($rec);
        foreach (array_keys($idx) as $idxName) {
            $proper[$idxName] = array($id);
        }
        $this->assertEqual($m->findByIndicesInArray($rec, array($rec), $idx, false), $proper, "Object should match to itself");
    }    
    
}