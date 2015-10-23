<?php
/**
 * @method Sample_Shop_Category[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Shop_Category_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id'; 

    var $recordClass = 'Sample_Shop_Category'; 

    var $tableName = '#__shop_categories'; 

    var $id = 'Sample_Shop_Category_Mapper'; 

    var $columnNames = array ( 0 => 'id', 1 => 'title', 2 => 'leftCol', 3 => 'rightCol', 4 => 'ignore', 5 => 'parentId', 6 => 'ordering', 7 => 'depth', 8 => 'metaId', 9 => 'pubId', ); 

    var $nullableColumns = array ( 0 => 'title', 1 => 'parentId', 2 => 'metaId', 3 => 'pubId', ); 

    var $defaults = array (
            'id' => NULL,
            'title' => NULL,
            'leftCol' => NULL,
            'rightCol' => NULL,
            'ignore' => NULL,
            'parentId' => NULL,
            'ordering' => NULL,
            'depth' => NULL,
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
        ));
    }
    
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), array (
            '_shopProducts' => false,
            '_shopProductsCount' => false,
            '_shopProductsLoaded' => false,
            '_shopProductIds' => false,
        ));
    }
    
    /**
     * @return Sample_Shop_Category 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Category_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Category 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Category 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Shop_Category 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Shop_Category 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Shop_Category 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Shop_Category[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Shop_Category[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Shop_Category     */
    function findFirst (array $query = array(), $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Shop_Category     */
    function findOne (array $query = array()) {
        return parent::findOne($query);
    }
    
    /**
     * @param array $query
     * @param mixed $keysToList
     * @param mixed $sort
     * @param int $limit
     * @param int $offset
     * @param bool $forceStorage
     * @return Sample_Shop_Category[]
     */
    function find (array $query = array(), $keysToList = true, $sort = false, $limit = false, $offset = false, & $remainingQuery = array(), & $sorted = false) {
        if (func_num_args() > 5) $remainingQuery = true;
        return parent::find($query, $keysToList, $sort, $limit, $offset, $remainingQuery, $sorted);
    }
    
    /**
     * Does partial search.
     * 
     * Objects are always returned by-identifiers.
     * 
     * @return Sample_Shop_Category[]
     *
     * @param array $inMemoryRecords - set of in-memory records to search in
     * @param type $areByIdentifiers - whether $inMemoryRecords are already indexed by identifiers
     * @param array $query - the query (set of criteria)
     * @param mixed $sort - how to sort
     * @param int $limit
     * @param int $offset
     * @param bool $canUseStorage - whether to ask storage to find missing items or apply storage-specific criteria first
     * @param array $remainingQuery - return value - critria that Mapper wasn't able to understand (thus they weren't applied)
     * @param bool $sorted - return value - whether the result was sorted according to $sort paramter
     */
    function filter (array $records, array $query = array(), $sort = false, $limit = false, $offset = false, & $remainingQuery = true, & $sorted = false, $areByIds = false) {
        if (func_num_args() > 5) $remainingQuery = true;
        return parent::filter($records, $query, $sort, $limit, $offset, $remainingQuery, $sorted, $areByIds);
    }
    

    
    function getTitleFieldName() {
        return 'title';   
    }
    
    function getDefaultSort() {
        return 'ordering';
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_shopProducts' => array (
                'srcMapperClass' => 'Sample_Shop_Category_Mapper',
                'destMapperClass' => 'Sample_Shop_Product_Mapper',
                'srcVarName' => '_shopProducts',
                'srcNNIdsVarName' => '_shopProductIds',
                'srcCountVarName' => '_shopProductsCount',
                'srcLoadedVarName' => '_shopProductsLoaded',
                'destVarName' => '_shopCategories',
                'destCountVarName' => '_shopCategoriesCount',
                'destLoadedVarName' => '_shopCategoriesLoaded',
                'destNNIdsVarName' => '_shopCategoryIds',
                'fieldLinks' => array (
                    'id' => 'categoryId',
                ),
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'midTableName' => '#__shop_product_categories',
                'fieldLinks2' => array (
                    'productId' => 'id',
                ),
            ),
        ));
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), array (
            'shopProducts' => array (
                'relationId' => '_shopProducts',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopProduct',
                'plural' => 'shopProducts',
                'class' => 'Ac_Model_Association_ManyToMany',
                'loadDestObjectsMapperMethod' => 'loadShopProductsFor',
                'loadSrcObjectsMapperMethod' => 'loadForShopProducts',
                'getSrcObjectsMapperMethod' => 'getOfShopProducts',
                'createDestObjectMethod' => 'createShopProduct',
                'listDestObjectsMethod' => 'listShopProducts',
                'countDestObjectsMethod' => 'countShopProducts',
                'getDestObjectMethod' => 'getShopProduct',
                'addDestObjectMethod' => 'addShopProduct',
                'isDestLoadedMethod' => 'isShopProductsLoaded',
                'loadDestIdsMapperMethod' => 'loadShopProductIdsFor',
                'getDestIdsMethod' => 'getShopProductIds',
                'setDestIdsMethod' => 'setShopProductIds',
                'clearDestObjectsMethod' => 'clearShopProducts',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => new Ac_Lang_String('sample_shop_categories_single'),
                'pluralCaption' => new Ac_Lang_String('sample_shop_categories_plural'),
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
     * @return Sample_Shop_Category 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Shop_Category 
     */
    function loadByPubId ($pubId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('pubId').' = '.$this->getDb()->q($pubId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) one or more shopCategories of given one or more shopProducts 
     * @param Sample_Shop_Category|array $shopProducts     
     * @return Sample_Shop_Category|array of Sample_Shop_Category objects  
     */
    function getOfShopProducts($shopProducts) {
        $rel = $this->getRelation('_shopProducts');
        $res = $rel->getSrc($shopProducts); 
        return $res;
    }
    
    /**
     * Loads one or more shopCategories of given one or more shopProducts 
     * @param Sample_Shop_Product|array $shopProducts of Sample_Shop_Category objects      
     */
    function loadForShopProducts($shopProducts) {
        $rel = $this->getRelation('_shopProducts');
        return $rel->loadSrc($shopProducts); 
    }
    
    /**
     * Loads one or more shopProducts of given one or more shopCategories 
     * @param Sample_Shop_Category|array $shopCategories     
     */
    function loadShopProductsFor($shopCategories) {
        $rel = $this->getRelation('_shopProducts');
        return $rel->loadDest($shopCategories); 
    }


    /**
     * @param Sample_Shop_Category|array $shopCategories 
     */
     function loadShopProductIdsFor($shopCategories) {
        $rel = $this->getRelation('_shopProducts');
        return $rel->loadDestNNIds($shopCategories); 
    }
    
    
}

