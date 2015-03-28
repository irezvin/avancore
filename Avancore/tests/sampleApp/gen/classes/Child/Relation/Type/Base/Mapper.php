<?php

class Child_Relation_Type_Base_Mapper extends Sample_Relation_Type_Mapper {

    var $recordClass = 'Child_Relation_Type'; 

    var $id = 'Child_Relation_Type_Mapper'; 
 
 
 
 
    /**
     * @return Child_Relation_Type 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Relation_Type_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Relation_Type 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Relation_Type 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Relation_Type 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Relation_Type 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Relation_Type 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    function getTitleFieldName() {
        return 'title';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_relations' => array (
                'srcMapperClass' => 'Child_Relation_Type_Mapper',
                'destMapperClass' => 'Child_Relation_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Relation type',
                'pluralCaption' => 'Relation types',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Relation_Type 
     */
    function loadByRelationTypeId ($relationTypeId) {
        $res = parent::loadByRelationTypeId($relationTypeId);
        return $res;
    }
    
}

