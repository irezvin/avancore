<?php

class Sample_Tree_Adjacent_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id'; 

    var $recordClass = 'Sample_Tree_Adjacent'; 

    var $tableName = '#__tree_adjacent'; 

    var $id = 'Sample_Tree_Adjacent_Mapper'; 

    var $columnNames = array ( 'id', 'parentId', 'ordering', 'title', 'tag', ); 

    var $nullableSqlColumns = array ( 'parentId', 'tag', ); 

    var $defaults = array (
              'id' => NULL,
              'parentId' => NULL,
              'ordering' => '0',
              'title' => '',
              'tag' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'id';
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    /**
     * @return Sample_Tree_Adjacent 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Tree_Adjacent_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Tree_Adjacent 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Tree_Adjacent 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Tree_Adjacent 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Tree_Adjacent 
     */ 
    function loadSingleRecord($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount);
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
                  'singleCaption' => 'Tree adjacent',
                  'pluralCaption' => 'Tree adjacent',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
        
    protected function doGetUniqueIndexData() {
        return array (
              'PRIMARY' => array (
                  'id',
              ),
        );
    }
        
    /**
     * @return Sample_Tree_Adjacent 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
        
}

