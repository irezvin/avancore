<?php

class Child_Tag_Base_Mapper extends Sample_Tag_Mapper {

    var $recordClass = 'Child_Tag'; 

    var $id = 'Child_Tag_Mapper'; 
 
 
 
 
    /**
     * @return Child_Tag 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Tag_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Tag 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Tag 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Tag 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Tag 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Tag 
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
                'srcMapperClass' => 'Child_Tag_Mapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ),
            '_perks' => array (
                'srcMapperClass' => 'Child_Tag_Mapper',
                'destMapperClass' => 'Child_Perk_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Tag',
                'pluralCaption' => 'Tags',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Tag 
     */
    function loadByTagId ($tagId) {
        $res = parent::loadByTagId($tagId);
        return $res;
    }

    /**
     * @return Child_Tag 
     */
    function loadByTitle ($title) {
        $res = parent::loadByTitle($title);
        return $res;
    }
    
    
}

