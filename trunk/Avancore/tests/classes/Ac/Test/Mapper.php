<?php

require_once('testsStartup.php');
require_once('simpletest/unit_tester.php');

class Ac_Test_Mapper extends Ac_Test_Base {

    var $peopleCols = array(
        'personId', 'name', 'gender', 'isSingle', 'birthDate', 'lastUpdatedDatetime', 'createdTs', 'sexualOrientationId'
    );
    
    function testMapper() {
        $appClassFile = dirname(dirname(dirname(dirname(__FILE__)))).'/sampleApp/classes/Sample/App.php';
        require_once($appClassFile);
        $sam = Sample_App::getInstance();
        $sam->addMapper(Ac_Autoparams::factory(array('id' => 'people', 'tableName' => '#__people'), 'Ac_Model_Mapper'));
        $m = $sam->getMapper('people');
        $this->assertEqual($m->pk, 'personId', 'Auto-detection of primary key by Ac_Model_Mapper');
        $this->assertTrue(!array_diff($m->getColumnNames(), $this->peopleCols), 'Ac_Model_Mapper::getColumnNames()');;
        $this->assertSame($m, Ac_Model_Mapper::getMapper('people'));
        $rec = $m->loadRecord(3);
        $this->assertEqual($m->getAutoincFieldName(), 'personId', 'Ac_Model_Mapper::getAutoincFieldName()');
    }
    
}