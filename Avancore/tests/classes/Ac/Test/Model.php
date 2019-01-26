<?php

class Ac_Test_Model extends Ac_Test_Base {
    
    protected $bootSampleApp = true;

    function _testGetPropertyInfoMemLeak() { // TODO: find proper test case
        $sam = Sample::getInstance()->getSampleShopProductMapper();
        $item0 = $sam->createRecord(); // load all necessary data
        $mem = memory_get_usage();
        $fields = array_merge($item0->listDataProperties(), array('authorPerson'));
        Ac_Prototyped::$countInstances = true;
        Ac_Debug::clear();
        gc_enable();
        for ($i = 0; $i < 1000; $i++) {
            $item = $sam->createRecord();
            $data = array();
            //foreach ($fields as $field) $data[$field] = $item->$field;
            $data = Ac_Accessor::getObjectProperty($item, $fields);
            $item->cleanupMembers();
            unset($item);
            unset($prop);
            gc_collect_cycles();
        }
        $mem1 = memory_get_usage();
        var_dump($mem, $mem1, $mem1 - $mem, Ac_Debug::$instanceStats);
        Ac_Prototyped::$countInstances = false;
    }

    function testPartialValidateRelations() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        $rm = Sample::getInstance()->getSampleReligionMapper();
        
        $pers = $pm->createRecord();
        $pers->religionId = '-1';
        $pers->check();
        $e = $pers->getErrors();
        $this->assertTrue(is_string(Ac_Util::getArrayByPath($e, array('religionId', 'valueList'))));
        $pers->religionId = '1';
        $pers->check(true);
        $e = $pers->getErrors();
        $this->assertTrue(is_null(Ac_Util::getArrayByPath($e, array('religionId', 'valueList'))));
       
        $vList = Ac_Model_Values::factoryWithProperty($pers->getPropertyInfo('religionId'));
        $this->assertTrue($vList instanceof Ac_Model_Values_Mapper);
        
        // order with best possible caching
        if ($vList instanceof Ac_Model_Values_Mapper) {
            $this->assertEqual(count($va = $vList->filterValuesArray(array(1, 2, 3, 999))), 3);
            $this->assertEqual(count($vList->filterValuesArray(array(1, 2, 3))), 3);
            $this->assertTrue($vList->check(1));
            $this->assertFalse($vList->check(999));
            $this->assertFalse(in_array(999, $va));
        }
        
        // reverse order
        $vList = Ac_Model_Values::factoryWithProperty($pers->getPropertyInfo('religionId'));
        $this->assertTrue($vList instanceof Ac_Model_Values_Mapper);
        if ($vList instanceof Ac_Model_Values_Mapper) {
            $this->assertTrue($vList->check(1));
            $this->assertEqual(count($vList->filterValuesArray(array(1, 2, 3))), 3);
            $this->assertEqual(count($va = $vList->filterValuesArray(array(1, 2, 3, 999))), 3);
            $this->assertFalse($vList->check(999));
            $this->assertFalse(in_array(999, $va));
        }
        
