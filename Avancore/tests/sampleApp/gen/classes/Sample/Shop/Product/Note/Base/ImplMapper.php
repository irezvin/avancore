<?php

class Sample_Shop_Product_Note_Base_ImplMapper extends Ac_Model_Mapper {

    var $pk = 'productId'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $tableName = '#__shop_product_notes'; 

    var $id = 'Sample_Shop_Product_Note_ImplMapper'; 

    var $columnNames = array ( 0 => 'productId', 1 => 'note', 2 => 'noteAuthorId', ); 

    var $nullableSqlColumns = array ( 0 => 'noteAuthorId', ); 

    var $defaults = array (
            'productId' => NULL,
            'note' => '',
            'noteAuthorId' => NULL,
        ); 
 
    protected $askRelationsForDefaults = false;
 
 
    function listSqlColumns() {
        return $this->columnNames;
    }
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), array (
            '_notePerson' => false,
            '_noteShopProductsCount' => false,
            '_noteShopProductsLoaded' => false,
        ));
    }
    
    /**
     * @return Sample_Shop_Product_Note 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Product_Note_ImplMapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Product_Note 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Product_Note 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Shop_Product_Note 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Shop_Product_Note 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Shop_Product_Note 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_notePerson' => array (
                'srcMapperClass' => 'Sample_Shop_Product_Note_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_notePerson',
                'srcCountVarName' => '_noteShopProductsCount',
                'srcLoadedVarName' => '_noteShopProductsLoaded',
                'destVarName' => '_noteShopProducts',
                'destCountVarName' => '_shopProductsCount',
                'destLoadedVarName' => '_shopProductsLoaded',
                'fieldLinks' => array (
                    'noteAuthorId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'Shop product note',
                'pluralCaption' => 'Shop product notes',
            ),
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
    return array (
            'PRIMARY' => array (
                0 => 'productId',
            ),
        );
    }

    /**
     * @return Sample_Shop_Product_Note 
     */
    function loadByProductId ($productId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('productId').' = '.$this->getDb()->q($productId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
}

