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
        /*$sam->addMapper(Ac_Prototyped::factory(array('id' => 'people', 'tableName' => '#__people'), 'Ac_Model_Mapper'));
        $m = $sam->getMapper('people');
        $this->assertEqual($m->pk, 'personId', 'Auto-detection of primary key by Ac_Model_Mapper');
        $this->assertTrue(!array_diff($m->getColumnNames(), $this->peopleCols), 'Ac_Model_Mapper::getColumnNames()');;
        $this->assertSame($m, Ac_Model_Mapper::getMapper('people'));
        $rec = $m->loadRecord(3);
        $this->assertEqual($m->getAutoincFieldName(), 'personId', 'Ac_Model_Mapper::getAutoincFieldName()');*/
    }
    
    function testPersistence() {
        // TODO: people => person
        $sam = Sample::getInstance();
        $m = $sam->getSamplePeopleMapper();
        $guy = $sam->createSamplePeople();
        $guy->name = 'Guy';
        $guy->birthDate = '0000-00-00';
        $guy->store();
        
        $this->assertTrue($guy->isPersistent());
        $guy2 = $m->loadRecord($guy->getPrimaryKey());

        //var_dump($guy->getDataFields());
        //var_dump($guy2->getDataFields());
        
        $guy->delete();
        
    }
    
}