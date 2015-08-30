<?php

class Child_Tree_Record_Base_Mapper extends Sample_Tree_Record_Mapper {

    var $recordClass = 'Child_Tree_Record'; 

    var $id = 'Child_Tree_Record_Mapper'; 
 
 
 
 
    /**
     * @return Child_Tree_Record 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Tree_Record_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Tree_Record 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Tree_Record 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Tree_Record 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Tree_Record 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Tree_Record 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    function getTitleFieldName() {
        return 'title';   
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Tree record',
                'pluralCaption' => 'Tree records',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Tree_Record 
     */
    function loadById ($id) {
        $res = parent::loadById($id);
        return $res;
    }
    
}

