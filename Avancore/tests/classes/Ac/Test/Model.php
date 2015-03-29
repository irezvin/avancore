<?php

class Ac_Test_Model extends Ac_Test_Base {
    
    protected $bootSampleApp = true;

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
       
        $vList = Ac_Model_Values::factoryWithFormOptions($pers, 'religionId');
        $this->assertTrue($vList instanceof Ac_Model_Values_Records);
        
        // order with best possible caching
        if ($vList instanceof Ac_Model_Values_Records) {
            $this->assertEqual(count($va = $vList->filterValuesArray(array(1, 2, 3, 999))), 3);
            $this->assertEqual(count($vList->filterValuesArray(array(1, 2, 3))), 3);
            $this->assertTrue($vList->check(1));
            $this->assertFalse($vList->check(999));
            $this->assertFalse(in_array(999, $va));
        }
        
        // reverse order
        $vList = Ac_Model_Values::factoryWithFormOptions($pers, 'religionId');
        $this->assertTrue($vList instanceof Ac_Model_Values_Records);
        if ($vList instanceof Ac_Model_Values_Records) {
            $this->assertTrue($vList->check(1));
            $this->assertEqual(count($vList->filterValuesArray(array(1, 2, 3))), 3);
            $this->assertEqual(count($va = $vList->filterValuesArray(array(1, 2, 3, 999))), 3);
            $this->assertFalse($vList->check(999));
            $this->assertFalse(in_array(999, $va));
        }
        
        // all in the cache
        $vList = Ac_Model_Values::factoryWithFormOptions($pers, 'religionId');
        $this->assertTrue($vList instanceof Ac_Model_Values_Records);
        if ($vList instanceof Ac_Model_Values_Records) {
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
        $data = new Ac_Model_Data();
        $dMix = new DataMixable();
        $data->addMixable($dMix);
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
        $rec = $pm->loadRecord(3);
        $rec->addMixable($mix);
        $rec->load();
        $this->assertFalse($rec->store());
        $this->assertFalse($rec->canDelete());
        $this->assertEqual($mix->events, array('doAfterLoad', 'doBeforeSave', 'doOnCanDelete'));
    }
    
}
