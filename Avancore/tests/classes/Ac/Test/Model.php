<?php

class Ac_Test_Model extends Ac_Test_Base {
    
    protected $bootSampleApp = true;
    
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