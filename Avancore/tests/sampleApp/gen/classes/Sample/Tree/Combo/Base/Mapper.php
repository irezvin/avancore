<?php

class Sample_Tree_Combo_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id'; 

    var $recordClass = 'Sample_Tree_Combo'; 

    var $tableName = '#__tree_combos'; 

    var $id = 'Sample_Tree_Combo_Mapper'; 

    var $columnNames = array ( 0 => 'id', 1 => 'leftCol', 2 => 'rightCol', 3 => 'parentId', 4 => 'ordering', 5 => 'title', 6 => 'tag', 7 => 'ignore', 8 => 'depth', ); 

    var $nullableSqlColumns = array ( 0 => 'parentId', 1 => 'tag', ); 

    var $defaults = array (
            'id' => NULL,
            'leftCol' => '0',
            'rightCol' => '1',
            'parentId' => NULL,
            'ordering' => '0',
            'title' => '',
            'tag' => NULL,
            'ignore' => '0',
            'depth' => '0',
        ); 
 
    
    protected $autoincFieldName = 'id';
    
    protected $askRelationsForDefaults = false;
    
 
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
        );
    }
    
    /**
     * @return Sample_Tree_Combo 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Tree_Combo_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Tree_Combo 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Tree_Combo 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Tree_Combo 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Tree_Combo 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Tree_Combo 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

        
    function getTitleFieldName() {
        return 'title';   
    }
            
    function getDefaultOrdering() {
        return 'ordering';
    }
                
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Tree combo',
                'pluralCaption' => 'Tree combos',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'id',
            ),
        );
    }
        
    /**
     * @return Sample_Tree_Combo 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
        
}

