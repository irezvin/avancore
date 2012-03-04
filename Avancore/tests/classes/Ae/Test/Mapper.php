<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ae_Test_Mapper extends Ae_Test_Base {

    var $peopleCols = array(
        'personId', 'name', 'gender', 'isSingle', 'birthDate', 'lastUpdatedDatetime', 'createdTs', 'sexualOrientationId'
    );
    
    function testMapper() {
        $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/classes/Sample/App.php';
        require_once($appClassFile);
        $sam = Sample_App::getInstance();
        $sam->addMapper(Ae_Autoparams::factory(array('id' => 'people', 'tableName' => '#__people'), 'Ae_Model_Mapper'));
        $m = $sam->getMapper('people');
        $this->assertEqual($m->pk, 'personId', 'Auto-detection of primary key by Ae_Model_Mapper');
        $this->assertTrue(!array_diff($m->getColumnNames(), $this->peopleCols), 'Ae_Model_Mapper::getColumnNames()');;
        $this->assertSame($m, Ae_Model_Mapper::getMapper('people'));
        $rec = $m->loadRecord(3);
        $this->assertEqual($m->getAutoincFieldName(), 'personId', 'Ae_Model_Mapper::getAutoincFieldName()');
    }
    
}