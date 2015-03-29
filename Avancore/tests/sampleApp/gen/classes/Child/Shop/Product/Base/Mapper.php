<?php

class Child_Shop_Product_Base_Mapper extends Sample_Shop_Product_Mapper {

    var $recordClass = 'Child_Shop_Product'; 

    var $id = 'Child_Shop_Product_Mapper'; 
 
 
    protected function doGetCoreMixables() { 
        return Ac_Util::m(parent::doGetCoreMixables(), array (
            'publish' => array (
                'class' => 'Child_Publish_MapperMixable',
            ),
            'extraCode' => array (
                'class' => 'Child_Shop_Product_Extra_Code_MapperMixable',
            ),
            'note' => array (
                'class' => 'Child_Shop_Product_Note_MapperMixable',
            ),
        ));
    }
    
 
 
    /**
     * @return Child_Shop_Product 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Shop_Product_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Shop_Product 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Shop_Product 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Shop_Product 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Shop_Product 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Shop_Product 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    function getTitleFieldName() {
        return 'title';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_shopCategories' => array (
                'srcMapperClass' => 'Child_Shop_Product_Mapper',
                'destMapperClass' => 'Child_Shop_Category_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Shop product',
                'pluralCaption' => 'Shop products',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Shop_Product 
     */
    function loadById ($id) {
        $res = parent::loadById($id);
        return $res;
    }

    /**
     * @return Child_Shop_Product 
     */
    function loadByPubId ($pubId) {
        $res = parent::loadByPubId($pubId);
        return $res;
    }
    
    
}

