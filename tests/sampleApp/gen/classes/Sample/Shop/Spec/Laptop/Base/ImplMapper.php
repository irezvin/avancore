<?php
/**
 * @method Sample_Shop_Spec_Laptop[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Shop_Spec_Laptop_Base_ImplMapper extends Ac_Model_Mapper {

    var $pk = 'productId';

    var $recordClass = 'Ac_Model_Record';

    var $tableName = '#__shop_spec_laptop';

    var $id = 'Sample_Shop_Spec_Laptop_ImplMapper';

    var $storage = 'Sample_Shop_Spec_Laptop_Storage';

    var $columnNames = [ 0 => 'productId', 1 => 'weight', 2 => 'battery', ];

    var $defaults = [
            'productId' => NULL,
            'weight' => NULL,
            'battery' => '',
        ];
    
    /**
     * @var Sample 
     */
     protected $application = false;
     
    protected $askRelationsForDefaults = false;
 
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_shopSpecLaptopShopSpec' => false,
        ]);
    }
    
    /**
     * @return Sample_Shop_Spec_Laptop 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = [], $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Sample_Shop_Spec_Laptop_ImplMapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return Sample_Shop_Spec_Laptop 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Spec_Laptop 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Sample_Shop_Spec_Laptop 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Sample_Shop_Spec_Laptop 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Sample_Shop_Spec_Laptop 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Shop_Spec_Laptop[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Shop_Spec_Laptop[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Shop_Spec_Laptop     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Shop_Spec_Laptop     */
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
     * @return Sample_Shop_Spec_Laptop[]
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
     * @return Sample_Shop_Spec_Laptop[]
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
        return 'battery';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_shopSpecLaptopShopSpec' => [
                'srcMapperClass' => 'Sample_Shop_Spec_Laptop_ImplMapper',
                'destMapperClass' => 'Sample_Shop_Spec_Mapper',
                'srcVarName' => '_shopSpecLaptopShopSpec',
                'fieldLinks' => [
                    'productId' => 'productId',
                ],
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_shop_spec_laptop_single'),

                'pluralCaption' => new Ac_Lang_String('sample_shop_spec_laptop_plural'),
            ],
            parent::doGetInfoParams()
        );
        
    }
    
    
    protected function doGetUniqueIndexData() {
        return [
            'PRIMARY' => [
                0 => 'productId',
            ],
        ];
    }

    /**
     * @return Sample_Shop_Spec_Laptop 
     */
    function loadByProductId ($productId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('productId').' = '.$this->getDb()->q($productId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
}

