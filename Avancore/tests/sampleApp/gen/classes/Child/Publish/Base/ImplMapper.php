<?php

class Child_Publish_Base_ImplMapper extends Sample_Publish_ImplMapper {

    var $recordClass = 'Ac_Model_Record'; 

    var $id = 'Child_Publish_ImplMapper'; 
 
 
 
 
    /**
     * @return Child_Publish 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Publish_ImplMapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Publish 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Publish 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Publish 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Publish 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Publish 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_authorPerson' => array (
                'srcMapperClass' => 'Child_Publish_ImplMapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ),
            '_editorPerson' => array (
                'srcMapperClass' => 'Child_Publish_ImplMapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Publish',
                'pluralCaption' => 'Publish',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Publish 
     */
    function loadById ($id) {
        $res = parent::loadById($id);
        return $res;
    }

    /**
     * @return Child_Publish 
     */
    function loadByPubChannelId ($pubChannelId) {
        $res = parent::loadByPubChannelId($pubChannelId);
        return $res;
    }
    
}

