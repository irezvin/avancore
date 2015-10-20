<?php

class Sample_Shop_Product_Note_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'productId'; 

    var $recordClass = 'Sample_Shop_Product_Note'; 

    var $tableName = '#__shop_product_notes'; 

    var $id = 'Sample_Shop_Product_Note_Mapper'; 

    var $columnNames = array ( 0 => 'productId', 1 => 'note', 2 => 'noteAuthorId', ); 

    var $nullableColumns = array ( 0 => 'noteAuthorId', ); 

    var $defaults = array (
            'productId' => NULL,
            'note' => '',
            'noteAuthorId' => NULL,
        ); 
 
 
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
            '_person' => false,
            '_shopProduct' => false,
        );
    }
    
    /**
     * @return Sample_Shop_Product_Note 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Product_Note_Mapper')->createRecord($className);
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
            '_person' => array (
                'srcMapperClass' => 'Sample_Shop_Product_Note_Mapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_person',
                'destVarName' => '_shopProductNotes',
                'destCountVarName' => '_shopProductNotesCount',
                'destLoadedVarName' => '_shopProductNotesLoaded',
                'fieldLinks' => array (
                    'noteAuthorId' => 'personId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ),
            '_shopProduct' => array (
                'srcMapperClass' => 'Sample_Shop_Product_Note_Mapper',
                'destMapperClass' => 'Sample_Shop_Product_Mapper',
                'srcVarName' => '_shopProduct',
                'destVarName' => '_shopProductNote',
                'fieldLinks' => array (
                    'productId' => 'id',
                ),
                'srcIsUnique' => true,
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
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadPeopleFor',
                'loadSrcObjectsMapperMethod' => 'loadForPeople',
                'getSrcObjectsMapperMethod' => 'getOfPeople',
                'createDestObjectMethod' => 'createPerson',
                'getDestObjectMethod' => 'getPerson',
                'setDestObjectMethod' => 'setPerson',
                'clearDestObjectMethod' => 'clearPerson',
            ),
            'shopProduct' => array (
                'relationId' => '_shopProduct',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopProduct',
                'plural' => 'shopProducts',
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadShopProductsFor',
                'loadSrcObjectsMapperMethod' => 'loadForShopProducts',
                'getSrcObjectsMapperMethod' => 'getOfShopProducts',
                'createDestObjectMethod' => 'createShopProduct',
                'getDestObjectMethod' => 'getShopProduct',
                'setDestObjectMethod' => 'setShopProduct',
                'clearDestObjectMethod' => 'clearShopProduct',
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
    
    /**
     * Returns (but not loads!) several shopProductNotes of given one or more people 
     * @param Sample_Shop_Product_Note|array $people     
     * @return Sample_Shop_Product_Note|array of Sample_Shop_Product_Note objects  
     */
    function getOfPeople($people) {
        $rel = $this->getRelation('_person');
        $res = $rel->getSrc($people); 
        return $res;
    }
    
    /**
     * Loads several shopProductNotes of given one or more people 
     * @param Sample_Person|array $people of Sample_Shop_Product_Note objects      
     */
    function loadForPeople($people) {
        $rel = $this->getRelation('_person');
        return $rel->loadSrc($people); 
    }
    
    /**
     * Loads several people of given one or more shopProductNotes 
     * @param Sample_Shop_Product_Note|array $shopProductNotes     
     */
    function loadPeopleFor($shopProductNotes) {
        $rel = $this->getRelation('_person');
        return $rel->loadDest($shopProductNotes); 
    }


    /**
     * Returns (but not loads!) one or more shopProductNotes of given one or more shopProducts 
     * @param Sample_Shop_Product_Note|array $shopProducts     
     * @return Sample_Shop_Product_Note|array of Sample_Shop_Product_Note objects  
     */
    function getOfShopProducts($shopProducts) {
        $rel = $this->getRelation('_shopProduct');
        $res = $rel->getSrc($shopProducts); 
        return $res;
    }
    
    /**
     * Loads one or more shopProductNotes of given one or more shopProducts 
     * @param Sample_Shop_Product|array $shopProducts of Sample_Shop_Product_Note objects      
     */
    function loadForShopProducts($shopProducts) {
        $rel = $this->getRelation('_shopProduct');
        return $rel->loadSrc($shopProducts); 
    }
    
    /**
     * Loads one or more shopProducts of given one or more shopProductNotes 
     * @param Sample_Shop_Product_Note|array $shopProductNotes     
     */
    function loadShopProductsFor($shopProductNotes) {
        $rel = $this->getRelation('_shopProduct');
        return $rel->loadDest($shopProductNotes); 
    }

    
}

