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
   
    
}