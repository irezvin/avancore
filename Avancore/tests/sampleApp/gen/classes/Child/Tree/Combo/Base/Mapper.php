<?php

class Child_Tree_Combo_Base_Mapper extends Sample_Tree_Combo_Mapper {

    var $recordClass = 'Child_Tree_Combo'; 

    var $id = 'Child_Tree_Combo_Mapper'; 
 
 
 
 
    /**
     * @return Child_Tree_Combo 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Tree_Combo_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Tree_Combo 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Tree_Combo 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Tree_Combo 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Tree_Combo 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Tree_Combo 
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
    

    /**
     * @return Child_Tree_Combo 
     */
    function loadById ($id) {
        $res = parent::loadById($id);
        return $res;
    }
    
}

