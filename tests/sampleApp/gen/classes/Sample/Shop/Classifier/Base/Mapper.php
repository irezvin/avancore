<?php
/**
 * @method Sample_Shop_Classifier[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Shop_Classifier_Base_Mapper extends Ac_Model_Mapper {

    var $pk = 'id';

    var $recordClass = 'Sample_Shop_Classifier';

    var $tableName = '#__shop_classifier';

    var $id = 'Sample_Shop_Classifier_Mapper';

    var $shortId = 'shopClassifier';

    var $storage = 'Sample_Shop_Classifier_Storage';

    var $columnNames = [ 0 => 'id', 1 => 'title', 2 => 'type', ];

    var $defaults = [
            'id' => NULL,
            'title' => '',
            'type' => NULL,
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
   
    protected $autoincFieldName = 'id';
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_shopClassifierType' => false,
            '_monitorShopSpecs' => false,
            '_monitorShopSpecsCount' => false,
            '_monitorShopSpecsLoaded' => false,
        ]);
    }
    
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * Creates new ShopClassifier instance that is bound to this mapper.
     * 
     * @param string|array $typeIdOrDefaults Id of child mapper (FALSE = create default if possible)
     * @param array $defaults Values of object properties' to assign
     * @return Sample_Shop_Classifier 
     */ 
    function createRecord($typeIdOrDefaults = false, array $defaults = []) {
        $res = parent::createRecord($typeIdOrDefaults, $defaults);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Classifier 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Shop_Classifier 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Shop_Classifier 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Shop_Classifier 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Shop_Classifier[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Shop_Classifier[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Shop_Classifier     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Shop_Classifier     */
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
     * @return Sample_Shop_Classifier[]
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
     * @return Sample_Shop_Classifier[]
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
            '_shopClassifierType' => [
                'srcMapperClass' => 'Sample_Shop_Classifier_Mapper',
                'destMapperClass' => 'Sample_Shop_Classifier_Type_Mapper',
                'srcVarName' => '_shopClassifierType',
                'destVarName' => '_shopClassifier',
                'destCountVarName' => '_shopClassifierCount',
                'destLoadedVarName' => '_shopClassifierLoaded',
                'fieldLinks' => [
                    'type' => 'type',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
            '_monitorShopSpecs' => [
                'srcMapperClass' => 'Sample_Shop_Classifier_Mapper',
                'destMapperClass' => 'Sample_Shop_Spec_Mapper',
                'srcVarName' => '_monitorShopSpecs',
                'srcCountVarName' => '_monitorShopSpecsCount',
                'srcLoadedVarName' => '_monitorShopSpecsLoaded',
                'fieldLinks' => [
                    'id' => 'matrixTypeId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => false,
            ],
        ]);
        
    }
    
    protected function doGetAssociationPrototypes() {
        return Ac_Util::m(parent::doGetAssociationPrototypes(), [
            'shopClassifierType' => [
                'relationId' => '_shopClassifierType',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'shopClassifierType',
                'plural' => 'shopClassifierType',
                'class' => 'Ac_Model_Association_One',
                'loadDestObjectsMapperMethod' => 'loadShopClassifierTypeFor',
                'loadSrcObjectsMapperMethod' => 'loadForShopClassifierType',
                'getSrcObjectsMapperMethod' => 'getOfShopClassifierType',
                'createDestObjectMethod' => 'createShopClassifierType',
                'getDestObjectMethod' => 'getShopClassifierType',
                'setDestObjectMethod' => 'setShopClassifierType',
                'clearDestObjectMethod' => 'clearShopClassifierType',
            ],
            'monitorShopSpecs' => [
                'relationId' => '_monitorShopSpecs',
                'useMapperMethods' => true,
                'useModelMethods' => true,
                'single' => 'monitorShopSpec',
                'plural' => 'monitorShopSpecs',
                'canLoadDestObjects' => false,
                'class' => 'Ac_Model_Association_Many',
                'loadDestObjectsMapperMethod' => NULL,
                'loadSrcObjectsMapperMethod' => 'loadForMonitorShopSpecs',
                'getSrcObjectsMapperMethod' => 'getOfMonitorShopSpecs',
                'createDestObjectMethod' => 'createMonitorShopSpec',
                'getAllDestObjectsMethod' => 'getAllMonitorShopSpecs',
                'listDestObjectsMethod' => 'listMonitorShopSpecs',
                'countDestObjectsMethod' => 'countMonitorShopSpecs',
                'getDestObjectMethod' => 'getMonitorShopSpec',
                'addDestObjectMethod' => 'addMonitorShopSpec',
                'isDestLoadedMethod' => 'isMonitorShopSpecsLoaded',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_shop_classifier_single'),

                'pluralCaption' => new Ac_Lang_String('sample_shop_classifier_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'id',
            ],
            'type_title' => [
                0 => 'type',
                1 => 'title',
            ],
        ];
    }

    /**
     * @return Sample_Shop_Classifier 
     */
    function loadById ($id) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('id').' = '.$this->getDb()->q($id).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }

    /**
     * @return Sample_Shop_Classifier 
     */
    function loadByTypeTitle ($type, $title) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('type').' = '.$this->getDb()->q($type).' AND '.$this->getDb()->n('title').' = '.$this->getDb()->q($title).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    /**
     * Returns (but not loads!) several shopClassifier of given one or more shopClassifierType 
     * @param Sample_Shop_Classifier|array $shopClassifierType     
     * @return Sample_Shop_Classifier|array of Sample_Shop_Classifier objects  
     */
    function getOfShopClassifierType($shopClassifierType) {
        $rel = $this->getRelation('_shopClassifierType');
        $res = $rel->getSrc($shopClassifierType); 
        return $res;
    }
    
    /**
     * Loads several shopClassifier of given one or more shopClassifierType 
     * @param Sample_Shop_Classifier_Type|array $shopClassifierType of Sample_Shop_Classifier objects      
     */
    function loadForShopClassifierType($shopClassifierType) {
        $rel = $this->getRelation('_shopClassifierType');
        return $rel->loadSrc($shopClassifierType); 
    }
    
    /**
     * Loads several shopClassifierType of given one or more shopClassifier 
     * @param Sample_Shop_Classifier|array $shopClassifier     
     */
    function loadShopClassifierTypeFor($shopClassifier) {
        $rel = $this->getRelation('_shopClassifierType');
        return $rel->loadDest($shopClassifier); 
    }

    /**
     * Returns (but not loads!) one or more shopClassifier of given one or more shopSpecs 
     * @param Sample_Shop_Classifier|array $monitorShopSpecs     
     * @return array of Sample_Shop_Classifier objects  
     */
    function getOfMonitorShopSpecs($monitorShopSpecs) {
        $rel = $this->getRelation('_monitorShopSpecs');
        $res = $rel->getSrc($monitorShopSpecs); 
        return $res;
    }
    
    /**
     * Loads one or more shopClassifier of given one or more shopSpecs 
     * @param Sample_Shop_Spec|array $monitorShopSpecs of Sample_Shop_Classifier objects      
     */
    function loadForMonitorShopSpecs($monitorShopSpecs) {
        $rel = $this->getRelation('_monitorShopSpecs');
        return $rel->loadSrc($monitorShopSpecs); 
    }
    

    
}

