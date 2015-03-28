<?php

class Child_Perk_Base_Mapper extends Sample_Perk_Mapper {

    var $recordClass = 'Child_Perk'; 

    var $id = 'Child_Perk_Mapper'; 
 
 
 
 
    /**
     * @return Child_Perk 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Perk_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Perk 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Perk 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Perk 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Perk 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Perk 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_tags' => array (
                'srcMapperClass' => 'Child_Perk_Mapper',
                'destMapperClass' => 'Child_Tag_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Perk',
                'pluralCaption' => 'Perks',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Perk 
     */
    function loadByPerkId ($perkId) {
        $res = parent::loadByPerkId($perkId);
        return $res;
    }
    
}

