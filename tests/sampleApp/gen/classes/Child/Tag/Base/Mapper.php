<?php
/**
 * @method Child_Tag[] loadFromRows(array $rows, $keysToList = false)
 */
class Child_Tag_Base_Mapper extends Sample_Tag_Mapper {

    var $recordClass = 'Child_Tag';

    var $id = 'Child_Tag_Mapper';

    var $shortId = 'tags';

    var $storage = 'Child_Tag_Storage';
    
    /**
     * @var Child 
     */
     protected $app = false;
     
 
 
    
    /**
     * @return Child 
     */
    function getApp() {
        return parent::getApp();
    }
    
    /**
     * Creates new Tags instance that is bound to this mapper.
     * 
     * @param string|array $typeIdOrDefaults Id of child mapper (FALSE = create default if possible)
     * @param array $defaults Values of object properties' to assign
     * @return Child_Tag 
     */ 
    function createRecord($typeIdOrDefaults = false, array $defaults = []) {
        $res = parent::createRecord($typeIdOrDefaults, $defaults);
        return $res;
    }
    
    /**
     * @return Child_Tag 
     */ 
    function reference ($values = []) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Tag 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Tag 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Tag 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Child_Tag[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Child_Tag[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Child_Tag     */
    function findFirst (array $query = [], $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Child_Tag     */
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
     * @return Child_Tag[]
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
     * @return Child_Tag[]
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
            '_people' => [
                'srcMapperClass' => 'Child_Tag_Mapper',
                'destMapperClass' => 'Child_Person_Mapper',
            ],
            '_perks' => [
                'srcMapperClass' => 'Child_Tag_Mapper',
                'destMapperClass' => 'Child_Perk_Mapper',
            ],
        ]);
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            [
                'singleCaption' => 'Tag',
                'pluralCaption' => 'Tags',
            ],
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Tag 
     */
    function loadByTagId ($tagId) {
        $res = parent::loadByTagId($tagId);
        return $res;
    }

    /**
     * @return Child_Tag 
     */
    function loadByTitle ($title) {
        $res = parent::loadByTitle($title);
        return $res;
    }
    
}

