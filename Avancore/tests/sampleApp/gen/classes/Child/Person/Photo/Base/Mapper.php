<?php

class Child_Person_Photo_Base_Mapper extends Sample_Person_Photo_Mapper {

    var $recordClass = 'Child_Person_Photo'; 

    var $id = 'Child_Person_Photo_Mapper'; 
 
 
 
 
    /**
     * @return Child_Person_Photo 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Person_Photo_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Person_Photo 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Person_Photo 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Person_Photo 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Person_Photo 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Person_Photo 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_person' => array (
                'srcMapperClass' => 'Child_Person_Photo_Mapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ),
            '_personAlbums' => array (
                'srcMapperClass' => 'Child_Person_Photo_Mapper',
                'destMapperClass' => 'Child_Person_Album_Mapper',
            ),
            '_portraitPerson' => array (
                'srcMapperClass' => 'Child_Person_Photo_Mapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ),
            '_personPosts' => array (
                'srcMapperClass' => 'Child_Person_Photo_Mapper',
                'destMapperClass' => 'Child_Person_Post_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Person photo',
                'pluralCaption' => 'Person photos',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Person_Photo 
     */
    function loadByPhotoId ($photoId) {
        $res = parent::loadByPhotoId($photoId);
        return $res;
    }
    
    
}

