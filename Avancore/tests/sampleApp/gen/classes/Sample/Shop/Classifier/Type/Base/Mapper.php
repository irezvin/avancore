<?php
/**
 * @method Sample_Shop_Classifier_Type[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Shop_Classifier_Type_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'type';

    var $recordClass = 'Sample_Shop_Classifier_Type';

    var $tableName = '#__shop_classifier_type';

    var $id = 'Sample_Shop_Classifier_Type_Mapper';

    var $storage = 'Sample_Shop_Classifier_Type_Storage';

    var $columnNames = array ( 0 => 'type', );

    var $defaults = array (
            'type' => NULL,
        );
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), array (
            '_shopClassifier' => false,
            '_shopClassifierCount' => false,
            '_shopClassifierLoaded' => false,
        ));
    }
    
    /**
     * @return Sample_Shop_Classifier_Type 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Classifier_Type_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Classifier_Type 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Classifier_Type 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Shop_Classifier_Type 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Shop_Classifier_Type 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Shop_Classifier_Type 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Shop_Classifier_Type[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Shop_Classifier_Type[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Shop_Classifier_Type     */
    function findFirst (array $query = array(), $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Shop_Classifier_Type     */
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
     * @return Sample_Shop_Classifier_Type[]
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
     * @return Sample_Shop_Classifier_Type[]
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
        return 'type';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_shopClassifier' => array (
                'srcMapperClass' => 'Sample_Shop_Classifier_Type_Mapper',
                'destMapperClass' => 'Sample_Shop_Classifier_Mapper',
                'srcVarName' => '_shopClassifier',
                'srcCountVarName' => '_shopClassifierCount',
                'srcLoadedVarName' => '_shopClassifierLoaded',
                'destVarName' => '_shopClassifierType',
                'fieldLinks' => array (
                    'type' => 'type',
                ),
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ),
        ));
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), array (
            'shopClassifier' => array (
                'relationId' => '_shopClassifier',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopClassifier',
                'plural' => 'shopClassifier',
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => 'loadShopClassifierFor',
                'loadSrcObjectsMapperMethod' => 'loadForShopClassifier',
                'getSrcObjectsMapperMethod' => 'getOfShopClassifier',
                'createDestObjectMethod' => 'createShopClassifier',
                'listDestObjectsMethod' => 'listShopClassifier',
                'countDestObjectsMethod' => 'countShopClassifier',
                'getDestObjectMethod' => 'getShopClassifier',
                'addDestObjectMethod' => 'addShopClassifier',
                'isDestLoadedMethod' => 'isShopClassifierLoaded',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => new Ac_Lang_String('sample_shop_classifier_type_single'),
                'pluralCaption' => new Ac_Lang_String('sample_shop_classifier_type_plural'),
            ),
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return array (
            'PRIMARY' => array (
                0 => 'type',
            ),
        );
    }

    /**
     * @return Sample_Shop_Classifier_Type 
     */
    function loadByType ($type) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('type').' = '.$this->getDb()->q($type).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) one or more shopClassifierType of given one or more shopClassifier 
     * @param Sample_Shop_Classifier_Type|array $shopClassifier     
     * @return array of Sample_Shop_Classifier_Type objects  
     */
    function getOfShopClassifier($shopClassifier) {
        $rel = $this->getRelation('_shopClassifier');
        $res = $rel->getSrc($shopClassifier); 
        return $res;
    }
    
    /**
     * Loads one or more shopClassifierType of given one or more shopClassifier 
     * @param Sample_Shop_Classifier|array $shopClassifier of Sample_Shop_Classifier_Type objects      
     */
    function loadForShopClassifier($shopClassifier) {
        $rel = $this->getRelation('_shopClassifier');
        return $rel->loadSrc($shopClassifier); 
    }
    
    /**
     * Loads one or more shopClassifier of given one or more shopClassifierType 
     * @param Sample_Shop_Classifier_Type|array $shopClassifierType     
     */
    function loadShopClassifierFor($shopClassifierType) {
        $rel = $this->getRelation('_shopClassifier');
        return $rel->loadDest($shopClassifierType); 
    }

    
}

