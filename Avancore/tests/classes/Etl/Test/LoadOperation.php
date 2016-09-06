<?php

class Etl_Test_LoadOperation extends Etl_Test_Class_Abstract {

    static $dbInstance;
    
    function setUp() {
        parent::setUp();
        self::$dbInstance = $this->db;
    }
    
    function testMatcher() {
        $pair = new DummyTablePair();
        $matcher = new Ac_Etl_Matcher(array(
            'sqlDb' => $this->db,
            'tablePair' => $pair,
            'ignore' => array(
                'id',
            ),
            'overrides' => array(
                'thisWillBeRelatedText' => 'relatedText1',
            )
        ));
        $cm = $matcher->getColMatches();
        if (!$this->assertEqual($cm, array(
            'name' => 'name',
            'description' => 'description',
            'thisWillBeRelatedText' => 'relatedText1',
            'somethingElse' => 'somethingElse'
        ))) var_dump($cm);
    }
    
    function testLoadOperation() {
        $im = new Ac_Etl_Import();
        $im->setDb($this->db);
        $im->addTables(array('items' => array(
            'sqlTableName' => '#__test_items_import',
        )));
        $im->setImportId(1);
        $loader = new Ac_Etl_Operation_Load(array(
            'tableId' => 'items',
            'id' => 'loader',
            'srcTableName' => '#__test_source_of_copy',
            'colMatches' => new Ac_Etl_Matcher(array(
                'ignore' => array('id'),
                'overrides' => array('relatedText1' => 'thisWillBeRelatedText'),
            ))
        ));
        
        $im->setOperations(array(
            'loader' => $loader
        ));
        
        $this->assertEqual($loader->getLeftDbName(), $this->db->getDbName());
        $this->assertEqual($loader->getRightDbName(), $this->db->getDbName());
        
        $im->process();
        $result = $this->db->fetchArray(
            "SELECT name, description, relatedText1 FROM #__test_items_import ORDER BY name"
        );
        
        
        if (!$this->assertEqual($result, array(
            array(
                'name' => 'aaa',
                'description' => 'aaa description',
                'relatedText1' => 'aaa related',
            ),
            array(
                'name' => 'bbb',
                'description' => 'bbb description',
                'relatedText1' => 'bbb related',
            ),
        ))) {
        var_dump($result);
        }
    }
    
}

class DummyTablePair implements Ac_Etl_I_TablePair {
    
    public function getLeftDbName() {
        return Etl_Test_LoadOperation::$dbInstance->getDbName();
    }
    
    public function getLeftTableName() {
        return '#__test_source_of_copy';
    }
    
    public function getLeftDbPrefix() {
        return Etl_Test_LoadOperation::$dbInstance->getDbPrefix();
    }
    
    public function getRightDbName() {
        return $this->getLeftDbName();
    }
    
    public function getRightDbPrefix() {
        return $this->getLeftDbPrefix();
    }
    
    public function getRightTableName() {
        return $this->getLeftTableName();
    }
    
}