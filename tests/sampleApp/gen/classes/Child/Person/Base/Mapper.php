<?php
/**
 * @method Child_Person[] loadFromRows(array $rows, $keysToList = false)
 */
class Child_Person_Base_Mapper extends Sample_Person_Mapper {

    var $recordClass = 'Child_Person';

    var $id = 'Child_Person_Mapper';

    var $storage = 'Child_Person_Storage';
 
 
    /**
     * @return Child_Person 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper('Child_Person_Mapper')->createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Person 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
        return $res;
    }
    
    /**
     * @return Child_Person 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return Child_Person 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return Child_Person 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return Child_Person 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Loads array of records.
     * 
     * @return Child_Person[] Records in the same order as in $ids array
     * @param array ids - Array of record identifiers
     * @param bool $keysToList DOES NOT accept customary fields
     */
    function loadRecordsArray(array $ids, $keysToList = false) {
        return parent::loadRecordsArray($ids, $keysToList);
    }

    /**
     * @deprecated Will be removed in 0.4
     * @return Child_Person[]
     */
    function loadRecordsByCriteria($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadRecordsByCriteria($where, $keysToList, $order, $joins, $limitOffset, $limitCount, $tableAlias);
    }
    
    /**
     * Returns first matching record 
     * 
     * @param array $query
     * @param mixed $sort
     * @return Child_Person     */
    function findFirst (array $query = array(), $sort = false) {
        return parent::findFirst($query, $sort);
    }
    
    /**
     * Returns the matching record only when resultset contains one record
     * 
     * @param array $query
     * @return Child_Person     */
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
     * @return Child_Person[]
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
     * @return Child_Person[]
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
        return 'name';   
    }
    
    protected function doGetRelationPrototypes() {
        return Ac_Util::m(parent::doGetRelationPrototypes(), array (
            '_portraitPersonPhoto' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Person_Photo_Mapper',
            ),
            '_religion' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Religion_Mapper',
            ),
            '_tags' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Tag_Mapper',
            ),
            '_personAlbums' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Person_Album_Mapper',
            ),
            '_personPhotos' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Person_Photo_Mapper',
            ),
            '_personPosts' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Person_Post_Mapper',
            ),
            '_incomingRelations' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Relation_Mapper',
                'destVarName' => '_incomingPerson',
            ),
            '_outgoingRelations' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Relation_Mapper',
                'destVarName' => '_outgoingPerson',
            ),
            '_extraCodeShopProducts' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Shop_Product_Mapper',
            ),
            '_noteShopProducts' => array (
                'srcMapperClass' => 'Child_Person_Mapper',
                'destMapperClass' => 'Child_Shop_Product_Mapper',
            ),
        ));
        
    }
    
    protected function doGetInfoParams() {
        return Ac_Util::m( 
            array (
                'singleCaption' => 'People',
                'pluralCaption' => 'People',
            ),
            parent::doGetInfoParams()
        );
        
    }
    

    /**
     * @return Child_Person 
     */
    function loadByPersonId ($personId) {
        $res = parent::loadByPersonId($personId);
        return $res;
    }
    
}

