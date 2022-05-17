<?php
/**
 * @method Sample_Shop_Product_Extra_Code[] loadFromRows(array $rows, $keysToList = false)
 */
class Sample_Shop_Product_Extra_Code_Base_ImplMapper extends Ac_Model_Mapper {

    protected $identifierField = NULL;

    var $pk = 'productId';

    var $recordClass = 'Ac_Model_Record';

    var $tableName = '#__shop_product_extraCodes';

    var $id = 'Sample_Shop_Product_Extra_Code_ImplMapper';

    var $shortId = 'shopProductExtraCodes';

    var $storage = 'Sample_Shop_Product_Extra_Code_Storage';

    var $columnNames = [ 0 => 'productId', 1 => 'ean', 2 => 'asin', 3 => 'gtin', 4 => 'responsiblePersonId', ];

    var $nullableColumns = [ 0 => 'responsiblePersonId', ];

    var $defaults = [
            'productId' => NULL,
            'ean' => '',
            'asin' => '',
            'gtin' => '',
            'responsiblePersonId' => NULL,
        ];
    
    /**
     * @var Sample 
     */
     protected $app = false;
     
    protected $askRelationsForDefaults = false;
 
    protected function doGetCoreMixables() { 
        return Ac_Util::m(parent::doGetCoreMixables(), [
            'Ac_Model_Typer_Abstract' => [
                'class' => 'Ac_Model_Typer_ExtraTable',
                'tableName' => '#__shop_product_extraCodes',
                'uniformTypeId' => 'Sample_Shop_Product_Mapper',
            ],
        ]);
    }
    
 
    function doGetInternalDefaults() {
        return Ac_Util::m(parent::doGetInternalDefaults(), [
            '_extraCodePerson' => false,
        ]);
    }
    
    
    /**
     * @return Sample 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * Creates new ShopProductExtraCodes instance that is bound to this mapper.
     * 
     * @param string|array $typeIdOrDefaults Id of child mapper (FALSE = create default if possible)
     * @param array $defaults Values of object properties' to assign
     * @return Sample_Shop_Product_Extra_Code 
     */ 
    function createRecord($typeIdOrDefaults = false, array $defaults = []) {
        $res = parent::createRecord($typeIdOrDefaults, $defaults);
        return $res;
    }
    
    /**
     * @return Sample_Shop_Product_Extra_Code 
     */ 
    function reference ($values = []) {
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
    
    /**
     * Loads array of records.
     * 
     * @return Sample_Shop_Product_Extra_Code[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Sample_Shop_Product_Extra_Code[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Sample_Shop_Product_Extra_Code     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Sample_Shop_Product_Extra_Code     */
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
     * @return Sample_Shop_Product_Extra_Code[]
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
     * @return Sample_Shop_Product_Extra_Code[]
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
        return 'ean';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), [
            '_extraCodePerson' => [
                'srcMapperClass' => 'Sample_Shop_Product_Extra_Code_ImplMapper',
                'destMapperClass' => 'Sample_Person_Mapper',
                'srcVarName' => '_extraCodePerson',
                'destVarName' => '_extraCodeShopProducts',
                'destCountVarName' => '_extraCodeShopProductsCount',
                'destLoadedVarName' => '_extraCodeShopProductsLoaded',
                'fieldLinks' => [
                    'responsiblePersonId' => 'personId',
                ],
                'srcIsUnique' => false,
                'destIsUnique' => true,
                'srcOutgoing' => true,
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [

                'singleCaption' => new Ac_Lang_String('sample_shop_product_extra_codes_single'),

                'pluralCaption' => new Ac_Lang_String('sample_shop_product_extra_codes_plural'),
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
     * @return Sample_Shop_Product_Extra_Code 
     */
    function loadByProductId ($productId) {
        $recs = $this->loadRecordsByCriteria(''.$this->getDb()->n('productId').' = '.$this->getDb()->q($productId).'');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
    
}

