<?php

class Child_Person_Base_Mapper extends Sample_Person_Mapper {

    var $recordClass = 'Child_Person'; 

    var $id = 'Child_Person_Mapper'; 
 
 
 
 
    /**
     * @return Child_Person 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Person_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Person 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Person 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Person 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Person 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Person 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_portraitPersonPhoto' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Person_Photo_Mapper',
            ),
            '_religion' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Religion_Mapper',
            ),
            '_tags' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Tag_Mapper',
            ),
            '_personAlbums' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Person_Album_Mapper',
            ),
            '_personPhotos' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Person_Photo_Mapper',
            ),
            '_personPosts' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Person_Post_Mapper',
            ),
            '_incomingRelations' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Relation_Mapper',
                'destVarName' => '_incomingPerson',
            ),
            '_outgoingRelations' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Relation_Mapper',
                'destVarName' => '_outgoingPerson',
            ),
            '_extraCodeShopProducts' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Shop_Product_Mapper',
            ),
            '_noteShopProducts' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Shop_Product_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'People',
                'pluralCaption' => 'People',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Person 
     */
    function loadByPersonId ($personId) {
        $res = parent::loadByPersonId($personId);
        return $res;
    }
    
}

