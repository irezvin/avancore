<?php

class Child_Religion_Base_Mapper extends Sample_Religion_Mapper {

    var $recordClass = 'Child_Religion'; 

    var $id = 'Child_Religion_Mapper'; 
 
 
 
 
    /**
     * @return Child_Religion 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Religion_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Religion 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Religion 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Religion 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Religion 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Religion 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    function getTitleFieldName() {
        return 'title';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_people' => array (
                'srcMapperClass' => 'Child_Religion_Mapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Religion',
                'pluralCaption' => 'Religion',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Religion 
     */
    function loadByReligionId ($religionId) {
        $res = parent::loadByReligionId($religionId);
        return $res;
    }

    /**
     * @return Child_Religion 
     */
    function loadByTitle ($title) {
        $res = parent::loadByTitle($title);
        return $res;
    }
    
}

