<?php

class Ac_Test_Model extends Ac_Test_Base {
    
    protected $bootSampleApp = true;
    
    function testReset() {
        
        $pm = Sample::getInstance()->getSamplePersonMapper();
        
        $pers = $pm->createRecord();
        
        $this->assertTrue(strtotime($pers->createdTs) > 0, 'Check for default for CURRENT_TIMESTAMP');
        
        $pers = $pm->loadByPersonId(4);
        $pers->listTags();
        $pers->getReligion();
        $pers->countTags();
        $pers->reset();
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