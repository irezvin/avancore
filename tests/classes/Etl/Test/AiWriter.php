<?php

class Etl_Test_AiWriter extends Etl_Test_Class_Abstract {

    function setUp() {
        parent::setUp();
        // Load initial data
        $this->resetAi('im_test_aipk');
        $this->db->query($this->db->insertStatement('#__test_aipk', array(
            array('id' => 1, 'content' => 'c1'), 
            array('id' => 2, 'content' => 'c2')
        ), true));
        $this->db->query($this->db->insertStatement('#__test_aipk_linked', array(
            array('masterId' => 1, 'name' => 'n1', 'otherContent' => 'oc1'), 
            array('masterId' => 2, 'name' => 'n2', 'otherContent' => 'oc2'),
        ), true));
        $this->db->query($this->db->insertStatement('#__test_aipk_import', array(
            array('name' => 'n3', 'content' => 'c3', 'otherContent' => 'oc3'),
            array('name' => 'n4', 'content' => 'c4', 'otherContent' => 'oc4'),
            array('name' => 'n2', 'content' => 'c2new', 'otherContent' => 'oc2new'),
        ), true));
    }
    
    function testAiWriter() {
        $im = $this->createEmptyImporter();
        $im->addTables(array(
            'aipk' => array('sqlTableName' => '#__test_aipk_import')
        ));
        $im->setOperations(array(
            'itemWriter' => array(
                'class' => 'Ac_Etl_Operation_Write',
                'tableId' => 'aipk',
                'targetSqlName' => '#__test_aipk',
                'nameMap' => array(
                    array('name', new Ac_Sql_Expression('linked.name')),
                ),
                'selectPrototype' => array(
                    'tables' => array(
                        'dataN' => array(
                            //'otherRequiredAliases' => array('linked'),
                        ),
                        'linked' => array(
                            'name' => '#__test_aipk_linked',
                            'joinsAlias' => 'dataN',  
                            'joinType' => 'LEFT JOIN',
                            'joinsNested' => true,
                            'joinsOn' => array('masterId' => 'id'),
                        ),
                    ),
                    'usedAliases' => array('linked'),
                ),
                'keyMap' => array(
                    'itemId' => 'id',
                ),
                'contentMap' => array(
                    'content' => 'content',
                ),
                'aiColumn' => 'id',
                'myAiColumn' => 'itemId',
                'draftColumn' => 'isDraft',
                'dontWriteNames' => true,
            ),
            'linkedWriter' => array(
                'class' => 'Ac_Etl_Operation_Write',
                'tableId' => 'aipk',
                'targetSqlName' => '#__test_aipk_linked',
                'nameMap' => array(
                    'itemId' => 'masterId',
                    'name' => 'name',
                ),
                'namesAreKeys' => true,
                'contentMap' => array(
                    'otherContent' => 'otherContent',
                ),
            ),
        ), true);
        

        $exception = null;
        try {
            $im->process();
        } catch (Exception $e) {
            $exception = $e;
        }
        //if ($exception) throw ($exception);
        
        if (!$this->assertIdentical($exception, null)) var_dump(''.$exception);        
        
        if (!$this->assertEqual($a = $this->db->fetchArray('SELECT * FROM #__test_aipk ORDER BY id ASC'), array(
            array('id' => 1, 'content' => 'c1'),
            array('id' => 2, 'content' => 'c2new'),
            array('id' => 3, 'content' => 'c3'),
            array('id' => 4, 'content' => 'c4'),
        ))) var_dump($a);
        
        if (!$this->assertEqual($a = $this->db->fetchArray('SELECT * FROM #__test_aipk_linked ORDER BY masterId ASC'), array(
            array('masterId' => 1, 'name' => 'n1', 'otherContent' => 'oc1'),
            array('masterId' => 2, 'name' => 'n2', 'otherContent' => 'oc2new'),
            array('masterId' => 3, 'name' => 'n3', 'otherContent' => 'oc3'),
            array('masterId' => 4, 'name' => 'n4', 'otherContent' => 'oc4'),
        ))) var_dump($a);
        
        if (!$this->assertEqual($a = $this->db->fetchArray('SELECT name, itemId, isDraft FROM #__test_aipk_import ORDER BY itemId ASC'), array(
            array('name' => 'n2', 'itemId' => '2', 'isDraft' => 0),
            array('name' => 'n3', 'itemId' => '3', 'isDraft' => 0),
            array('name' => 'n4', 'itemId' => '4', 'isDraft' => 0),
        ))) var_dump($a);
        
        $this->writeStats($im);

        $exception = false;
        try {
            $im->getOperation('itemWriter')->setDraftColumn(false);
            $im->getOperation('itemWriter')->process();
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertIsA($exception, 'Exception');
        
    }
    
}