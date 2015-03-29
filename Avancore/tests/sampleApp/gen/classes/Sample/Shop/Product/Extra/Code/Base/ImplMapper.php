<?php

class Sample_Shop_Product_Extra_Code_Base_ImplMapper extends Ac_Model_Mapper {

    var $pk = 'productId'; 

    var $recordClass = 'Ac_Model_Record'; 

    var $tableName = '#__shop_product_extraCodes'; 

    var $id = 'Sample_Shop_Product_Extra_Code_ImplMapper'; 

    var $columnNames = array ( 0 => 'productId', 1 => 'ean', 2 => 'asin', 3 => 'gtin', 4 => 'responsiblePersonId', ); 

    var $nullableSqlColumns = array ( 0 => 'responsiblePersonId', ); 

    var $defaults = array (
            'productId' => NULL,
            'ean' => '',
            'asin' => '',
            'gtin' => '',
            'responsiblePersonId' => NULL,
        ); 
 
 
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
            '_person' => false,
        );
    }
    
    /**
     * @return Sample_Shop_Product_Extra_Code 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Product_Extra_Code_ImplMapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Product_Extra_Code 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Product_Extra_Code 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Shop_Product_Extra_Code 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Shop_Product_Extra_Code 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Shop_Product_Extra_Code 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }

                
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_person' => array (
                'srcMapperClass' => 'Sample_Shop_Product_Extra_Code_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_person',
                'destVarName' => '_shopProducts',
                'destCountVarName' => '_shopProductsCount',
                'destLoadedVarName' => '_shopProductsLoaded',
                'fieldLinks' => array (
                    'responsiblePersonId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
        ));
        
    }
            
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), array (
            'person' => array (
                'relationId' => '_person',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'person',
                'plural' => 'people',
                'canLoadSrcObjects' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadPeopleFor',
                'loadSrcObjectsMapperMethod' => NULL,
                'getSrcObjectsMapperMethod' => 'getOfPeople',
                'createDestObjectMethod' => 'createPerson',
                'getDestObjectMethod' => 'getPerson',
                'setDestObjectMethod' => 'setPerson',
                'clearDestObjectMethod' => 'clearPerson',
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
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'productId',
            ),
        );
    }
        
    /**
     * @return Sample_Shop_Product_Extra_Code 
     */
    function loadByProductId ($productId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('productId').' = '.$this->getDb()->q($productId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    
    
    /**
     * Loads several people of given one or more shopProductExtraCodes 
     * @param Sample_Shop_Product_Extra_Code|array $shopProductExtraCodes     
     */
    function loadPeopleFor($shopProductExtraCodes) {
        $rel = $this->getRelation('_person');
        return $rel->loadDest($shopProductExtraCodes); 
    }

    
}

