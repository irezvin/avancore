<?php

class Sample_Shop_Product_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id'; 

    var $recordClass = 'Sample_Shop_Product'; 

    var $tableName = '#__shop_products'; 

    var $id = 'Sample_Shop_Product_Mapper'; 

    var $columnNames = array ( 0 => 'id', 1 => 'sku', 2 => 'title', 3 => 'metaId', 4 => 'pubId', ); 

    var $nullableSqlColumns = array ( 0 => 'metaId', 1 => 'pubId', ); 

    var $defaults = array (
            'id' => NULL,
            'sku' => '',
            'title' => '',
            'metaId' => NULL,
            'pubId' => NULL,
        ); 
 
    
    protected $autoincFieldName = 'id';
    
    protected $askRelationsForDefaults = false;
    
    protected function doGetCoreMixables() { 
        return Ac_Util::m(parent::doGetCoreMixables(), array (
            'publish' => array (
                'class' => 'Sample_Publish_MapperMixable',
                'colMap' => array (
                    'id' => 'pubId',
                ),
            ),
            'extraCode' => array (
                'class' => 'Sample_Shop_Product_Extra_Code_MapperMixable',
                'colMap' => array (
                    'productId' => 'id',
                ),
            ),
            'note' => array (
                'class' => 'Sample_Shop_Product_Note_MapperMixable',
                'colMap' => array (
                    'productId' => 'id',
                ),
            ),
        ));
    }
    
 
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    function doGetInternalDefaults() {
        return array (
            '_shopCategories' => false,
            '_shopCategoriesCount' => false,
            '_shopCategoriesLoaded' => false,
            '_shopCategoryIds' => false,
            '_notePerson' => false,
            '_noteShopProductsCount' => false,
            '_noteShopProductsLoaded' => false,
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
                
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_shopCategories' => array (
                'srcMapperClass' => 'Sample_Shop_Product_Mapper',
                'destMapperClass' => 'Sample_Shop_Category_Mapper',
                'srcVarName' => '_shopCategories',
                'srcNNIdsVarName' => '_shopCategoryIds',
                'srcCountVarName' => '_shopCategoriesCount',
                'srcLoadedVarName' => '_shopCategoriesLoaded',
                'destVarName' => '_shopProducts',
                'destCountVarName' => '_shopProductsCount',
                'destLoadedVarName' => '_shopProductsLoaded',
                'destNNIdsVarName' => '_shopProductIds',
                'fieldLinks' => array (
                    'id' => 'productId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'midTableName' => '#__shop_product_categories',
                'fieldLinks2' => array (
                    'categoryId' => 'id',
                ),
            ),
        ));
        
    }
            
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), array (
            'shopCategories' => array (
                'relationId' => '_shopCategories',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopCategory',
                'plural' => 'shopCategories',
                'class' => 'Ac_Model_Association_ManyToMany',
                'loadDestObjectsMapperMethod' => 'loadShopCategoriesFor',
                'loadSrcObjectsMapperMethod' => 'loadForShopCategories',
                'getSrcObjectsMapperMethod' => 'getOfShopCategories',
                'createDestObjectMethod' => 'createShopCategory',
                'listDestObjectsMethod' => 'listShopCategories',
                'countDestObjectsMethod' => 'countShopCategories',
                'getDestObjectMethod' => 'getShopCategory',
                'addDestObjectMethod' => 'addShopCategory',
                'isDestLoadedMethod' => 'isShopCategoriesLoaded',
                'loadDestIdsMapperMethod' => 'loadShopCategoryIdsFor',
                'getDestIdsMethod' => 'getShopCategoryIds',
                'setDestIdsMethod' => 'setShopCategoryIds',
                'clearDestObjectsMethod' => 'clearShopCategories',
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
    
        
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'id',
            ),
            'idxPubId' => array (
                0 => 'pubId',
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

    /**
     * @return Sample_Shop_Product 
     */
    function loadByPubId ($pubId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('pubId').' = '.$this->getDb()->q($pubId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns (but not loads!) one or more shopProducts of given one or more shopCategories 
     * @param Sample_Shop_Product|array $shopCategories     
     * @return Sample_Shop_Product|array of Sample_Shop_Product objects  
     */
    function getOfShopCategories($shopCategories) {
        $rel = $this->getRelation('_shopCategories');
        $res = $rel->getSrc($shopCategories); 
        return $res;
    }
    
    /**
     * Loads one or more shopProducts of given one or more shopCategories 
     * @param Sample_Shop_Category|array $shopCategories of Sample_Shop_Product objects      
     */
    function loadForShopCategories($shopCategories) {
        $rel = $this->getRelation('_shopCategories');
        return $rel->loadSrc($shopCategories); 
    }
    
    /**
     * Loads one or more shopCategories of given one or more shopProducts 
     * @param Sample_Shop_Product|array $shopProducts     
     */
    function loadShopCategoriesFor($shopProducts) {
        $rel = $this->getRelation('_shopCategories');
        return $rel->loadDest($shopProducts); 
    }


    /**
     * @param Sample_Shop_Product|array $shopProducts 
     */
     function loadShopCategoryIdsFor($shopProducts) {
        $rel = $this->getRelation('_shopCategories');
        return $rel->loadDestNNIds($shopProducts); 
    }
    

    
    
    /**
     * Loads several people of given one or more shopProducts 
     * @param Sample_Shop_Product|array $shopProducts     
     */
    function loadNotePeopleFor($shopProducts) {
        $rel = $this->getRelation('_notePerson');
        return $rel->loadDest($shopProducts); 
    }

    
}

