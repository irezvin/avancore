<?php
/**
 * @method Sample_Shop_Product[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Shop_Product_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id';

    var $recordClass = 'Sample_Shop_Product';

    var $tableName = '#__shop_products';

    var $id = 'Sample_Shop_Product_Mapper';

    var $storage = 'Sample_Shop_Product_Storage';

    var $columnNames = [ 0 => 'id', 1 => 'sku', 2 => 'title', 3 => 'metaId', 4 => 'pubId', ];

    var $nullableColumns = [ 0 => 'metaId', 1 => 'pubId', ];

    var $defaults = [
            'id' => NULL,
            'sku' => '',
            'title' => '',
            'metaId' => NULL,
            'pubId' => NULL,
        ];
    
    /**
     * @var Sample 
     */
     protected $application = false;
     
   
    protected $autoincFieldName = 'id';
    protected $askRelationsForDefaults = false;
 
    protected function doGetCoreMixables() { 
        return Ac_Util::m(parent::doGetCoreMixables(), [
            'publish' => [
                'class' => 'Sample_Publish_MapperMixable',
                'colMap' => [
                    'id' => 'pubId',
                ],
                'fieldNames' => [
                    'sharedObjectType' => false,
                ],
            ],
            'extraCode' => [
                'class' => 'Sample_Shop_Product_Extra_Code_MapperMixable',
                'colMap' => [
                    'productId' => 'id',
                ],
            ],
            'note' => [
                'class' => 'Sample_Shop_Product_Note_MapperMixable',
                'colMap' => [
                    'productId' => 'id',
                ],
            ],
        ]);
    }
    
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_shopCategories' => false,
            '_shopCategoriesCount' => false,
            '_shopCategoriesLoaded' => false,
            '_shopCategoryIds' => false,
            '_referencedShopProducts' => false,
            '_referencedShopProductsCount' => false,
            '_referencedShopProductsLoaded' => false,
            '_referencedShopProductIds' => false,
            '_referencingShopProducts' => false,
            '_referencingShopProductsCount' => false,
            '_referencingShopProductsLoaded' => false,
            '_referencingShopProductIds' => false,
            '_shopSpec' => false,
            '_notePerson' => false,
            '_noteShopProductsCount' => false,
            '_noteShopProductsLoaded' => false,
        ]);
    }
    
    /**
     * @return Sample_Shop_Product 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = [], $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Product_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
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
    function reference ($values = []) {
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
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Shop_Product[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Shop_Product[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Shop_Product     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Shop_Product     */
    function findOne (array $query = []) {
        return parent::findOne($query);
    }
    
    /**
     * @param array $query
     * @param mixed $keysToList
     * @param mixed $sort
     * @param int $limit
     * @param int $offset
     * @param bool $forceStorage
     * @return Sample_Shop_Product[]
     */
    function find (array $query = [], $keysToList = true, $sort = false, $limit = false, $offset = false, & $remainingQuery = [], & $sorted = false) {
        if (func_num_args() > 5) $remainingQuery = true;
        return parent::find($query, $keysToList, $sort, $limit, $offset, $remainingQuery, $sorted);
    }
    
    /**
     * Does partial search.
     * 
     * Objects are always returned by-identifiers.
     * 
     * @return Sample_Shop_Product[]
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
    function filter (array $records, array $query = [], $sort = false, $limit = false, $offset = false, & $remainingQuery = true, & $sorted = false, $areByIds = false) {
        if (func_num_args() > 5) $remainingQuery = true;
        return parent::filter($records, $query, $sort, $limit, $offset, $remainingQuery, $sorted, $areByIds);
    }
    

    
    function getTitleFieldName() {
        return 'title';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_shopCategories' => [
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
                'fieldLinks' => [
                    'id' => 'productId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'srcLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadShopCategoryIdsFor',
                ],
                'destLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadShopProductIdsFor',
                ],
                'midTableName' => '#__shop_product_categories',
                'fieldLinks2' => [
                    'categoryId' => 'id',
                ],
            ],
            '_referencedShopProducts' => [
                'srcMapperClass' => 'Sample_Shop_Product_Mapper',
                'destMapperClass' => 'Sample_Shop_Product_Mapper',
                'srcVarName' => '_referencedShopProducts',
                'srcNNIdsVarName' => '_referencedShopProductIds',
                'srcCountVarName' => '_referencedShopProductsCount',
                'srcLoadedVarName' => '_referencedShopProductsLoaded',
                'destVarName' => '_referencingShopProducts',
                'destCountVarName' => '_referencingShopProductsCount',
                'destLoadedVarName' => '_referencingShopProductsLoaded',
                'destNNIdsVarName' => '_referencingShopProductIds',
                'fieldLinks' => [
                    'id' => 'productId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'srcLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadReferencedShopProductIdsFor',
                ],
                'destLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadReferencingShopProductIdsFor',
                ],
                'midTableName' => '#__shop_product_related',
                'fieldLinks2' => [
                    'relatedProductId' => 'id',
                ],
            ],
            '_referencingShopProducts' => [
                'srcMapperClass' => 'Sample_Shop_Product_Mapper',
                'destMapperClass' => 'Sample_Shop_Product_Mapper',
                'srcVarName' => '_referencingShopProducts',
                'srcNNIdsVarName' => '_referencingShopProductIds',
                'srcCountVarName' => '_referencingShopProductsCount',
                'srcLoadedVarName' => '_referencingShopProductsLoaded',
                'destVarName' => '_referencedShopProducts',
                'destCountVarName' => '_referencedShopProductsCount',
                'destLoadedVarName' => '_referencedShopProductsLoaded',
                'destNNIdsVarName' => '_referencedShopProductIds',
                'fieldLinks' => [
                    'id' => 'relatedProductId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => false,
                'srcLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadReferencingShopProductIdsFor',
                ],
                'destLoadNNIdsMethod' => [
                    0 => true,
                    1 => 'loadReferencedShopProductIdsFor',
                ],
                'midTableName' => '#__shop_product_related',
                'fieldLinks2' => [
                    'productId' => 'id',
                ],
            ],
            '_shopSpec' => [
                'srcMapperClass' => 'Sample_Shop_Product_Mapper',
                'destMapperClass' => 'Sample_Shop_Spec_Mapper',
                'srcVarName' => '_shopSpec',
                'destVarName' => '_shopProduct',
                'fieldLinks' => [
                    'id' => 'productId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => true,
            ],
        ]);
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), [
            'shopCategories' => [
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
                'getAllDestObjectsMethod' => 'getAllShopCategories',
                'listDestObjectsMethod' => 'listShopCategories',
                'countDestObjectsMethod' => 'countShopCategories',
                'getDestObjectMethod' => 'getShopCategory',
                'addDestObjectMethod' => 'addShopCategory',
                'isDestLoadedMethod' => 'isShopCategoriesLoaded',
                'loadDestIdsMapperMethod' => 'loadShopCategoryIdsFor',
                'getDestIdsMethod' => 'getShopCategoryIds',
                'setDestIdsMethod' => 'setShopCategoryIds',
                'clearDestObjectsMethod' => 'clearShopCategories',
            ],
            'referencedShopProducts' => [
                'relationId' => '_referencedShopProducts',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'referencedShopProduct',
                'plural' => 'referencedShopProducts',
                'class' => 'Ac_Model_Association_ManyToMany',
                'loadDestObjectsMapperMethod' => 'loadReferencedShopProductsFor',
                'loadSrcObjectsMapperMethod' => 'loadForReferencedShopProducts',
                'getSrcObjectsMapperMethod' => 'getOfReferencedShopProducts',
                'createDestObjectMethod' => 'createReferencedShopProduct',
                'getAllDestObjectsMethod' => 'getAllReferencedShopProducts',
                'listDestObjectsMethod' => 'listReferencedShopProducts',
                'countDestObjectsMethod' => 'countReferencedShopProducts',
                'getDestObjectMethod' => 'getReferencedShopProduct',
                'addDestObjectMethod' => 'addReferencedShopProduct',
                'isDestLoadedMethod' => 'isReferencedShopProductsLoaded',
                'loadDestIdsMapperMethod' => 'loadReferencedShopProductIdsFor',
                'getDestIdsMethod' => 'getReferencedShopProductIds',
                'setDestIdsMethod' => 'setReferencedShopProductIds',
                'clearDestObjectsMethod' => 'clearReferencedShopProducts',
            ],
            'referencingShopProducts' => [
                'relationId' => '_referencingShopProducts',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'referencingShopProduct',
                'plural' => 'referencingShopProducts',
                'class' => 'Ac_Model_Association_ManyToMany',
                'loadDestObjectsMapperMethod' => 'loadReferencingShopProductsFor',
                'loadSrcObjectsMapperMethod' => 'loadForReferencingShopProducts',
                'getSrcObjectsMapperMethod' => 'getOfReferencingShopProducts',
                'createDestObjectMethod' => 'createReferencingShopProduct',
                'getAllDestObjectsMethod' => 'getAllReferencingShopProducts',
                'listDestObjectsMethod' => 'listReferencingShopProducts',
                'countDestObjectsMethod' => 'countReferencingShopProducts',
                'getDestObjectMethod' => 'getReferencingShopProduct',
                'addDestObjectMethod' => 'addReferencingShopProduct',
                'isDestLoadedMethod' => 'isReferencingShopProductsLoaded',
                'loadDestIdsMapperMethod' => 'loadReferencingShopProductIdsFor',
                'getDestIdsMethod' => 'getReferencingShopProductIds',
                'setDestIdsMethod' => 'setReferencingShopProductIds',
                'clearDestObjectsMethod' => 'clearReferencingShopProducts',
            ],
            'shopSpec' => [
                'relationId' => '_shopSpec',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopSpec',
                'plural' => 'shopSpecs',
                'isReferenced' => false,
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadShopSpecsFor',
                'loadSrcObjectsMapperMethod' => 'loadForShopSpecs',
                'getSrcObjectsMapperMethod' => 'getOfShopSpecs',
                'createDestObjectMethod' => 'createShopSpec',
                'getDestObjectMethod' => 'getShopSpec',
                'setDestObjectMethod' => 'setShopSpec',
                'clearDestObjectMethod' => 'clearShopSpec',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_shop_products_single'),

                'pluralCaption' => new Ac_Lang_String('sample_shop_products_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'id',
            ],
            'idxPubId' => [
                0 => 'pubId',
            ],
        ];
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
     * Returns (but not loads!) one or more shopProducts of given one or more shopProducts 
     * @param Sample_Shop_Product|array $referencedShopProducts     
     * @return Sample_Shop_Product|array of Sample_Shop_Product objects  
     */
    function getOfReferencedShopProducts($referencedShopProducts) {
        $rel = $this->getRelation('_referencedShopProducts');
        $res = $rel->getSrc($referencedShopProducts); 
        return $res;
    }
    
    /**
     * Loads one or more shopProducts of given one or more shopProducts 
     * @param Sample_Shop_Product|array $referencedShopProducts of Sample_Shop_Product objects      
     */
    function loadForReferencedShopProducts($referencedShopProducts) {
        $rel = $this->getRelation('_referencedShopProducts');
        return $rel->loadSrc($referencedShopProducts); 
    }
    
    /**
     * Loads one or more shopProducts of given one or more shopProducts 
     * @param Sample_Shop_Product|array $shopProducts     
     */
    function loadReferencedShopProductsFor($shopProducts) {
        $rel = $this->getRelation('_referencedShopProducts');
        return $rel->loadDest($shopProducts); 
    }


    /**
     * @param Sample_Shop_Product|array $shopProducts 
     */
     function loadReferencedShopProductIdsFor($shopProducts) {
        $rel = $this->getRelation('_referencedShopProducts');
        return $rel->loadDestNNIds($shopProducts); 
    }
    
    /**
     * Returns (but not loads!) one or more shopProducts of given one or more shopProducts 
     * @param Sample_Shop_Product|array $referencingShopProducts     
     * @return Sample_Shop_Product|array of Sample_Shop_Product objects  
     */
    function getOfReferencingShopProducts($referencingShopProducts) {
        $rel = $this->getRelation('_referencingShopProducts');
        $res = $rel->getSrc($referencingShopProducts); 
        return $res;
    }
    
    /**
     * Loads one or more shopProducts of given one or more shopProducts 
     * @param Sample_Shop_Product|array $referencingShopProducts of Sample_Shop_Product objects      
     */
    function loadForReferencingShopProducts($referencingShopProducts) {
        $rel = $this->getRelation('_referencingShopProducts');
        return $rel->loadSrc($referencingShopProducts); 
    }
    
    /**
     * Loads one or more shopProducts of given one or more shopProducts 
     * @param Sample_Shop_Product|array $shopProducts     
     */
    function loadReferencingShopProductsFor($shopProducts) {
        $rel = $this->getRelation('_referencingShopProducts');
        return $rel->loadDest($shopProducts); 
    }


    /**
     * @param Sample_Shop_Product|array $shopProducts 
     */
     function loadReferencingShopProductIdsFor($shopProducts) {
        $rel = $this->getRelation('_referencingShopProducts');
        return $rel->loadDestNNIds($shopProducts); 
    }
    
    /**
     * Returns (but not loads!) one or more shopProducts of given one or more shopSpecs 
     * @param Sample_Shop_Product|array $shopSpecs     
     * @return Sample_Shop_Product|array of Sample_Shop_Product objects  
     */
    function getOfShopSpecs($shopSpecs) {
        $rel = $this->getRelation('_shopSpec');
        $res = $rel->getSrc($shopSpecs); 
        return $res;
    }
    
    /**
     * Loads one or more shopProducts of given one or more shopSpecs 
     * @param Sample_Shop_Spec|array $shopSpecs of Sample_Shop_Product objects      
     */
    function loadForShopSpecs($shopSpecs) {
        $rel = $this->getRelation('_shopSpec');
        return $rel->loadSrc($shopSpecs); 
    }
    
    /**
     * Loads one or more shopSpecs of given one or more shopProducts 
     * @param Sample_Shop_Product|array $shopProducts     
     */
    function loadShopSpecsFor($shopProducts) {
        $rel = $this->getRelation('_shopSpec');
        return $rel->loadDest($shopProducts); 
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

