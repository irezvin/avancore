<?php

class Sample_Shop_Product_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id'; 

    var $recordClass = 'Sample_Shop_Product'; 

    var $tableName = '#__shop_products'; 

    var $id = 'Sample_Shop_Product_Mapper'; 

    var $columnNames = array ( 0 => 'id', 1 => 'sku', 2 => 'title', 3 => 'metaId', 4 => 'published', ); 

    var $nullableSqlColumns = array ( 0 => 'metaId', ); 

    var $defaults = array (
            'id' => NULL,
            'sku' => '',
            'title' => '',
            'metaId' => NULL,
            'published' => '0',
        ); 
 
    
    protected $autoincFieldName = 'id';
    
    protected $askRelationsForDefaults = false;
    
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
        );
    }
    
    /**
     * @return Sample_Shop_Product 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Product_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Product 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Product 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Shop_Product 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Shop_Product 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Shop_Product 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

        
    function getTitleFieldName() {
        return 'title';   
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
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'id',
            ),
        );
    }
        
    /**
     * @return Sample_Shop_Product 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
        
}