        // all in the cache
        $vList = Ac_Model_Values::factoryWithProperty($pers->getPropertyInfo('religionId'));
        $this->assertTrue($vList instanceof Ac_Model_Values_Mapper);
        if ($vList instanceof Ac_Model_Values_Mapper) {
            $rm->getAllRecords(); // will populate all records
            $this->assertTrue($vList->check(1));
            $this->assertEqual(count($vList->filterValuesArray(array(1, 2, 3))), 3);
            $this->assertEqual(count($va = $vList->filterValuesArray(array(1, 2, 3, 999))), 3);
            $this->assertFalse($vList->check(999));
            $this->assertFalse(in_array(999, $va));
        }
    }
    
    function testReset() {
        
        $pm = Sample::getInstance()->getSamplePersonMapper();
        
        $pers = $pm->createRecord();
        
        //$this->assertTrue(strtotime($pers->createdTs) > 0, 'Check for default for CURRENT_TIMESTAMP');
        
        $pers = $pm->loadByPersonId(4);
        $pers->listTags();
        $pers->getReligion();
        $pers->countTags();
        $pers->reset();
        $this->assertEqual($pers->isSingle, 0);
        $this->assertFalse($pers->isPersistent());
        $this->assertFalse($pers->hasFullPrimaryKey());
        $this->assertTrue($pers->_tags === false);
        $this->assertTrue($pers->_tagIds === false);
        $this->assertTrue($pers->_tagsLoaded === false);
        $this->assertTrue($pers->_tagsCount === false);
        $this->assertTrue($pers->_religion === false);
        
        $pers = $pm->loadByPersonId(4);
        $old = $pers->getDataFields();
        $pers->listTags();
        $pers->getReligion();
        $pers->countTags();
        $pers->name = 'Foobar';
        $pers->reset(true);
        $this->assertTrue($pers->isPersistent());
        $this->assertTrue($pers->hasFullPrimaryKey());
        $this->assertTrue(!$pers->getChanges());
        $this->assertEqual($pers->getDataFields(), $old);
    }
    
    function testGetNNRecPi() {
        
        $pm = Sample::getInstance()->getSamplePersonMapper();
        $pers = $pm->loadByPersonId(4);
        $this->assertIsA($pers->getPropertyInfo('religion[title]'), 'Ac_Model_Property');
        
        $rm = Sample::getInstance()->getSampleReligionMapper();
        $rel = $rm->loadByReligionId(1);
        $this->assertIsA($rel->getPropertyInfo('people[0][name]'), 'Ac_Model_Property');
        
        $pm = Sample::getInstance()->getSamplePersonMapper();
        $pers = $pm->loadByPersonId(4);
        $pers->listTags();
        
        $pi = $pers->getPropertyInfo('tags[0][title]');
        $this->assertIsA($pi, 'Ac_Model_Property');
    }
   
    function testPersistence() {
        $pm = Sample::getInstance()->getSamplePersonMapper();
        
        $pm->useRecordsCollection = false;
        $pm->reset();
        
        $personData = array('name' => 'testPerson', 'gender' => 'M', 'birthDate' => '2014-11-07');
        $religionData = array('title' => 'Pastafarian');
        $tagA = array('title' => 'The', 'titleM' => 'The Guy', 'titleF' => 'The Girl');
        $tagB = array('title' => 'A', 'titleM' => 'A Guy', 'titleF' => 'A Girl');
        
        // Let's clean up the mess from prev. TC
        $db = $this->getAeDb();
        $db->args($tagA['title'])->query("DELETE FROM #__tags WHERE title = ?");
        $db->args($tagB['title'])->query("DELETE FROM #__tags WHERE title = ?");
        $db->args($religionData['title'])->query("DELETE FROM #__religion WHERE title = ?");
        $db->args($personData['name'])->query("DELETE FROM #__people WHERE name = ?");
        
        $this->resetAi('#__people');
        $this->resetAi('#__tags');
        $this->resetAi('#__religion');
        
        $data = $personData;
        //$data['religion'] = $religionData;
        //$data['tags'] = array($tagA, $tagB);
        
        $person = $pm->createRecord();
        
        $person->bind($data);
        $oReligion = $person->createReligion();
        $oReligion->bind($religionData);
        
        $oTagA = $person->createTag();
        $oTagA->bind($tagA);
        
        $oTagB = $person->createTag();
        $oTagB->bind($tagB);
        
        $person->store();
        
        $this->assertTrue($oReligion->isPersistent());
        $this->assertTrue($oTagA->isPersistent());
        $this->assertTrue($oTagB->isPersistent());
        $this->assertTrue(!array_diff(array($oTagA->tagId, $oTagB->tagId), $person->getTagIds()));
        
    }
    
    function testDataMixable() {
        require_once(dirname(__FILE__).'/assets/DataWithMixable.php');
        $data = new DataMixin();
        $data->bar = 'val';
        $dMix = new DataMixable();
        $dMix->bar = 'mixableVal';
        $data->addMixable($dMix);
        $this->assertTrue($data->getField('bar'), 'val');
        $validHandlers = array('onCheck', 'onListProperties', 'onGetPropertiesInfo');
        $currHandlers = $dMix->listEventHandlerMethods();
        $this->assertTrue(
            !array_diff($currHandlers, $validHandlers) 
            && !array_diff(array_keys($currHandlers), $validHandlers), 
            'only event methods overridden in sub-class must be bound by Ac_Model_Mixable_Data'
        );
        $this->assertTrue(in_array('foo', $data->listFields()));
        $this->assertEqual($data->getPropertyInfo('foo')->caption, 'The Foo property');
        $data->foo = 'bar';
        $this->assertTrue(!$data->check() && strpos($data->getError(), 'Foo is not supposed to have the value "bar"') !== false);
    }
    
    function testObjectMixable() {
        require_once(dirname(__FILE__).'/assets/ObjectWithMixable.php');
        $pm = Sample::getInstance()->getSamplePersonMapper();
        $mix = new ObjectMixable();
        $mix->personId = 'foobar';
        $rec = $pm->loadRecord(3);
        $rec->addMixable($mix);
        $this->assertEqual($rec->getField('personId'), 3);
        $rec->load();
        $this->assertFalse($rec->store());
        $this->assertFalse($rec->canDelete());
        $this->assertEqual($mix->events, array('doAfterLoad', 'doBeforeSave', 'doOnCanDelete'));
    }
    
    function checkMapperModelMethods($mapper, $ignoreMixins = false) {
        $o = $mapper->getPrototype();
        $aa = $mapper->getAssociations();
        $nonMatching = array();
        $c = $ignoreMixins? 'method_exists' : array('Ac_Accessor', 'methodExists');
        foreach ($aa as $id => $assoc) {
            $mn = $assoc->getMethodNames();
            $mmk = preg_grep('/MapperMethod/', array_keys($mn));
            $omk = array_diff(array_keys($mn), $mmk);
            $mmn = array();
            $omn = array();
            foreach($mmk as $key) $mmn[] = $mn[$key];
            foreach($omk as $key) $omn[] = $mn[$key];
            foreach ($mmn as $method) 
                if (strlen($method) && !$c($mapper, $method)) $nonMatching[$id]['mapper'][] = $method;
            foreach ($omn as $method) 
                if (strlen($method) && !$c($o, $method)) $nonMatching[$id]['model'][] = $method;
        }
        if (!$this->assertTrue(!count($nonMatching), 'All mapper & model methods must be correctly set in Association')) {
            var_dump($nonMatching);
        }
    }
    
    function testAssoc() {
        $m = Sample::getInstance()->getSamplePersonMapper();
        $this->checkMapperModelMethods($m, true);
        $m->useRecordsCollection = false;
        $m->reset();
        $a = $m->loadByPersonId(3);
        $assoc = $a->getAssociationObject('religion');
        $assoc2 = clone $assoc;
        $this->assertTrue($assoc->getImmutable());
        $this->assertFalse($assoc2->getImmutable());
        if ($assoc instanceof Ac_Model_Association_One) {
            $do = $assoc->getDestObject($a);
            $this->assertSame($do, $a->getReligion());
        }
        
        $assoc2->setUseMapperMethods(false);
        $assoc2->setUseModelMethods(false);
        $do2 = $assoc2->getDestObject($a);
        $this->assertSame($do2, $a->getReligion());
        
        $a2 = $m->loadByPersonId(3);
        $this->assertTrue($assoc2->getCanLoadDestObjects());
        $do3 = $assoc2->getDestObject($a2);
        $this->assertEqual($do3->getDataFields(), $do->getDataFields());
    }
    
    function testMixinAssoc() {
        $sam = Sample::getInstance();
        
        $people = new Ac_Model_Mapper(array(
            'id' => 'People', 
            'tableName' => '#__people'
        ));
        $religion = new Ac_Model_Mapper(array(
            'id' => 'Religion',
            'tableName' => '#__religion'
        ));
        $sam->addComponent($people);
        $sam->addComponent($religion);
        
        $peopleReligion = new Ac_Model_Association_One(array(
            'id' => 'religion',
            'plural' => 'religion',
            'relation' => new Ac_Model_Relation(array(
                'srcMapper' => $people,
                'destMapper' => $religion,
                'fieldLinks' => array('religionId' => 'religionId'),
                'srcVarName' => '_religion',
                'destVarName' => '_people',
                'destLoadedVarName' => '_peopleLoaded',
                'destCountVarName' => '_peopleCount',
            )),
        ));
        
        $religionPeople = new Ac_Model_Association_Many(array(
            'id' => 'people',
            'single' => 'person',
            'relation' => new Ac_Model_Relation(array(
                'srcMapper' => $religion,
                'destMapper' => $people,
                'fieldLinks' => array('religionId' => 'religionId'),
                'srcVarName' => '_people',
                'srcLoadedVarName' => '_peopleLoaded',
                'srcCountVarName' => '_peopleCount',
                'destVarName' => '_religion',
            )),
        ));
        
        $people->addMixable($peopleReligion);
        $religion->addMixable($religionPeople);
        
        $this->checkMapperModelMethods($people);
        $this->checkMapperModelMethods($religion);
        
        $pers = $people->createRecord();
        $rel = $religion->createRecord(); 
        
        $this->assertSame($pers->_religion, false);
        $this->assertSame($rel->_people, false);
        $this->assertSame($rel->_peopleLoaded, false);
        $this->assertSame($rel->_peopleCount, false);
        
        $p3 = $people->loadRecord(3);
        $r = $p3->getReligion();
        if ($this->assertIsA($r, 'Ac_Model_Object')) {
            $this->assertSame($r->getMapper(), $religion);
        }
        $list = $r->listPeople();
        $this->assertTrue(is_array($list));
        $this->assertTrue(count($list) > 1);
        $this->assertTrue($r->isPeopleLoaded());
        $this->assertSame($r->getPerson(0), $p3);
        
        $tags = new Ac_Model_Mapper(array(
            'id' => 'Tags',
            'tableName' => '#__tags'
        ));
        $sam->addComponent($tags);
        
        $peopleTags = new Ac_Model_Association_ManyToMany(array(
            'id' => 'tags',
            'relation' => new Ac_Model_Relation(array(
                'srcMapper' => $people,
                'destMapper' => $tags,
                'midTableName' => '#__people_tags',
                'fieldLinks' => array(
                    'personId' => 'idOfPerson'
                ),
                'fieldLinks2' => array(
                    'idOfTag' => 'tagId',
                ),
                'srcVarName' => '_tags',
                'srcNNIdsVarName' => '_tagIds',
                'srcCountVarName' => '_tagsCount',
                'srcLoadedVarName' => '_tagsLoaded',
                
                'destVarName' => '_people',
                'destNNIdsVarName' => '_personIds',
                'destCountVarName' => '_peopleCount',
                'destLoadedVarName' => '_peopleLoaded',
            )),
        ));
        
        $tagsPeople = new Ac_Model_Association_ManyToMany(array(
            'id' => 'people',
            'single' => 'person',
            'relation' => new Ac_Model_Relation(array(
                'destMapper' => $people,
                'srcMapper' => $tags,
                'midTableName' => '#__people_tags',
                'fieldLinks' => array(
                    'tagId' => 'idOfTag',
                ),
                'fieldLinks2' => array(
                    'idOfPerson' => 'personId',
                ),
                'destVarName' => '_tags',
                'destNNIdsVarName' => '_tagIds',
                'destCountVarName' => '_tagsCount',
                'destLoadedVarName' => '_tagsLoaded',
                
                'srcVarName' => '_people',
                'srcNNIdsVarName' => '_personIds',
                'srcCountVarName' => '_peopleCount',
                'srcLoadedVarName' => '_peopleLoaded',
            )),
        ));
        
        $people->addMixable($peopleTags);
        $tags->addMixable($tagsPeople);
        
        $this->checkMapperModelMethods($people);
        $this->checkMapperModelMethods($tags);
        
        $p4 = $people->loadRecord(4);
        $this->assertTrue(is_array($ids = $p4->getTagIds()));
        $this->assertTrue(is_array($tagsList = $p4->listTags()));
        $this->assertEqual(count($ids), count($tagsList));
        if (count($tagsList)) {
            $found = false;
            $first = $p4->getTag(0);
            foreach ($first->listPeople() as $i) {
                if ($first->getPerson($i) === $p4) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found);
        }
        
        $this->assertTrue($p4->hasProperty('religion'));
        $this->assertTrue($p4->hasProperty('religion[title]'));
        
        // TODO: fix WTFy inconsistency: Cg creates plural name of property (tags, tags[0][title]) that
        // causes errors, Assoc creates singular (but unconvenient) name (tag, tag[0][title])
        
        $this->assertTrue($p4->hasProperty('tag'));
        $this->assertTrue($p4->hasProperty('tag[0][titleF]'));
        
        $s = $tags->createSqlSelect();
        $s->useAlias('person[religion]');
        $s->distinct = true;
        $s->columns = '`person[religion]`.title';
        $stmt = $s->getStatement();
        $db = $s->getDb();
        $this->assertTrue(strpos($stmt, $db->n('#__people_tags')) !== false);
        $this->assertTrue(strpos($stmt, $db->n('#__people')) !== false);
        $this->assertTrue(strpos($stmt, $db->n('#__religion')) !== false);
        
        // TODO: check un-binding of associations
        
    }
    
}
