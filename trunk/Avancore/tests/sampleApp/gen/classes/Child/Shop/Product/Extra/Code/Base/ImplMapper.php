<?php

class Child_Shop_Product_Extra_Code_Base_ImplMapper extends Sample_Shop_Product_Extra_Code_ImplMapper {

    var $recordClass = 'Ac_Model_Record'; 

    var $id = 'Child_Shop_Product_Extra_Code_ImplMapper'; 
 
 
 
 
    /**
     * @return Child_Shop_Product_Extra_Code 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Shop_Product_Extra_Code_ImplMapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Shop_Product_Extra_Code 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Shop_Product_Extra_Code 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Shop_Product_Extra_Code 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Shop_Product_Extra_Code 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Shop_Product_Extra_Code 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_extraCodeExtraCodePerson' => array (
                'srcMapperClass' => 'Child_Shop_Product_Extra_Code_ImplMapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Shop product extra code',
                'pluralCaption' => 'Shop product extra codes',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Shop_Product_Extra_Code 
     */
    function loadByProductId ($productId) {
        $res = parent::loadByProductId($productId);
        return $res;
    }
    
    
}

