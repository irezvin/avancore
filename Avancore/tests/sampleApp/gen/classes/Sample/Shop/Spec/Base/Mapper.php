<?php
/**
 * @method Sample_Shop_Spec[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Shop_Spec_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'productId';

    var $recordClass = 'Sample_Shop_Spec';

    var $tableName = '#__shop_specs';

    var $id = 'Sample_Shop_Spec_Mapper';

    var $storage = 'Sample_Shop_Spec_Storage';

    var $columnNames = array ( 0 => 'productId', 1 => 'detailsUrl', 2 => 'specsType', );

    var $defaults = array (
            'productId' => NULL,
            'detailsUrl' => '',
            'specsType' => '',
        );
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), array (
            '_shopProduct' => false,
        ));
    }
    
    /**
     * @return Sample_Shop_Spec 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Spec_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Spec 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Spec 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Shop_Spec 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Shop_Spec 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Shop_Spec 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Shop_Spec[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Shop_Spec[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Shop_Spec     */
    function findFirst (array $query = array(), $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Shop_Spec     */
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
     * @return Sample_Shop_Spec[]
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
     * @return Sample_Shop_Spec[]
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
        return 'detailsUrl';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_shopProduct' => array (
                'srcMapperClass' => 'Sample_Shop_Spec_Mapper',
                'destMapperClass' => 'Sample_Shop_Product_Mapper',
                'srcVarName' => '_shopProduct',
                'destVarName' => '_shopSpec',
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
                'singleCaption' => new Ac_Lang_String('sample_shop_specs_single'),
                'pluralCaption' => new Ac_Lang_String('sample_shop_specs_plural'),
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
     * @return Sample_Shop_Spec 
     */
    function loadByProductId ($productId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('productId').' = '.$this->getDb()->q($productId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) one or more shopSpecs of given one or more shopProducts 
     * @param Sample_Shop_Spec|array $shopProducts     
     * @return Sample_Shop_Spec|array of Sample_Shop_Spec objects  
     */
    function getOfShopProducts($shopProducts) {
        $rel = $this->getRelation('_shopProduct');
        $res = $rel->getSrc($shopProducts); 
        return $res;
    }
    
    /**
     * Loads one or more shopSpecs of given one or more shopProducts 
     * @param Sample_Shop_Product|array $shopProducts of Sample_Shop_Spec objects      
     */
    function loadForShopProducts($shopProducts) {
        $rel = $this->getRelation('_shopProduct');
        return $rel->loadSrc($shopProducts); 
    }
    
    /**
     * Loads one or more shopProducts of given one or more shopSpecs 
     * @param Sample_Shop_Spec|array $shopSpecs     
     */
    function loadShopProductsFor($shopSpecs) {
        $rel = $this->getRelation('_shopProduct');
        return $rel->loadDest($shopSpecs); 
    }

    
}

