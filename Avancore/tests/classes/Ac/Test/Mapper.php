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

        $this->assertNull($m->loadRecord(-10), 'Attempt to load non-existent record returns NULL');
        
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
        
        $m->reset();
        $m->useRecordsCollection = true;
        $m->trackNewRecords = true;
        
        $idxData = array('idxName' => 'name');
        
        $idC = 3;
        
        $name3 = $this->getAeDb()->args($idC)->fetchValue('SELECT name FROM #__people WHERE personId = ?');
        
        $a = $m->createRecord();
        $a->bind(array('name' => $name3));
        $idA = $m->getIdentifierOfObject($a);
        
        $b = $m->createRecord();
        $b->bind(array('name' => $name3));
        $idB = $m->getIdentifierOfObject($b);
        
        $this->assertArraysMatch(
            $m->checkRecordPresence($a, true, array(), $idxData, false, Ac_Model_Mapper::PRESENCE_STORAGE),
            array('idxName' => array($idC)),
            'Ac_Model_Mapper::PRESENCE_STORAGE works without $withNewRecords',
            'sort'
        );
        
        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, false, array(false), $idxData, true, Ac_Model_Mapper::PRESENCE_STORAGE)),
            ($proper = array('idxName' => array($idC, $idA, $idB))),
            'Ac_Model_Mapper::PRESENCE_STORAGE works with $withNewRecords',
            'sort'
        )) var_dump($foo, $proper);
        
        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, false, array(false), $idxData, true, Ac_Model_Mapper::PRESENCE_MEMORY)),
            ($proper = array('idxName' => array($idA, $idB))),
            'Ac_Model_Mapper::PRESENCE_MEMORY works with $withNewRecords',
            'sort'
        )) var_dump($foo, $proper);

        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, true, array(false), $idxData, true, Ac_Model_Mapper::PRESENCE_MEMORY)),
            ($proper = array('idxName' => array($idB))),
            'Ac_Model_Mapper::PRESENCE_MEMORY works with dontReturnOwnIdentifier === true',
            'sort'
        )) var_dump($foo, $proper);
        
        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, true, array(false), $idxData, false, Ac_Model_Mapper::PRESENCE_MEMORY)),
            ($proper = array()),
            'Ac_Model_Mapper::PRESENCE_MEMORY works with dontReturnOwnIdentifier === true, withNewRecords === false',
            'sort'
        )) var_dump($foo, $proper);
        
        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, true, array(false), $idxData, false, Ac_Model_Mapper::PRESENCE_PARTIAL)),
            ($proper = array('idxName' => array($idC))),
            'Ac_Model_Mapper::PRESENCE_PARTIAL works with dontReturnOwnIdentifier === true, withNewRecords === false',
            'sort'
        )) var_dump($foo, $proper);
        
        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, true, array(false), $idxData, true, Ac_Model_Mapper::PRESENCE_PARTIAL)),
            ($proper = array('idxName' => array($idB))),
            'Ac_Model_Mapper::PRESENCE_PARTIAL works with dontReturnOwnIdentifier === true, withNewRecords === true, '
            . 'does\'t return persistent object ID',
            'sort'
        )) var_dump($foo, $proper);
        
        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, true, array(false), $idxData, true, Ac_Model_Mapper::PRESENCE_FULL)),
            ($proper = array('idxName' => array($idB, $idC))),
            'Ac_Model_Mapper::PRESENCE_FULL works with dontReturnOwnIdentifier === true, withNewRecords === true, '
            . 'does\'t return persistent object ID',
            'sort'
        )) var_dump($foo, $proper);
        
        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, true, array(false), $idxData, false, Ac_Model_Mapper::PRESENCE_FULL)),
            ($proper = array('idxName' => array($idC))),
            'Ac_Model_Mapper::PRESENCE_FULL works with dontReturnOwnIdentifier === true, withNewRecords === true, ',
            'sort'
        )) var_dump($foo, $proper);

        $d = $m->loadRecord(4);
        $d->name = $name3;
        $idD = $m->getIdentifierOfObject($d);
        
        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, true, array(false), $idxData, true, Ac_Model_Mapper::PRESENCE_SMART)),
            ($proper = array('idxName' => array($idB, $idD))),
            'Ac_Model_Mapper::PRESENCE_SMART works',
            'sort'
        )) var_dump($foo, $proper);
        
        $d->forget();
        
        $m->getAllRecords();
        $this->assertTrue($m->areAllRecordsLoaded());
        
        if (!$this->assertArraysMatch(
            ($foo = $m->checkRecordPresence($a, true, array(false), $idxData, true, Ac_Model_Mapper::PRESENCE_SMART_FULL)),
            ($proper = array('idxName' => array($idB, $idC))),
            'Ac_Model_Mapper::PRESENCE_SMART works',
            'sort'
        )) var_dump($foo, $proper);
            
    }
    
    function testSearch() {
        $p = $this->getSampleApp()->getSamplePersonMapper();
        $p->useRecordsCollection = true;
        $p->reset();
        $p->getAllRecords();
        
        $res = $p->aFind($options = array(
            'query' => array(
                'personId' => 3
            )
        ));
        if (!$this->assertArraysMatch($proper = array(
            3 => array(
                'personId' => 3
            ),
        ), $actual = Ac_Debug::dr($res), 'Best case: find-by-PK works')) {
            var_dump(compact('options', 'proper', 'actual'));
        } else {
            $this->assertTrue(count($res) === count($proper), 'find-by-PK returns only one result');
        }
        $res = $p->aFind($options = array(
            'query' => array(
                'personId' => array(3, 4)
            )
        ));
        
        if (!$this->assertArraysMatch($proper = array(
            3 => array(
                'personId' => 3,
            ),
            4 => array(
                'personId' => 4,
            ),
        ), $actual = Ac_Debug::dr($res), 'Best case: find-by-PK works for several PKs')) {
            var_dump(compact('options', 'proper', 'actual'));
        } else {
            $this->assertTrue(count($res) === count($proper), 'find-by-PK returns only one result');
        }
        
        $res = $p->aFind($options = array(
            'query' => array(
                'personId' => 3,
            ),
            'offset' => 1,
        ));
        if (!$this->assertTrue(!count($actual = Ac_Debug::dr($res)), 'PK + offset = 0 results')) {
            var_dump(compact('options', 'actual'));
        }
        
        $res = $p->aFind($options = array(
            'query' => array(
                'name' => 'Илья',
            )
        ));
        if (!$this->assertArraysMatch($proper = array(
            3 => array(
                'personId' => 3,
                'name' => $options['query']['name'],
            ),
        ), $actual = Ac_Debug::dr($res), 'Best case: find-by-uindex works')) {
            var_dump(compact('options', 'proper', 'actual'));
        } else {
            $this->assertTrue(count($res) === count($proper), 'find-by-uindex returns only one result');
        }
        
        $res = $p->aFind($options = array(
            'query' => array(
                'name' => 'Илья',
                'personId' => 4,
            )
        ));
        if (!$this->assertTrue(!count($actual = Ac_Debug::dr($res)), 'Confl. PK + uindex = 0 results')) {
            var_dump(compact('options', 'actual'));
        }
        
        $res = $p->aFind($options = array(
            'query' => array(
                'name' => 'Илья',
            ),
            'offset' => 1,
        ));
        if (!$this->assertTrue(!count($actual = Ac_Debug::dr($res)), 'uindex + offset = 0 results')) {
            var_dump(compact('options', 'actual'));
        }

        try {
            $ex = null;
            $res = $p->aFind($options = array(
                'query' => array(
                    'name' => 'Илья',
                    'wtfNoSuchField' => 123,
                ),
            ));
        } catch (Exception $ex) {
        }
        $this->assertTrue($ex && preg_match('/Criterion.*is unknown/', $ex->getMessage()), 'Unknown criterion in query produces an exception');
        
        // let's find something using the storage
         
        $res = $p->aFind($options = array(
            'query' => array(
                'name' => 'Таня',
                'gender' => 'F',
                'birthDate' => '1981-12-23',
            )
        ));
        if (!$this->assertArraysMatch($proper = array(
            4 => array(
                'personId' => 4,
                'name' => $options['query']['name'],
                'gender' => $options['query']['gender'],
                'birthDate' => $options['query']['birthDate'],
            ),
        ), $actual = Ac_Debug::dr($res), 'Find-no-SqlSelect-works')) {
            var_dump(compact('options', 'proper', 'actual'));
        } else {
            $this->assertTrue(count($res) === count($proper), 'proper ## of results');
        }
         
        $res = $p->aFind($options = array(
            'query' => array(
                'birthYear' => '1981',
            )
        ));
        if (!$this->assertArraysMatch($proper = array(
            4 => array(
                'name' => 'Таня',
            ),
            6 => array(
                'name' => 'Ян',
            ),
            7 => array(
                'name' => 'Оля',
            ),
        ), $actual = Ac_Debug::dr($res), 'Find-by-SqlSelect-Part works')) {
            var_dump(compact('options', 'proper', 'actual'));
        } else {
            $this->assertTrue(count($res) === count($proper), 'proper ## of results');
        }
         
        $res = $p->aFind($options = array(
            'query' => array(
                'birthYear' => '1981',
            ),
            'sort' => array('gender' => false, 'birthDate'),
        ));
        if (
            !$this->assertArraysMatch($proper = array(
                6 => array( 
                    'gender' => 'M', 
                    'birthDate' => '1981-09-21',
                ),
                7 => array(
                    'gender' => 'F', 
                    'birthDate' => '1981-09-08',
                ),
                4 => array(
                    'gender' => 'F', 
                    'birthDate' => '1981-12-23',
                ),
            ), $actual = Ac_Debug::dr($res), 'Find-by-SqlSelect-Part works, order for SqlSelect works too') 
            ||
            !$this->assertTrue(array_keys($res) == array_keys($proper), 'same ## and order of results')) {
            var_dump(compact('options', 'proper', 'actual'));
        }
         
        $res = $p->aFind($options = array(
            'query' => array(
                'tags[title]' => 'Ум',
            ),
        ));
        if (
            !$this->assertArraysMatch($proper = array(
                4 => array(
                ),
                6 => array( 
                ),
            ), $actual = Ac_Debug::dr($res), 'Find-by-SqlSelect-With-aliases work') 
            ||
            !$this->assertTrue(count($res) == count($proper), 'same ## and order of results')) {
            var_dump(compact('options', 'proper', 'actual'));
        }
       
        
    }
    
}